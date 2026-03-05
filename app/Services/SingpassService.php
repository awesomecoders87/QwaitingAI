<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * SingpassService — FAPI 2.0 compliant Singpass Login integration.
 *
 * Flow:
 *  1. redirectToSingpass() calls getAuthUrl() which:
 *       a) Generates PKCE code_verifier / code_challenge
 *       b) Creates a signed JWT Request Object (JAR) with all auth params
 *       c) POSTs to /fapi/par with signed client_assertion + request object
 *       d) Returns the resulting /auth?request_uri= redirect URL
 *  2. handleSingpassCallback() calls exchangeCodeForToken() which:
 *       a) POSTs to /token with code + PKCE code_verifier + client_assertion
 *  3. getUserInfo() decodes the returned id_token JWT payload
 */
class SingpassService
{
    private string $clientId;
    private string $redirectUri;
    private string $discoveryUrl;
    private string $keyPath;
    private string $keyId = 'singpass-key-1';

    public function __construct()
    {
        $this->clientId     = env('SINGPASS_CLIENT_ID', '');
        $this->redirectUri  = env('SINGPASS_REDIRECT_URI', '');
        $this->discoveryUrl = env('SINGPASS_DISCOVERY_URL', 'https://stg-id.singpass.gov.sg/.well-known/openid-configuration');
        $this->keyPath      = env('SINGPASS_PRIVATE_KEY_PATH', 'storage/app/singpass_private.pem');
    }

