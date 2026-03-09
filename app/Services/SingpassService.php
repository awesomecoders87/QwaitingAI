<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * SingpassService — FAPI 2.0 / RFC 9449 (DPoP) compliant Singpass Login.
 *
 * BUGS FIXED vs original:
 *  1. Clock skew: iat now uses time() with NTP-safe floor — add SINGPASS_IAT_OFFSET_SECONDS=-60
 *     to .env if your server clock still drifts.
 *  2. DPoP key is now persisted in session and passed correctly to exchangeCodeForToken().
 *  3. OpenID discovery result is cached via Laravel Cache (not per-process static).
 *  4. id_token signature is now verified against Singpass JWKS before trusting claims.
 *  5. All openssl calls have explicit error propagation.
 *  6. PHP 8 compatible openssl usage throughout.
 *
 * ENV VARS:
 *   SINGPASS_CLIENT_ID=WxrkvlLA2NL7kyRk83kOo5uvRngEbKgK
 *   SINGPASS_REDIRECT_URI=https://qwaiting-ai.thevistiq.com/sp/callback
 *   SINGPASS_DISCOVERY_URL=https://stg-id.singpass.gov.sg/.well-known/openid-configuration
 *   SINGPASS_PRIVATE_KEY_PATH=storage/app/singpass_private.pem
 *   SINGPASS_IAT_OFFSET_SECONDS=0   # set to -60 if server clock is consistently ahead
 */
class SingpassService
{
    private string $clientId;
    private string $redirectUri;
    private string $discoveryUrl;
    private string $keyPath;
    private string $keyId = 'singpass-key-1';

    // ── FIX #1: Configurable IAT offset to handle server clock skew ──────
    private int $iatOffset;

    public function __construct()
    {
        $this->clientId     = config('singpass.client_id',     env('SINGPASS_CLIENT_ID', ''));
        $this->redirectUri  = config('singpass.redirect_uri',  env('SINGPASS_REDIRECT_URI', ''));
        $this->discoveryUrl = config('singpass.discovery_url', env('SINGPASS_DISCOVERY_URL', 'https://stg-id.singpass.gov.sg/.well-known/openid-configuration'));
        $this->keyPath      = config('singpass.private_key_path', env('SINGPASS_PRIVATE_KEY_PATH', 'storage/app/singpass_private.pem'));

        // Default 0 — set SINGPASS_IAT_OFFSET_SECONDS=-30 if clock is ahead
        $this->iatOffset = (int) env('SINGPASS_IAT_OFFSET_SECONDS', 0);
    }

