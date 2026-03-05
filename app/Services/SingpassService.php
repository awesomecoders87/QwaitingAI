<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SingpassService
{
    protected $clientId;
    protected $redirectUri;
    protected $discoveryUrl;

    public function __construct()
    {
        $this->clientId = env('SINGPASS_CLIENT_ID');
        $this->redirectUri = env('SINGPASS_REDIRECT_URI');
        $this->discoveryUrl = env('SINGPASS_DISCOVERY_URL', 'https://stg-id.singpass.gov.sg/.well-known/openid-configuration');
    }

    public function getAuthUrl($state, $nonce)
    {
        // For simplicity, returning a standard authorization URL assuming discovery or manual constructing
        // Singpass requires PKCE and potentially PAR
        
        $config = $this->getOpenIdConfiguration();
        $authEndpoint = $config['authorization_endpoint'] ?? 'https://stg-id.singpass.gov.sg/auth';

        // Assuming basic flow for now as per Laravel standard integration 
        // Actual Singpass requires Private Key JWT, PKCE, PAR etc.
        $query = http_build_query([
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'redirect_uri' => $this->redirectUri,
            'scope' => 'openid', // 'openid name mobile_number email' if needed
            'state' => $state,
            'nonce' => $nonce,
            // 'code_challenge' and 'code_challenge_method' required in production PKCE
        ]);

        return $authEndpoint . '?' . $query;
    }

    public function exchangeCodeForToken($code, $state)
    {
        // MOCK token exchange for template compliance
        // Real implementation requires signed JWT client assertion
        // And calling the Singpass Token Endpoint

        $config = $this->getOpenIdConfiguration();
        $tokenEndpoint = $config['token_endpoint'] ?? 'https://stg-id.singpass.gov.sg/token';

        // Mock response for testing the flow
        if (env('APP_ENV') === 'local' || env('SINGPASS_MOCK') === true) {
            return [
                'access_token' => Str::random(40),
                'id_token' => 'mocked_id_token',
            ];
        }

        // Real implementation logic (simplified)
        $response = Http::asForm()->post($tokenEndpoint, [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
            'client_id' => $this->clientId,
            // 'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
            // 'client_assertion' => $this->generateClientAssertion(),
        ]);

        return $response->json();
    }

    public function getUserInfo($idToken)
    {
        // MOCK extraction for template compliance
        if (env('APP_ENV') === 'local' || env('SINGPASS_MOCK') === true) {
            return [
                'sub' => 'S1234567A', // Mock NRIC/FIN
                'name' => 'Mock User',
                'mobile_number' => '+6591234567',
                'email' => 'mockuser@example.com'
            ];
        }

        // Real logic: Verify the ID Token and extract claims
        // 1. Verify JWS signature using Singpass JWKS
        // 2. Decrypt JWE if encrypted
        // 3. Extract 'sub', 'name', 'mobile_number', etc.

        return [];
    }

    protected function getOpenIdConfiguration()
    {
        if (env('APP_ENV') === 'local' || env('SINGPASS_MOCK') === true) {
            return [];
        }

        return \Illuminate\Support\Facades\Cache::remember('singpass_openid_config', 3600, function () {
            return Http::get($this->discoveryUrl)->json();
        });
    }

    public function getJwks()
    {
        // Return your app's public keys
        return [
            "keys" => [
                // [ "kty" => "EC", "use" => "sig", "crv" => "P-256", "kid" => "...", "x" => "...", "y" => "..." ]
            ]
        ];
    }
}
