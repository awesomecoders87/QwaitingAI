<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SingpassSetup extends Command
{
    protected $signature   = 'singpass:setup';
    protected $description = 'Generate EC P-256 key pair for Singpass FAPI integration and display JWKS public key';

    public function handle(): int
    {
        $keyPath = base_path(env('SINGPASS_PRIVATE_KEY_PATH', 'storage/app/singpass_private.pem'));

        $this->info('==========================================================');
        $this->info('  Singpass FAPI — Key Setup');
        $this->info('==========================================================');

        // ── 1. Generate key ──────────────────────────────────────────────
        if (file_exists($keyPath)) {
            $this->line('');
            $this->comment('Private key already exists at: ' . $keyPath);
            $resource = openssl_pkey_get_private(file_get_contents($keyPath));
        } else {
            $this->line('');
            $this->info('Generating EC P-256 private key...');

            $resource = openssl_pkey_new([
                'private_key_type' => OPENSSL_KEYTYPE_EC,
                'curve_name'       => 'prime256v1',
            ]);

            if (!$resource) {
                $this->error('Failed to generate key: ' . openssl_error_string());
                return self::FAILURE;
            }

            openssl_pkey_export($resource, $pem);

            $dir = dirname($keyPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0750, true);
            }

            file_put_contents($keyPath, $pem);
            $this->info('✔  Private key saved to: ' . $keyPath);
        }

        // ── 2. Extract public key coords ─────────────────────────────────
        $details = openssl_pkey_get_details($resource);
        $x = rtrim(strtr(base64_encode($details['ec']['x']), '+/', '-_'), '=');
        $y = rtrim(strtr(base64_encode($details['ec']['y']), '+/', '-_'), '=');

        $jwks = json_encode([
            'keys' => [[
                'kty' => 'EC',
                'use' => 'sig',
                'crv' => 'P-256',
                'alg' => 'ES256',
                'kid' => 'singpass-key-1',
                'x'   => $x,
                'y'   => $y,
            ]],
        ], JSON_PRETTY_PRINT);

        // ── 3. Display next steps ────────────────────────────────────────
        $this->line('');
        $this->info('Your JWKS (public key) JSON:');
        $this->line('');
        $this->line($jwks);
        $this->line('');

        $jwksUrl = env('APP_URL', 'https://qwaiting-ai.thevistiq.com') . '/sp/jwks';

        $this->info('==========================================================');
        $this->info('  NEXT STEPS — do these BEFORE testing login:');
        $this->info('==========================================================');
        $this->line('');
        $this->line('1. Open your browser and verify this URL returns the JSON above:');
        $this->comment('   ' . $jwksUrl);
        $this->line('');
        $this->line('2. Go to the Singpass Developer Portal:');
        $this->comment('   https://developer.singpass.gov.sg');
        $this->line('');
        $this->line('3. Open your App → Configuration → Edit');
        $this->line('4. In "JWKS endpoint" enter:');
        $this->comment('   ' . $jwksUrl);
        $this->line('5. Click Save / Update App');
        $this->line('');
        $this->line('6. Now click "Login with Singpass" in your app — it should work!');
        $this->line('');

        return self::SUCCESS;
    }
}
