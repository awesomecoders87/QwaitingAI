<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SingpassService
{
    private string $clientId;
    private string $redirectUri;
    private string $keyPath;
    private string $keyId  = 'singpass-key-1';
    private int    $iatOffset;

    // ── Hardcoded Singpass staging constants ──────────────────────────────
    private string $issuer        = 'https://stg-id.singpass.gov.sg';
    private string $parEndpoint   = 'https://stg-id.singpass.gov.sg/fapi/par';
    private string $authEndpoint  = 'https://stg-id.singpass.gov.sg/auth';
    private string $tokenEndpoint = 'https://stg-id.singpass.gov.sg/token';
    private string $jwksUri       = 'https://stg-id.singpass.gov.sg/.well-known/keys';

    public function __construct()
    {
        $this->clientId    = 'QA7M3gEqL5XeCchggRsoFGpApfLoa1bJ';
        $this->redirectUri = 'https://qwaiting-ai.thevistiq.com/sp/callback';
        $this->keyPath     = 'storage/app/singpass_private.pem';
        $this->iatOffset   = 0;

        Log::info('[Singpass] Loaded client_id: ' . $this->clientId);
    }

    // ─────────────────────────────────────────────────────────────────────
    // PUBLIC API
    // ─────────────────────────────────────────────────────────────────────

    /**
     * @return array{url: string, code_verifier: string, dpop_pem: string}
     */
    public function buildAuthorizationRequest(string $state, string $nonce): array
    {
        $codeVerifier  = $this->generateCodeVerifier();
        $codeChallenge = $this->base64UrlEncode(hash('sha256', $codeVerifier, true));

        [$dpopKey, $dpopPublicJwk, $dpopPrivateKeyPem] = $this->generateDpopKeyPair();
        $dpopJkt   = $this->computeJwkThumbprint($dpopPublicJwk);
        $dpopProof = $this->buildDpopProof($dpopKey, $dpopPublicJwk, 'POST', $this->parEndpoint);

        // aud MUST be $this->issuer — NOT $this->parEndpoint
        $clientAssertion = $this->buildClientAssertion($this->issuer);

        $parBody = [
            'response_type'               => 'code',
            'client_id'                   => $this->clientId,
            'redirect_uri'                => $this->redirectUri,
            'scope'                       => 'openid',
            'state'                       => $state,
            'nonce'                       => $nonce,
            'code_challenge'              => $codeChallenge,
            'code_challenge_method'       => 'S256',
            'dpop_jkt'                    => $dpopJkt,
            // 'authentication_context_type' => 'urn:singpass:authn:context:qr',
            'client_assertion_type'       => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
            'client_assertion'            => $clientAssertion,
        ];

        Log::info('[Singpass] PAR request', [
            'par_endpoint' => $this->parEndpoint,
            'client_id'    => $this->clientId,
            'aud_issuer'   => $this->issuer,
            'dpop_jkt'     => $dpopJkt,
            'server_time'  => time(),
        ]);

        $response = Http::withHeaders(['DPoP' => $dpopProof])
            ->asForm()
            ->post($this->parEndpoint, $parBody);

        Log::info('[Singpass] PAR response', [
            'status' => $response->status(),
            'body'   => $response->body(),
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('Singpass PAR Error: ' . ($response->json('error_description') ?? $response->body()));
        }

        $requestUri = $response->json('request_uri');
        if (empty($requestUri)) {
            throw new \RuntimeException('Singpass PAR response missing request_uri: ' . $response->body());
        }

        $authUrl = $this->authEndpoint . '?' . http_build_query([
            'client_id'   => $this->clientId,
            'request_uri' => $requestUri,
        ]);

        return [
            'url'           => $authUrl,
            'code_verifier' => $codeVerifier,
            'dpop_pem'      => $dpopPrivateKeyPem,
        ];
    }

    public function exchangeCodeForToken(
        string $code,
        string $codeVerifier = '',
        string $dpopPem = ''
    ): array {
        // aud MUST be $this->issuer
        $clientAssertion = $this->buildClientAssertion($this->issuer);

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

        if (!empty($dpopPem)) {
            [$dpopKey, $dpopPublicJwk] = $this->loadDpopKeyFromPem($dpopPem);
            $headers['DPoP'] = $this->buildDpopProof($dpopKey, $dpopPublicJwk, 'POST', $this->tokenEndpoint);
        } else {
            Log::warning('[Singpass] No DPoP PEM for token exchange — may fail');
        }

        $response = Http::withHeaders($headers)->asForm()->post($this->tokenEndpoint, $payload);

        if ($response->failed()) {
            Log::error('[Singpass] Token exchange failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('Singpass token exchange failed: ' . $response->body());
        }

        return $response->json();
    }

    public function getUserInfo(string $idToken): array
    {
        $parts = explode('.', $idToken);

        if (count($parts) === 5) {
            Log::warning('[Singpass] JWE id_token — needs decryption for production');
            return $this->decryptJweIdToken($idToken);
        }

        if (count($parts) !== 3) {
            throw new \RuntimeException('[Singpass] Malformed id_token — expected 3 or 5 parts');
        }

        $this->verifyIdTokenSignature($idToken);

        $payload = json_decode($this->base64UrlDecode($parts[1]), true);
        if (!is_array($payload)) {
            throw new \RuntimeException('[Singpass] id_token payload is not valid JSON');
        }

        return $payload;
    }

    public function getJwks(): array
    {
        $sigPem     = $this->loadOrGeneratePrivateKey();
        $sigDetails = $this->getKeyDetails($sigPem);

        $keys = [[
            'kty' => 'EC',
            'use' => 'sig',
            'crv' => 'P-256',
            'alg' => 'ES256',
            'kid' => $this->keyId,
            'x'   => $this->base64UrlEncode($sigDetails['ec']['x']),
            'y'   => $this->base64UrlEncode($sigDetails['ec']['y']),
        ]];

        $encKeyPath = env('SINGPASS_ENC_PRIVATE_KEY_PATH', '');
        if (!empty($encKeyPath)) {
            $encFullPath = base_path($encKeyPath);
            if (file_exists($encFullPath)) {
                $encPem = file_get_contents($encFullPath);
                if (!empty(trim($encPem))) {
                    $encRes = openssl_pkey_get_private($encPem);
                    if ($encRes) {
                        $encDetails = openssl_pkey_get_details($encRes);
                        $keys[] = [
                            'kty' => 'EC',
                            'use' => 'enc',
                            'crv' => 'P-256',
                            'alg' => 'ECDH-ES+A128KW',
                            'kid' => 'singpass-enc-1',
                            'x'   => $this->base64UrlEncode($encDetails['ec']['x']),
                            'y'   => $this->base64UrlEncode($encDetails['ec']['y']),
                        ];
                    }
                }
            }
        }

        return ['keys' => $keys];
    }

    // ─────────────────────────────────────────────────────────────────────
    // DPOP
    // ─────────────────────────────────────────────────────────────────────

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
            'x'   => $this->base64UrlEncode($details['ec']['x']),
            'y'   => $this->base64UrlEncode($details['ec']['y']),
        ];

        return [$dpopKey, $dpopPublicJwk, $dpopPrivateKeyPem];
    }

    private function loadDpopKeyFromPem(string $pem): array
    {
        $dpopKey = openssl_pkey_get_private($pem);
        if (!$dpopKey) {
            throw new \RuntimeException('[Singpass] Failed to load DPoP key: ' . openssl_error_string());
        }
        $details = openssl_pkey_get_details($dpopKey);
        $dpopPublicJwk = [
            'kty' => 'EC',
            'crv' => 'P-256',
            'x'   => $this->base64UrlEncode($details['ec']['x']),
            'y'   => $this->base64UrlEncode($details['ec']['y']),
        ];
        return [$dpopKey, $dpopPublicJwk];
    }

    private function computeJwkThumbprint(array $publicJwk): string
    {
        $canonical = json_encode([
            'crv' => $publicJwk['crv'],
            'kty' => $publicJwk['kty'],
            'x'   => $publicJwk['x'],
            'y'   => $publicJwk['y'],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return $this->base64UrlEncode(hash('sha256', $canonical, true));
    }

    private function buildDpopProof(mixed $dpopPrivateKey, array $dpopPublicJwk, string $httpMethod, string $httpUrl): string
    {
        $now = time() + $this->iatOffset;

        $header = [
            'typ' => 'dpop+jwt',
            'alg' => 'ES256',
            'jwk' => [
                'kty' => 'EC',
                'crv' => 'P-256',
                'x'   => $dpopPublicJwk['x'],
                'y'   => $dpopPublicJwk['y'],
            ],
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

    private function buildClientAssertion(string $aud): string
    {
        $now        = time() + $this->iatOffset;
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
            'aud' => $aud,
            'iat' => $now,
            'exp' => $now + 120,
            'jti' => (string) Str::uuid(),
        ];

        $jwt = $this->signJwtWithKey($signingKey, $header, $payload);

        $jwtParts = explode('.', $jwt);
        Log::debug('[Singpass] client_assertion', [
            'payload' => json_decode($this->base64UrlDecode($jwtParts[1]), true),
        ]);

        return $jwt;
    }

    // ─────────────────────────────────────────────────────────────────────
    // ID TOKEN VERIFICATION
    // ─────────────────────────────────────────────────────────────────────

    private function verifyIdTokenSignature(string $idToken): void
    {
        $parts  = explode('.', $idToken);
        $header = json_decode($this->base64UrlDecode($parts[0]), true);
        $kid    = $header['kid'] ?? null;

        $jwks = $this->fileCache('remote_jwks', 300, function () {
            $resp = Http::timeout(10)->get($this->jwksUri);
            if ($resp->failed()) {
                throw new \RuntimeException('[Singpass] Could not fetch Singpass JWKS');
            }
            return $resp->json('keys', []);
        });

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

        $publicKeyPem = $this->jwkToPem($matchingKey);
        $signingInput = $parts[0] . '.' . $parts[1];
        $signature    = $this->base64UrlDecode($parts[2]);

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

    private function decryptJweIdToken(string $idToken): array
    {
        throw new \RuntimeException('[Singpass] JWE decryption not yet implemented — required for production.');
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

    private function derToRawRS(string $der, int $len): string
    {
        $o = 0;
        if (ord($der[$o++]) !== 0x30) throw new \RuntimeException('[Singpass] DER: expected SEQUENCE');
        $sl = ord($der[$o++]);
        if ($sl & 0x80) $o += ($sl & 0x7f);

        $extractInt = function (string $der, int &$o) use ($len): string {
            if (ord($der[$o++]) !== 0x02) throw new \RuntimeException('[Singpass] DER: expected INTEGER');
            $l = ord($der[$o++]);
            $v = substr($der, $o, $l);
            $o += $l;
            if (strlen($v) > $len && ord($v[0]) === 0x00) $v = substr($v, 1);
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
    // JWK → PEM
    // ─────────────────────────────────────────────────────────────────────

    private function jwkToPem(array $jwk): string
    {
        $kty = $jwk['kty'] ?? '';

        if ($kty === 'EC') {
            $x    = $this->base64UrlDecode($jwk['x']);
            $y    = $this->base64UrlDecode($jwk['y']);
            $pub  = "\x04" . $x . $y;
            $oid  = "\x30\x13\x06\x07\x2a\x86\x48\xce\x3d\x02\x01\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07";
            $spki = $oid . "\x03" . chr(strlen($pub) + 1) . "\x00" . $pub;
            $der  = "\x30" . chr(strlen($spki)) . $spki;
            return "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($der), 64, "\n") . "-----END PUBLIC KEY-----\n";
        }

        if ($kty === 'RSA') {
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
            $seq  = $encodeInt($n) . $encodeInt($e);
            $seq  = "\x30" . $encodeLength(strlen($seq)) . $seq;
            $bs   = "\x03" . $encodeLength(strlen($seq) + 1) . "\x00" . $seq;
            $oid  = "\x30\x0d\x06\x09\x2a\x86\x48\x86\xf7\x0d\x01\x01\x01\x05\x00";
            $spki = $oid . $bs;
            $der  = "\x30" . $encodeLength(strlen($spki)) . $spki;
            return "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($der), 64, "\n") . "-----END PUBLIC KEY-----\n";
        }

        throw new \RuntimeException("[Singpass] Unsupported JWK kty: {$kty}");
    }

    // ─────────────────────────────────────────────────────────────────────
    // KEY MANAGEMENT
    // ─────────────────────────────────────────────────────────────────────

    private function loadOrGeneratePrivateKey(): string
    {
        $base64Key = env('SINGPASS_PRIVATE_KEY_BASE64', '');
        if (!empty($base64Key)) {
            $pem = base64_decode($base64Key);
            if (!empty(trim($pem)) && openssl_pkey_get_private($pem)) {
                return $pem;
            }
            Log::warning('[Singpass] SINGPASS_PRIVATE_KEY_BASE64 invalid — falling back to file');
        }

        $fullPath = base_path($this->keyPath);
        if (file_exists($fullPath)) {
            $pem = file_get_contents($fullPath);
            if (!empty(trim($pem))) return $pem;
        }

        Log::info('[Singpass] Generating new EC P-256 signing key');
        $resource = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'prime256v1']);
        if (!$resource) throw new \RuntimeException('[Singpass] Key generation failed: ' . openssl_error_string());
        openssl_pkey_export($resource, $pem);
        $dir = dirname($fullPath);
        if (!is_dir($dir)) mkdir($dir, 0750, true);
        file_put_contents($fullPath, $pem, LOCK_EX);
        chmod($fullPath, 0600);
        Log::warning('[Singpass] New key generated — re-register JWKS in Developer Portal!');
        return $pem;
    }

    private function getKeyDetails(string $pem): array
    {
        $res = openssl_pkey_get_private($pem);
        if (!$res) throw new \RuntimeException('[Singpass] JWKS: could not load key: ' . openssl_error_string());
        $details = openssl_pkey_get_details($res);
        if (!isset($details['ec']['x'], $details['ec']['y'])) throw new \RuntimeException('[Singpass] JWKS: not an EC key');
        return $details;
    }

    // ─────────────────────────────────────────────────────────────────────
    // FILE CACHE
    // ─────────────────────────────────────────────────────────────────────

    private function fileCache(string $key, int $ttlSeconds, callable $callback): mixed
    {
        $path = storage_path('framework/cache/singpass_' . $key . '.json');

        if (file_exists($path)) {
            $data = json_decode(file_get_contents($path), true);
            if (isset($data['expires_at'], $data['value']) && time() < $data['expires_at']) {
                return $data['value'];
            }
        }

        $value = $callback();
        @file_put_contents($path, json_encode(['expires_at' => time() + $ttlSeconds, 'value' => $value]), LOCK_EX);
        return $value;
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