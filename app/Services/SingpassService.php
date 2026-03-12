<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Models\SingpassSetting;
use phpseclib3\Crypt\EC;
use phpseclib3\Crypt\EC\Curves\Prime256v1;

class SingpassService
{
    private string $clientId;
    private string $redirectUri;
    private string $keyPath;
    private string $keyId = 'singpass-key-1';
    private int    $iatOffset;

    // ✅ Issuer from: https://stg-id.singpass.gov.sg/fapi/.well-known/openid-configuration
    private string $issuer        = 'https://stg-id.singpass.gov.sg/fapi';
    private string $parEndpoint   = 'https://stg-id.singpass.gov.sg/fapi/par';
    private string $authEndpoint  = 'https://stg-id.singpass.gov.sg/fapi/auth';
    private string $tokenEndpoint = 'https://stg-id.singpass.gov.sg/fapi/token';
    private string $jwksUri       = 'https://stg-id.singpass.gov.sg/.well-known/keys';

    private ?SingpassSetting $setting = null;

    public function __construct()
    {
        $this->resolveSetting();
        $this->iatOffset = (int) env('SINGPASS_IAT_OFFSET_SECONDS', 0);
    }

    /**
     * Resolve the Singpass setting using a hierarchical lookup.
     */
    private function resolveSetting(): void
    {
        $this->setting = SingpassSetting::where('is_enabled', 1)->first();

        if (!$this->setting) {
            Log::warning('[SingpassService] Singpass is not enabled or not configured.');
            $this->clientId    = '';
            $this->redirectUri = url('/sp/callback');
            return;
        }

        $this->clientId    = $this->setting->client_id;
        $this->redirectUri = url('/sp/callback');

        // Switch endpoints based on environment
        if ($this->setting->environment === 'production') {
            Log::info('[Singpass] Using PRODUCTION endpoints');

            $this->issuer        = 'https://id.singpass.gov.sg/fapi';
            $this->parEndpoint   = 'https://id.singpass.gov.sg/fapi/par';
            $this->authEndpoint  = 'https://id.singpass.gov.sg/fapi/auth';
            $this->tokenEndpoint = 'https://id.singpass.gov.sg/fapi/token';
            $this->jwksUri       = 'https://id.singpass.gov.sg/.well-known/keys';
        } else {
            Log::info('[Singpass] Using STAGING endpoints');

            $this->issuer        = 'https://stg-id.singpass.gov.sg/fapi';
            $this->parEndpoint   = 'https://stg-id.singpass.gov.sg/fapi/par';
            $this->authEndpoint  = 'https://stg-id.singpass.gov.sg/fapi/auth';
            $this->tokenEndpoint = 'https://stg-id.singpass.gov.sg/fapi/token';
            $this->jwksUri       = 'https://stg-id.singpass.gov.sg/.well-known/keys';
        }
 
        // Key ID logic
        $tId = $this->setting->team_id ?? 'default';
        $lId = $this->setting->location_id ?? 'default';
        $this->keyId = "sig-{$tId}-{$lId}";

        Log::info('[Singpass] Loaded client_id: ' . $this->clientId);
    }