    // ──────────────────────────────────────────────────────────────────────
    // PUBLIC API
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Build the Singpass authorization URL using PAR + JAR + PKCE.
     * $codeVerifier is populated by reference so the controller can stash it.
     */
    public function getAuthUrl(string $state, string $nonce, string &$codeVerifier = ''): string
    {
        $config = $this->getOpenIdConfiguration();

        $parEndpoint  = $config['pushed_authorization_request_endpoint']
            ?? 'https://stg-id.singpass.gov.sg/fapi/par';
        $authEndpoint = $config['authorization_endpoint']
            ?? 'https://stg-id.singpass.gov.sg/auth';

        // Generate PKCE
        $codeVerifier  = Str::random(96);
        $codeChallenge = $this->base64UrlEncode(hash('sha256', $codeVerifier, true));

        // Auth params that go into the signed Request Object
        $authParams = [
            'client_id'             => $this->clientId,
            'response_type'         => 'code',
            'redirect_uri'          => $this->redirectUri,
            'scope'                 => 'openid',
            'state'                 => $state,
            'nonce'                 => $nonce,
            'code_challenge'        => $codeChallenge,
            'code_challenge_method' => 'S256',
        ];

        // Build the signed JAR (JWT Authorization Request)
        $requestJwt = $this->buildSignedJwt($authParams, $this->clientId, $parEndpoint);

        // Build the client_assertion JWT for authentication
        $clientAssertion = $this->buildClientAssertion($parEndpoint);

        // POST to PAR endpoint
        $response = Http::asForm()->post($parEndpoint, [
            'client_id'              => $this->clientId,
            'request'                => $requestJwt,
            'client_assertion_type'  => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
            'client_assertion'       => $clientAssertion,
        ]);

        if ($response->failed()) {
            Log::error('Singpass PAR failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('Singpass PAR request failed: ' . $response->body());
        }

        $requestUri = $response->json('request_uri');

        if (empty($requestUri)) {
            throw new \RuntimeException('Singpass PAR response missing request_uri: ' . $response->body());
        }

        return $authEndpoint . '?' . http_build_query([
            'client_id'   => $this->clientId,
            'request_uri' => $requestUri,
        ]);
    }

    /**
     * Exchange authorization code for tokens.
     */
    public function exchangeCodeForToken(string $code, string $codeVerifier = ''): array
    {
        $config        = $this->getOpenIdConfiguration();
        $tokenEndpoint = $config['token_endpoint'] ?? 'https://stg-id.singpass.gov.sg/token';

        $clientAssertion = $this->buildClientAssertion($tokenEndpoint);

        $payload = [
            'grant_type'             => 'authorization_code',
            'code'                   => $code,
            'redirect_uri'           => $this->redirectUri,
            'client_id'              => $this->clientId,
            'client_assertion_type'  => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
            'client_assertion'       => $clientAssertion,
        ];

        if (!empty($codeVerifier)) {
            $payload['code_verifier'] = $codeVerifier;
        }

        $response = Http::asForm()->post($tokenEndpoint, $payload);

        if ($response->failed()) {
            Log::error('Singpass token exchange failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('Singpass token exchange failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Decode the ID token JWT payload.
     * In production you MUST verify the JWS signature + decrypt JWE.
     */
    public function getUserInfo(string $idToken): array
    {
        $parts = explode('.', $idToken);

        // Handle both plain JWS (3 parts) and JWE (5 parts – encrypted)
        if (count($parts) < 3) {
            Log::warning('Singpass: could not parse id_token', ['token_length' => strlen($idToken)]);
            return [];
        }

        // For JWE (encrypted token) we cannot decode without the private key decryption step.
        // For now we attempt the JWS path (staging may return plain JWS).
        $payload = json_decode($this->base64UrlDecode($parts[1]), true);

        return is_array($payload) ? $payload : [];
    }

    /**
     * Expose the application's EC public key as JWKS so Singpass can verify our client_assertions.
     * Register the endpoint https://your-domain.com/sp/jwks in the Developer Portal.
     */
    public function getJwks(): array
    {
        $pem = $this->loadOrGeneratePrivateKey();
        $res = openssl_pkey_get_private($pem);

        if (!$res) {
            return ['keys' => []];
        }

        $details = openssl_pkey_get_details($res);

        return [
            'keys' => [[
                'kty' => 'EC',
                'use' => 'sig',
                'crv' => 'P-256',
                'alg' => 'ES256',
                'kid' => $this->keyId,
                'x'   => $this->base64UrlEncode($details['ec']['x']),
                'y'   => $this->base64UrlEncode($details['ec']['y']),
            ]],
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Build a signed ES256 JWT.  Used for both the JAR (request object) and client_assertion.
     */
    private function buildSignedJwt(array $claims, string $issuer, string $audience): string
    {
        $now = time();

        $header = [
            'alg' => 'ES256',
            'typ' => 'JWT',
            'kid' => $this->keyId,
        ];

        $baseClaims = [
            'iss' => $issuer,
            'sub' => $issuer,
            'aud' => $audience,
            'iat' => $now,
            'exp' => $now + 120,
            'jti' => (string) Str::uuid(),
        ];

        $payload = array_merge($baseClaims, $claims);

        $headerB64  = $this->base64UrlEncode(json_encode($header));
        $payloadB64 = $this->base64UrlEncode(json_encode($payload));
        $signingInput = $headerB64 . '.' . $payloadB64;

        $signature = $this->signWithEc($signingInput);

        return $signingInput . '.' . $signature;
    }

    /**
     * Build the client_assertion JWT used to authenticate this app to Singpass.
     */
    private function buildClientAssertion(string $audience): string
    {
        return $this->buildSignedJwt([], $this->clientId, $audience);
    }

    /**
     * Sign a string with the EC private key and return a base64url-encoded signature.
     * ES256 = ECDSA P-256 SHA-256, the signature must be in raw R||S format.
     */
    private function signWithEc(string $data): string
    {
        $pem = $this->loadOrGeneratePrivateKey();
        $key = openssl_pkey_get_private($pem);

        if (!$key) {
            throw new \RuntimeException('Singpass: could not load private key: ' . openssl_error_string());
        }

        // openssl_sign on EC returns a DER-encoded ECDSA signature
        $derSignature = '';
        if (!openssl_sign($data, $derSignature, $key, OPENSSL_ALGO_SHA256)) {
            throw new \RuntimeException('Singpass: EC signing failed: ' . openssl_error_string());
        }

        // Convert DER → raw R||S (64 bytes for P-256)
        $rawSignature = $this->derToRawRS($derSignature, 32);

        return $this->base64UrlEncode($rawSignature);
    }

    /**
     * Convert a DER-encoded ECDSA signature to raw R||S concatenation.
     * DER structure: 0x30 [len] 0x02 [rlen] R 0x02 [slen] S
     */
    private function derToRawRS(string $der, int $componentLength): string
    {
        $offset = 0;
        if (ord($der[$offset++]) !== 0x30) {
            throw new \RuntimeException('Invalid DER signature: missing SEQUENCE tag');
        }

        // Skip length byte(s)
        $seqLen = ord($der[$offset++]);
        if ($seqLen & 0x80) {
            $offset += $seqLen & 0x7f; // long-form length — unlikely for P-256
        }

        $extractInt = function (string $der, int &$offset) use ($componentLength): string {
            if (ord($der[$offset++]) !== 0x02) {
                throw new \RuntimeException('Invalid DER signature: missing INTEGER tag');
            }
            $len = ord($der[$offset++]);
            $value = substr($der, $offset, $len);
            $offset += $len;

            // Strip leading zero added by DER encoding to keep the number positive
            if (strlen($value) > $componentLength && ord($value[0]) === 0x00) {
                $value = substr($value, 1);
            }

            // Left-pad with zeroes if shorter than expected
            $value = str_pad($value, $componentLength, "\x00", STR_PAD_LEFT);

            return $value;
        };

        $r = $extractInt($der, $offset);
        $s = $extractInt($der, $offset);

        return $r . $s;
    }

    /**
     * Load the PEM private key from disk, or generate and persist a new one.
     */
    private function loadOrGeneratePrivateKey(): string
    {
        $fullPath = base_path($this->keyPath);

        if (file_exists($fullPath)) {
            return file_get_contents($fullPath);
        }

        // Generate a new EC P-256 key pair
        $resource = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name'       => 'prime256v1',
        ]);

        if (!$resource) {
            throw new \RuntimeException(
                'Singpass: failed to generate EC key pair. OpenSSL error: ' . openssl_error_string()
            );
        }

        openssl_pkey_export($resource, $pem);

        // Ensure directory exists
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }

        file_put_contents($fullPath, $pem);

        Log::info('Singpass: generated new EC P-256 private key at ' . $fullPath);

        return $pem;
    }

    /**
     * Fetch and in-process cache the OpenID Connect discovery document.
     */
    private function getOpenIdConfiguration(): array
    {
        static $config = null;

        if ($config === null) {
            $response = Http::timeout(10)->get($this->discoveryUrl);

            if ($response->failed()) {
                Log::error('Singpass: discovery document fetch failed', [
                    'url'    => $this->discoveryUrl,
                    'status' => $response->status(),
                ]);
                $config = [];
            } else {
                $config = $response->json() ?? [];
            }
        }

        return $config;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