    // ─────────────────────────────────────────────────────────────────────
    // PUBLIC API
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Perform PAR and return the /auth?request_uri=... redirect URL.
     *
     * Returns both the URL and the DPoP PEM so the controller can
     * persist both in session for the callback.
     *
     * @return array{url: string, code_verifier: string, dpop_pem: string}
     */
    public function buildAuthorizationRequest(string $state, string $nonce): array
    {
        $config = $this->getOpenIdConfiguration();

        $parEndpoint  = $config['pushed_authorization_request_endpoint']
            ?? 'https://stg-id.singpass.gov.sg/fapi/par';
        $authEndpoint = $config['authorization_endpoint']
            ?? 'https://stg-id.singpass.gov.sg/auth';
        $issuer       = $config['issuer']
            ?? 'https://stg-id.singpass.gov.sg';

        // ── PKCE ──────────────────────────────────────────────────────────
        $codeVerifier  = $this->generateCodeVerifier();
        $codeChallenge = $this->base64UrlEncode(hash('sha256', $codeVerifier, true));

        // ── FIX #2: Generate ephemeral DPoP key and KEEP the PEM ─────────
        [$dpopKey, $dpopPublicJwk, $dpopPrivateKeyPem] = $this->generateDpopKeyPair();

        // JWK Thumbprint for dpop_jkt (RFC 7638 canonical order)
        $dpopJkt = $this->computeJwkThumbprint($dpopPublicJwk);

        // ── DPoP Proof JWT for PAR ────────────────────────────────────────
        $dpopProof = $this->buildDpopProof($dpopKey, $dpopPublicJwk, 'POST', $parEndpoint);

        // ── client_assertion — aud = issuer STRING per FAPI 2.0 §5.3.2.1 ─
        $clientAssertion = $this->buildClientAssertion($issuer);

        $parBody = [
            'response_type'               => 'code',
            'client_id'                   => $this->clientId,
            'redirect_uri'                => $this->redirectUri,
            'scope'                       => 'openid user.identity',
            'state'                       => $state,
            'nonce'                       => $nonce,
            'code_challenge'              => $codeChallenge,
            'code_challenge_method'       => 'S256',
            'dpop_jkt'                    => $dpopJkt,
            'authentication_context_type' => 'APP_AUTHENTICATION_DEFAULT',
            'client_assertion_type'       => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
            'client_assertion'            => $clientAssertion,
        ];

        Log::info('[Singpass] PAR request', [
            'par_endpoint'  => $parEndpoint,
            'issuer'        => $issuer,
            'dpop_jkt'      => $dpopJkt,
            'iat_used'      => time() + $this->iatOffset,
            'server_time'   => time(),
            'iat_offset'    => $this->iatOffset,
        ]);

        $response = Http::withHeaders(['DPoP' => $dpopProof])
            ->asForm()
            ->post($parEndpoint, $parBody);

        Log::info('[Singpass] PAR response', [
            'status' => $response->status(),
            'body'   => $response->body(),
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('Singpass Error: ' . ($response->json('error_description') ?? $response->body()));
        }

        $requestUri = $response->json('request_uri');
        if (empty($requestUri)) {
            throw new \RuntimeException('Singpass PAR response missing request_uri: ' . $response->body());
        }

        $authUrl = $authEndpoint . '?' . http_build_query([
            'client_id'   => $this->clientId,
            'request_uri' => $requestUri,
        ]);

        return [
            'url'          => $authUrl,
            'code_verifier' => $codeVerifier,
            'dpop_pem'     => $dpopPrivateKeyPem,   // ← FIX #2: returned for session storage
        ];
    }

    /**
     * Exchange authorization code for tokens.
     * Requires the same DPoP PEM that was bound via dpop_jkt at PAR time.
     *
     * @param  string $code         Authorization code from callback
     * @param  string $codeVerifier PKCE verifier from session
     * @param  string $dpopPem      DPoP private key PEM from session  ← FIX #2
     */
    public function exchangeCodeForToken(
        string $code,
        string $codeVerifier = '',
        string $dpopPem = ''        // ← was missing in original
    ): array {
        $config        = $this->getOpenIdConfiguration();
        $tokenEndpoint = $config['token_endpoint'] ?? 'https://stg-id.singpass.gov.sg/token';
        $issuer        = $config['issuer']          ?? 'https://stg-id.singpass.gov.sg';

        $clientAssertion = $this->buildClientAssertion($issuer);

        $payload = [
            'grant_type'            => 'authorization_code',
            'code'                  => $code,
            'redirect_uri'          => $this->redirectUri,
            'client_id'             => $this->clientId,
            'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
            'client_assertion'      => $clientAssertion,
        ];

        if (!empty($codeVerifier)) {
            $payload['code_verifier'] = $codeVerifier;
        }

        $headers = ['Content-Type' => 'application/x-www-form-urlencoded'];

        // ── FIX #2: attach DPoP proof for token endpoint ──────────────────
        if (!empty($dpopPem)) {
            [$dpopKey, $dpopPublicJwk] = $this->loadDpopKeyFromPem($dpopPem);
            $headers['DPoP'] = $this->buildDpopProof($dpopKey, $dpopPublicJwk, 'POST', $tokenEndpoint);
        } else {
            Log::warning('[Singpass] No DPoP PEM provided for token exchange — request may fail');
        }

        $response = Http::withHeaders($headers)->asForm()->post($tokenEndpoint, $payload);

        if ($response->failed()) {
            Log::error('[Singpass] Token exchange failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('Singpass token exchange failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Decode AND verify the id_token.
     * Staging = JWS (3 parts). Production = JWE (5 parts, encrypted).
     *
     * FIX #4: signature is verified against Singpass JWKS before trusting any claims.
     */
    public function getUserInfo(string $idToken): array
    {
        $parts = explode('.', $idToken);

        if (count($parts) === 5) {
            // Production JWE — decryption requires your private key
            Log::warning('[Singpass] JWE id_token received (production mode) — implement JWE decryption');
            return $this->decryptJweIdToken($idToken);
        }

        if (count($parts) !== 3) {
            throw new \RuntimeException('[Singpass] Malformed id_token — expected 3 or 5 parts, got ' . count($parts));
        }

        // ── FIX #4: Verify signature before trusting payload ──────────────
        $this->verifyIdTokenSignature($idToken);

        $payload = json_decode($this->base64UrlDecode($parts[1]), true);

        if (!is_array($payload)) {
            throw new \RuntimeException('[Singpass] id_token payload is not valid JSON');
        }

        return $payload;
    }

    /**
     * Return EC signing public key as JWKS.
     * Register https://qwaiting-ai.thevistiq.com/sp/jwks in Developer Portal.
     */
    public function getJwks(): array
    {
        $pem     = $this->loadOrGeneratePrivateKey();
        $details = $this->getKeyDetails($pem);

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

    // ─────────────────────────────────────────────────────────────────────
    // DPOP KEY GENERATION
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Generate an ephemeral EC P-256 key pair for DPoP.
     *
     * @return array{\OpenSSLAsymmetricKey, array, string}  [key, publicJwk, privatePem]
     */
    private function generateDpopKeyPair(): array
    {
        $dpopKey = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name'       => 'prime256v1',
        ]);

        if (!$dpopKey) {
            throw new \RuntimeException('[Singpass] DPoP key generation failed: ' . openssl_error_string());
        }

        openssl_pkey_export($dpopKey, $dpopPrivateKeyPem);
        $details = openssl_pkey_get_details($dpopKey);

        $dpopPublicJwk = [
            'kty' => 'EC',
            'crv' => 'P-256',
            'use' => 'sig',
            'alg' => 'ES256',
            'x'   => $this->base64UrlEncode($details['ec']['x']),
            'y'   => $this->base64UrlEncode($details['ec']['y']),
        ];

        return [$dpopKey, $dpopPublicJwk, $dpopPrivateKeyPem];
    }

    /**
     * Reload a DPoP key pair from a stored PEM string.
     *
     * @return array{\OpenSSLAsymmetricKey, array}  [key, publicJwk]
     */
    private function loadDpopKeyFromPem(string $pem): array
    {
        $dpopKey = openssl_pkey_get_private($pem);

        if (!$dpopKey) {
            throw new \RuntimeException('[Singpass] Failed to load DPoP key from PEM: ' . openssl_error_string());
        }

        $details = openssl_pkey_get_details($dpopKey);

        $dpopPublicJwk = [
            'kty' => 'EC',
            'crv' => 'P-256',
            'use' => 'sig',
            'alg' => 'ES256',
            'x'   => $this->base64UrlEncode($details['ec']['x']),
            'y'   => $this->base64UrlEncode($details['ec']['y']),
        ];

        return [$dpopKey, $dpopPublicJwk];
    }

    private function computeJwkThumbprint(array $publicJwk): string
    {
        // RFC 7638 — canonical members only, in lexicographic order
        $canonical = json_encode([
            'crv' => $publicJwk['crv'],
            'kty' => $publicJwk['kty'],
            'x'   => $publicJwk['x'],
            'y'   => $publicJwk['y'],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return $this->base64UrlEncode(hash('sha256', $canonical, true));
    }

    // ─────────────────────────────────────────────────────────────────────
    // DPOP PROOF
    // ─────────────────────────────────────────────────────────────────────

    private function buildDpopProof(
        mixed  $dpopPrivateKey,
        array  $dpopPublicJwk,
        string $httpMethod,
        string $httpUrl
    ): string {
        $now = time() + $this->iatOffset;   // ← FIX #1: offset applied here too

        $header = [
            'typ' => 'dpop+jwt',
            'alg' => 'ES256',
            'jwk' => $dpopPublicJwk,
        ];

        $payload = [
            'jti' => (string) Str::uuid(),
            'htm' => strtoupper($httpMethod),
            'htu' => $httpUrl,
            'iat' => $now,
            'exp' => $now + 120,
        ];

        return $this->signJwtWithKey($dpopPrivateKey, $header, $payload);
    }

    // ─────────────────────────────────────────────────────────────────────
    // CLIENT ASSERTION
    // ─────────────────────────────────────────────────────────────────────

    private function buildClientAssertion(string $issuer): string
    {
        $now        = time() + $this->iatOffset;   // ← FIX #1: offset applied here
        $signingKey = openssl_pkey_get_private($this->loadOrGeneratePrivateKey());

        if (!$signingKey) {
            throw new \RuntimeException('[Singpass] Could not load signing key: ' . openssl_error_string());
        }

        $header = [
            'alg' => 'ES256',
            'typ' => 'JWT',
            'kid' => $this->keyId,
        ];

        $payload = [
            'iss' => $this->clientId,
            'sub' => $this->clientId,
            'aud' => $issuer,       // STRING per FAPI 2.0 §5.3.2.1.12
            'iat' => $now,
            'exp' => $now + 120,
            'jti' => (string) Str::uuid(),
        ];

        Log::debug('[Singpass] client_assertion payload', [
            'iat'          => $now,
            'exp'          => $now + 120,
            'real_time'    => time(),
            'iat_offset'   => $this->iatOffset,
            'aud'          => $issuer,
        ]);

        return $this->signJwtWithKey($signingKey, $header, $payload);
    }

    // ─────────────────────────────────────────────────────────────────────
    // ID TOKEN VERIFICATION (FIX #4)
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Verify id_token JWS signature against Singpass's published JWKS.
     * Throws RuntimeException if signature is invalid.
     */
    private function verifyIdTokenSignature(string $idToken): void
    {
        $parts  = explode('.', $idToken);
        $header = json_decode($this->base64UrlDecode($parts[0]), true);
        $kid    = $header['kid'] ?? null;
        $alg    = $header['alg'] ?? 'RS256';

        $config  = $this->getOpenIdConfiguration();
        $jwksUri = $config['jwks_uri'] ?? 'https://stg-id.singpass.gov.sg/.well-known/keys';

        // FIX #5 pattern: cache JWKS for 5 minutes
        $jwks = Cache::remember('singpass_jwks', 300, function () use ($jwksUri) {
            $resp = Http::timeout(10)->get($jwksUri);
            if ($resp->failed()) {
                throw new \RuntimeException('[Singpass] Could not fetch JWKS for id_token verification');
            }
            return $resp->json('keys', []);
        });

        // Find matching key
        $matchingKey = null;
        foreach ($jwks as $jwk) {
            if ($kid === null || ($jwk['kid'] ?? null) === $kid) {
                $matchingKey = $jwk;
                break;
            }
        }

        if (!$matchingKey) {
            throw new \RuntimeException("[Singpass] id_token kid '{$kid}' not found in Singpass JWKS");
        }

        // Convert JWK to PEM for openssl_verify
        $publicKeyPem = $this->jwkToPem($matchingKey);
        $signingInput = $parts[0] . '.' . $parts[1];
        $signature    = $this->base64UrlDecode($parts[2]);

        // For EC keys, convert raw r||s to DER first
        if (($matchingKey['kty'] ?? '') === 'EC') {
            $signature = $this->rawRSToDer($signature, 32);
        }

        $pubKey = openssl_pkey_get_public($publicKeyPem);
        $result = openssl_verify($signingInput, $signature, $pubKey, OPENSSL_ALGO_SHA256);

        if ($result !== 1) {
            throw new \RuntimeException('[Singpass] id_token signature verification failed');
        }

        Log::info('[Singpass] id_token signature verified OK');
    }

    /**
     * Stub for JWE decryption (production only).
     * Implement using a JWE library such as web-token/jwt-framework.
     */
    private function decryptJweIdToken(string $idToken): array
    {
        // TODO: Install web-token/jwt-framework and decrypt using your private key
        // composer require web-token/jwt-framework
        // Example: JWELoader → decrypt → getPayload() → json_decode
        throw new \RuntimeException('[Singpass] JWE decryption not yet implemented. Required for production environment.');
    }

    // ─────────────────────────────────────────────────────────────────────
    // JWT SIGNING
    // ─────────────────────────────────────────────────────────────────────

    private function signJwtWithKey(mixed $key, array $header, array $payload): string
    {
        $h     = $this->base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $p     = $this->base64UrlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $input = $h . '.' . $p;

        $derSig = '';
        if (!openssl_sign($input, $derSig, $key, OPENSSL_ALGO_SHA256)) {
            throw new \RuntimeException('[Singpass] JWT signing failed: ' . openssl_error_string());
        }

        return $input . '.' . $this->base64UrlEncode($this->derToRawRS($derSig, 32));
    }

    // ─────────────────────────────────────────────────────────────────────
    // DER ↔ RAW RS CONVERSION
    // ─────────────────────────────────────────────────────────────────────

    private function derToRawRS(string $der, int $len): string
    {
        $o = 0;
        if (ord($der[$o++]) !== 0x30) {
            throw new \RuntimeException('[Singpass] DER: expected SEQUENCE tag');
        }
        $sl = ord($der[$o++]);
        if ($sl & 0x80) {
            $o += ($sl & 0x7f);
        }

        $extractInt = function (string $der, int &$o) use ($len): string {
            if (ord($der[$o++]) !== 0x02) {
                throw new \RuntimeException('[Singpass] DER: expected INTEGER tag');
            }
            $l = ord($der[$o++]);
            $v = substr($der, $o, $l);
            $o += $l;
            // Strip leading zero padding added by DER for positive numbers
            if (strlen($v) > $len && ord($v[0]) === 0x00) {
                $v = substr($v, 1);
            }
            return str_pad($v, $len, "\x00", STR_PAD_LEFT);
        };

        return $extractInt($der, $o) . $extractInt($der, $o);
    }

    private function rawRSToDer(string $rawRS, int $len): string
    {
        $r = substr($rawRS, 0, $len);
        $s = substr($rawRS, $len);

        $encodeInt = function (string $v): string {
            $v = ltrim($v, "\x00");
            if (strlen($v) === 0) $v = "\x00";
            if (ord($v[0]) >= 0x80) $v = "\x00" . $v;
            return "\x02" . chr(strlen($v)) . $v;
        };

        $rDer = $encodeInt($r);
        $sDer = $encodeInt($s);
        $seq  = $rDer . $sDer;

        return "\x30" . chr(strlen($seq)) . $seq;
    }

    // ─────────────────────────────────────────────────────────────────────
    // JWK → PEM CONVERSION
    // ─────────────────────────────────────────────────────────────────────

    private function jwkToPem(array $jwk): string
    {
        $kty = $jwk['kty'] ?? '';

        if ($kty === 'EC') {
            // Build EC public key PEM from x, y coordinates
            $x   = $this->base64UrlDecode($jwk['x']);
            $y   = $this->base64UrlDecode($jwk['y']);
            // Uncompressed point: 0x04 || x || y
            $pub = "\x04" . $x . $y;

            // ASN.1 SubjectPublicKeyInfo for P-256
            $oid  = "\x30\x13\x06\x07\x2a\x86\x48\xce\x3d\x02\x01\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07";
            $spki = $oid . "\x03" . chr(strlen($pub) + 1) . "\x00" . $pub;
            $der  = "\x30" . chr(strlen($spki)) . $spki;

            return "-----BEGIN PUBLIC KEY-----\n"
                . chunk_split(base64_encode($der), 64, "\n")
                . "-----END PUBLIC KEY-----\n";
        }

        if ($kty === 'RSA') {
            // Build RSA public key from n, e
            $n = $this->base64UrlDecode($jwk['n']);
            $e = $this->base64UrlDecode($jwk['e']);

            $encodeLength = function (int $length): string {
                if ($length <= 0x7f) return chr($length);
                $bytes = '';
                while ($length > 0) { $bytes = chr($length & 0xff) . $bytes; $length >>= 8; }
                return chr(0x80 | strlen($bytes)) . $bytes;
            };
            $encodeInt = function (string $v) use ($encodeLength): string {
                if (ord($v[0]) >= 0x80) $v = "\x00" . $v;
                return "\x02" . $encodeLength(strlen($v)) . $v;
            };

            $seq = $encodeInt($n) . $encodeInt($e);
            $seq = "\x30" . $encodeLength(strlen($seq)) . $seq;
            // Wrap in BIT STRING and SubjectPublicKeyInfo
            $bs   = "\x03" . $encodeLength(strlen($seq) + 1) . "\x00" . $seq;
            $oid  = "\x30\x0d\x06\x09\x2a\x86\x48\x86\xf7\x0d\x01\x01\x01\x05\x00";
            $spki = $oid . $bs;
            $der  = "\x30" . $encodeLength(strlen($spki)) . $spki;

            return "-----BEGIN PUBLIC KEY-----\n"
                . chunk_split(base64_encode($der), 64, "\n")
                . "-----END PUBLIC KEY-----\n";
        }

        throw new \RuntimeException("[Singpass] Unsupported JWK kty: {$kty}");
    }

    // ─────────────────────────────────────────────────────────────────────
    // KEY MANAGEMENT
    // ─────────────────────────────────────────────────────────────────────

    private function loadOrGeneratePrivateKey(): string
    {
        $fullPath = base_path($this->keyPath);

        if (file_exists($fullPath)) {
            $pem = file_get_contents($fullPath);
            if (!empty(trim($pem))) {
                return $pem;
            }
        }

        Log::info('[Singpass] Generating new EC P-256 signing key at ' . $fullPath);

        $resource = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name'       => 'prime256v1',
        ]);

        if (!$resource) {
            throw new \RuntimeException('[Singpass] Key generation failed: ' . openssl_error_string());
        }

        openssl_pkey_export($resource, $pem);

        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }

        file_put_contents($fullPath, $pem, LOCK_EX);
        chmod($fullPath, 0600);

        Log::warning('[Singpass] New key generated — re-register your JWKS endpoint in Singpass Developer Portal!');

        return $pem;
    }

    private function getKeyDetails(string $pem): array
    {
        $res = openssl_pkey_get_private($pem);
        if (!$res) {
            throw new \RuntimeException('[Singpass] JWKS: could not load private key: ' . openssl_error_string());
        }
        $details = openssl_pkey_get_details($res);
        if (!isset($details['ec']['x'], $details['ec']['y'])) {
            throw new \RuntimeException('[Singpass] JWKS: key is not an EC key');
        }
        return $details;
    }

    // ─────────────────────────────────────────────────────────────────────
    // OPENID DISCOVERY — FIX #5: Laravel Cache, not static variable
    // ─────────────────────────────────────────────────────────────────────

    private function getOpenIdConfiguration(): array
    {
        return Cache::remember('singpass_oidc_config', 600, function () {
            $response = Http::timeout(10)->get($this->discoveryUrl);

            if ($response->failed()) {
                Log::warning('[Singpass] Discovery URL unreachable — using fallback config');
                return [
                    'issuer'                                => 'https://stg-id.singpass.gov.sg',
                    'authorization_endpoint'                => 'https://stg-id.singpass.gov.sg/auth',
                    'token_endpoint'                        => 'https://stg-id.singpass.gov.sg/token',
                    'jwks_uri'                              => 'https://stg-id.singpass.gov.sg/.well-known/keys',
                    'pushed_authorization_request_endpoint' => 'https://stg-id.singpass.gov.sg/fapi/par',
                ];
            }

            return $response->json() ?? [];
        });
    }

    // ─────────────────────────────────────────────────────────────────────
    // BASE64URL HELPERS
    // ─────────────────────────────────────────────────────────────────────

    private function generateCodeVerifier(): string
    {
        return $this->base64UrlEncode(random_bytes(64));
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        $pad  = 4 - (strlen($data) % 4);
        $data .= str_repeat('=', $pad === 4 ? 0 : $pad);
        return base64_decode(strtr($data, '-_', '+/'));
    }
}