    /**
     * Step 1 & 2: PAR + build auth URL
     */
    public function buildAuthorizationRequest(string $state, string $nonce): array
    {
        $codeVerifier  = $this->generateCodeVerifier();
        $codeChallenge = $this->base64UrlEncode(hash('sha256', $codeVerifier, true));

        [$dpopKey, $dpopPublicJwk, $dpopPrivateKeyPem] = $this->generateDpopKeyPair();
        $dpopJkt   = $this->computeJwkThumbprint($dpopPublicJwk);
        $dpopProof = $this->buildDpopProof($dpopKey, $dpopPublicJwk, 'POST', $this->parEndpoint);

        $clientAssertion = $this->buildClientAssertion($this->issuer);

        $parBody = [
            'response_type'               => 'code',
            'client_id'                   => $this->clientId,
            'redirect_uri'                => $this->redirectUri,
            'scope'                       => 'openid name email mobileno user.identity',
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

    /**
     * Step 3: Exchange auth code for tokens
     */
    public function exchangeCodeForToken(
        string $code,
        string $codeVerifier = '',
        string $dpopPem = ''
    ): array {
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

        Log::info('[Singpass] Token exchange request', ['code' => substr($code, 0, 10) . '...']);

        $response = Http::withHeaders($headers)->asForm()->post($this->tokenEndpoint, $payload);

        Log::info('[Singpass] Token exchange response', [
            'status' => $response->status(),
            'body'   => substr($response->body(), 0, 200),
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('Singpass token exchange failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Step 4: Parse id_token
     * Staging & Production both return JWE (5-part encrypted token)
     */
    public function getUserInfo(string $idToken): array
    {
        $parts = explode('.', $idToken);

        if (count($parts) === 5) {
            Log::info('[Singpass] JWE id_token detected — decrypting');
            return $this->decryptJweIdToken($idToken);
        }

        if (count($parts) !== 3) {
            throw new \RuntimeException('[Singpass] Malformed id_token — expected 3 or 5 parts, got ' . count($parts));
        }

        // Plain JWS — verify and return
        $this->verifyIdTokenSignature($idToken);
        $payload = json_decode($this->base64UrlDecode($parts[1]), true);

        if (!is_array($payload)) {
            throw new \RuntimeException('[Singpass] id_token payload is not valid JSON');
        }

        Log::info('[Singpass] id_token claims', ['sub' => $payload['sub'] ?? 'unknown']);
        return $payload;
    }

    /**
     * JWKS endpoint — returns both sig and enc public keys
     */
    public function getJwks(): array
    {
        if (!$this->setting) return ['keys' => []];

        $sigPem     = $this->setting->signing_private_key;
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

        if (!empty($this->setting->enc_private_key)) {
            $encPem = $this->setting->enc_private_key;
            $encRes = openssl_pkey_get_private($encPem);
            if ($encRes) {
                $encDetails = openssl_pkey_get_details($encRes);
                $keys[] = [
                    'kty' => 'EC',
                    'use' => 'enc',
                    'crv' => 'P-256',
                    'alg' => 'ECDH-ES+A128KW',
                    'kid' => 'enc-' . ($this->setting->team_id ?? 'default') . '-' . ($this->setting->location_id ?? 'default'),
                    'x'   => $this->base64UrlEncode($encDetails['ec']['x']),
                    'y'   => $this->base64UrlEncode($encDetails['ec']['y']),
                ];
            }
        }

        return ['keys' => $keys];
    }

    // =========================================================================
    // JWE DECRYPTION (ECDH-ES+A128KW + A256CBC-HS512)
    // Uses phpseclib3 for ECDH since openssl_pkey_derive is unavailable
    // =========================================================================

    private function decryptJweIdToken(string $idToken): array
    {
        $parts = explode('.', $idToken);
        if (count($parts) !== 5) {
            throw new \RuntimeException('[Singpass] JWE must have 5 parts');
        }

        [$headerB64, $encryptedKeyB64, $ivB64, $ciphertextB64, $tagB64] = $parts;

        $header = json_decode($this->base64UrlDecode($headerB64), true);
        Log::info('[Singpass] JWE header', [
            'alg' => $header['alg'] ?? '?',
            'enc' => $header['enc'] ?? '?',
            'kid' => $header['kid'] ?? '?',
        ]);

        if (!$this->setting || empty($this->setting->enc_private_key)) {
            throw new \RuntimeException('[Singpass] Encryption private key not configured in settings');
        }

        $encPem = $this->setting->enc_private_key;

        // Get ephemeral public key from JWE header
        $epk = $header['epk'] ?? null;
        if (!$epk) {
            throw new \RuntimeException('[Singpass] JWE header missing epk');
        }

        // Compute ECDH shared secret using phpseclib3
        $sharedSecret = $this->ecdhComputeSharedSecret($encPem, $epk);
        Log::info('[Singpass] ECDH shared secret computed', ['len' => strlen($sharedSecret)]);

        // Derive KEK using Concat KDF
        $alg    = $header['alg']; // ECDH-ES+A128KW
        $enc    = $header['enc']; // A256CBC-HS512 or A128CBC-HS256
        $keyLen = $this->getKeyLength($alg);
        $kek    = $this->concatKdf($sharedSecret, $alg, $keyLen, $header);
        Log::info('[Singpass] KEK derived', ['len' => strlen($kek), 'alg' => $alg]);

        // Unwrap CEK using AES Key Wrap
        $encryptedKey = $this->base64UrlDecode($encryptedKeyB64);
        $cek          = $this->aesKeyUnwrap($kek, $encryptedKey);
        Log::info('[Singpass] CEK unwrapped', ['len' => strlen($cek)]);

        // Decrypt ciphertext
        $iv         = $this->base64UrlDecode($ivB64);
        $ciphertext = $this->base64UrlDecode($ciphertextB64);
        $tag        = $this->base64UrlDecode($tagB64);

        $plaintext = $this->aesCbcHmacDecrypt($cek, $iv, $ciphertext, $headerB64, $tag, $enc);
        Log::info('[Singpass] JWE decrypted — inner JWT length: ' . strlen($plaintext));

        // The plaintext is the inner JWS
        $innerJwt = trim($plaintext);
        $this->verifyIdTokenSignature($innerJwt);

        $innerParts   = explode('.', $innerJwt);
        $innerPayload = json_decode($this->base64UrlDecode($innerParts[1]), true);

        if (!is_array($innerPayload)) {
            throw new \RuntimeException('[Singpass] Decrypted id_token payload is not valid JSON');
        }

        Log::info('[Singpass] id_token ALL claims', $innerPayload);
        return $innerPayload;
    }

    /**
     * ECDH shared secret using phpseclib3
     */
    private function ecdhComputeSharedSecret(string $ourPrivatePem, array $epkJwk): string
    {
        // Load our private key via phpseclib
        $privateKey = \phpseclib3\Crypt\PublicKeyLoader::loadPrivateKey($ourPrivatePem);

        // Reconstruct ephemeral public key PEM from JWK x,y coordinates
        $x = $this->base64UrlDecode($epkJwk['x']);
        $y = $this->base64UrlDecode($epkJwk['y']);
        $epkPem = $this->ecPointToPem($x, $y);
        $epkPublicKey = \phpseclib3\Crypt\PublicKeyLoader::loadPublicKey($epkPem);

        // Compute ECDH shared secret using DH::computeSecret
        $sharedSecret = \phpseclib3\Crypt\DH::computeSecret($privateKey, $epkPublicKey);

        if (empty($sharedSecret)) {
            throw new \RuntimeException('[Singpass] ECDH shared secret is empty');
        }

        return $sharedSecret;
    }

    /**
     * Build EC public key PEM from raw x,y coordinates
     */
    private function ecPointToPem(string $x, string $y): string
    {
        // Pad to 32 bytes each
        $x = str_pad($x, 32, "\x00", STR_PAD_LEFT);
        $y = str_pad($y, 32, "\x00", STR_PAD_LEFT);

        $pub  = "\x04" . $x . $y;
        $oid  = "\x30\x13\x06\x07\x2a\x86\x48\xce\x3d\x02\x01\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07";
        $spki = $oid . "\x03" . chr(strlen($pub) + 1) . "\x00" . $pub;
        $der  = "\x30" . chr(strlen($spki)) . $spki;

        return "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($der), 64, "\n") . "-----END PUBLIC KEY-----\n";
    }

    /**
     * Concat KDF (JWA RFC 7518 Section 4.6.2)
     */
    private function concatKdf(string $sharedSecret, string $alg, int $keyLenBits, array $header): string
    {
        $keyLenBytes = $keyLenBits / 8;
        $algId       = pack('N', strlen($alg)) . $alg;
        $apu         = !empty($header['apu']) ? $this->base64UrlDecode($header['apu']) : '';
        $apv         = !empty($header['apv']) ? $this->base64UrlDecode($header['apv']) : '';
        $apuData     = pack('N', strlen($apu)) . $apu;
        $apvData     = pack('N', strlen($apv)) . $apv;
        $suppPubInfo = pack('N', $keyLenBits);

        $hashInput = pack('N', 1) . $sharedSecret . $algId . $apuData . $apvData . $suppPubInfo;
        $digest    = hash('sha256', $hashInput, true);

        return substr($digest, 0, $keyLenBytes);
    }

    /**
     * AES Key Unwrap (RFC 3394)
     */
    private function aesKeyUnwrap(string $kek, string $wrappedKey): string
    {
        $n = intdiv(strlen($wrappedKey), 8) - 1;
        $r = [];
        $a = substr($wrappedKey, 0, 8);

        for ($i = 1; $i <= $n; $i++) {
            $r[$i] = substr($wrappedKey, $i * 8, 8);
        }

        $cipherLen = strlen($kek) === 16 ? 'aes-128-ecb' : 'aes-256-ecb';

        for ($j = 5; $j >= 0; $j--) {
            for ($i = $n; $i >= 1; $i--) {
                $t     = ($n * $j) + $i;
                $tBytes = pack('J', $t);
                $aXorT = $a ^ $tBytes;
                $b     = openssl_decrypt(
                    $aXorT . $r[$i],
                    $cipherLen,
                    $kek,
                    OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING
                );
                if ($b === false) {
                    throw new \RuntimeException('[Singpass] AES Key Unwrap decryption failed at j=' . $j . ' i=' . $i);
                }
                $a    = substr($b, 0, 8);
                $r[$i] = substr($b, 8, 8);
            }
        }

        if ($a !== "\xa6\xa6\xa6\xa6\xa6\xa6\xa6\xa6") {
            throw new \RuntimeException('[Singpass] AES Key Unwrap integrity check failed — wrong enc key?');
        }

        $cek = '';
        for ($i = 1; $i <= $n; $i++) {
            $cek .= $r[$i];
        }

        return $cek;
    }

    /**
     * AES-CBC + HMAC decrypt (JWA RFC 7516)
     */
    private function aesCbcHmacDecrypt(
        string $cek,
        string $iv,
        string $ciphertext,
        string $aad,
        string $tag,
        string $enc
    ): string {
        $halfLen = intdiv(strlen($cek), 2);
        $macKey  = substr($cek, 0, $halfLen);
        $encKey  = substr($cek, $halfLen);

        $cipherAlg = match ($enc) {
            'A128CBC-HS256' => 'aes-128-cbc',
            'A256CBC-HS512' => 'aes-256-cbc',
            default         => throw new \RuntimeException('[Singpass] Unsupported enc: ' . $enc),
        };

        $macAlg = match ($enc) {
            'A128CBC-HS256' => 'sha256',
            'A256CBC-HS512' => 'sha512',
            default         => 'sha256',
        };

        // Verify MAC: AAD || IV || Ciphertext || AAD_length_in_bits
        $aadLenBits = strlen($aad) * 8;
        // Pack as 64-bit big-endian
        $aadLenBytes = pack('J', $aadLenBits);
        $macInput    = $aad . $iv . $ciphertext . $aadLenBytes;
        $mac         = hash_hmac($macAlg, $macInput, $macKey, true);
        $expectedTag = substr($mac, 0, $halfLen);

        if (!hash_equals($expectedTag, $tag)) {
            throw new \RuntimeException('[Singpass] JWE MAC verification failed — data tampered or wrong key');
        }

        $plaintext = openssl_decrypt($ciphertext, $cipherAlg, $encKey, OPENSSL_RAW_DATA, $iv);

        if ($plaintext === false) {
            throw new \RuntimeException('[Singpass] JWE AES-CBC decryption failed: ' . openssl_error_string());
        }

        return $plaintext;
    }

    /**
     * Returns key length in bits for Concat KDF based on alg
     */
    private function getKeyLength(string $alg): int
    {
        return match ($alg) {
            'ECDH-ES+A128KW' => 128,
            'ECDH-ES+A256KW' => 256,
            'ECDH-ES'        => 256,
            default          => 128,
        };
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

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
        $details       = openssl_pkey_get_details($dpopKey);
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
        $details       = openssl_pkey_get_details($dpopKey);
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
        $now    = time() + $this->iatOffset;
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

    private function buildClientAssertion(string $aud): string
    {
        $now        = time() + $this->iatOffset;
        $signingKey = openssl_pkey_get_private($this->loadOrGeneratePrivateKey());

        if (!$signingKey) {
            throw new \RuntimeException('[Singpass] Could not load signing key: ' . openssl_error_string());
        }

        $header  = ['alg' => 'ES256', 'typ' => 'JWT', 'kid' => $this->keyId];
        $payload = [
            'iss' => $this->clientId,
            'sub' => $this->clientId,
            'aud' => $aud,
            'iat' => $now,
            'exp' => $now + 120,
            'jti' => (string) Str::uuid(),
        ];

        Log::debug('[Singpass] client_assertion', ['payload' => $payload]);

        return $this->signJwtWithKey($signingKey, $header, $payload);
    }

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
            $n            = $this->base64UrlDecode($jwk['n']);
            $e            = $this->base64UrlDecode($jwk['e']);
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

    private function loadOrGeneratePrivateKey(): string
    {
        if ($this->setting && !empty($this->setting->signing_private_key)) {
            return $this->setting->signing_private_key;
        }

        throw new \RuntimeException('[Singpass] Signing private key not found in database settings');
    }

    private function getKeyDetails(string $pem): array
    {
        $res = openssl_pkey_get_private($pem);
        if (!$res) throw new \RuntimeException('[Singpass] JWKS: could not load key: ' . openssl_error_string());
        $details = openssl_pkey_get_details($res);
        if (!isset($details['ec']['x'], $details['ec']['y'])) throw new \RuntimeException('[Singpass] JWKS: not an EC key');
        return $details;
    }

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