<?php

namespace App\Livewire;

use App\Models\SingpassSetting as SingpassSettingModel;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class SingpassSetting extends Component
{
    public string $client_id = '';
    public string $environment = 'staging';
    public bool $is_enabled = false;

    public ?string $teamId = null;
    public ?int $locationId = null;

    public string $jwksUrl = '';
    public string $callbackUrl = '';
    public bool $signing_key_exists = false;
    public bool $enc_key_exists = false;
    public ?string $keys_generated_at = null;

    public function mount(): void
    {
        $this->teamId = tenant('id');
        $this->locationId = (int)Session::get('selectedLocation');

        $setting = SingpassSettingModel::forTeamLocation($this->teamId, $this->locationId)->first();

        if ($setting) {
            $this->client_id = $setting->client_id ?? '';
            $this->environment = $setting->environment ?? 'staging';
            $this->is_enabled = (bool)$setting->is_enabled;
            $this->signing_key_exists = !empty($setting->signing_public_key);
            $this->enc_key_exists = !empty($setting->enc_public_key);
            $this->keys_generated_at = $setting->keys_generated_at
                ? $setting->keys_generated_at->format('d M Y H:i')
                : null;
        }

        // Hardcode production URLs as requested
        $this->jwksUrl = 'https://qwaiting-ai.thevistiq.com/sp/jwks';
        $this->callbackUrl = 'https://qwaiting-ai.thevistiq.com/sp/callback';
    }

    public function save(): void
    {
        $this->validate([
            'client_id' => 'required|string|max:255',
            'environment' => 'required|in:staging,production',
            'is_enabled' => 'boolean',
        ]);

        $setting = SingpassSettingModel::forTeamLocation($this->teamId, $this->locationId)
        ->where('environment', $this->environment)
        ->firstOrNew([
            'team_id'     => $this->teamId,
            'location_id' => $this->locationId,
            'environment' => $this->environment,
        ]);

        // ONLY generate new keys if they do not exist
        if (empty($setting->signing_private_key) || empty($setting->enc_private_key)) {
            // ── Generate Signing Key pair ──────────────────────────────────────────
            $signingKey = openssl_pkey_new([
                'curve_name' => 'prime256v1',
                'private_key_type' => OPENSSL_KEYTYPE_EC,
            ]);

            if (!$signingKey) {
                session()->flash('error', 'Key generation failed: ' . openssl_error_string());
                return;
            }

            openssl_pkey_export($signingKey, $signingPrivatePem);
            $signingDetails = openssl_pkey_get_details($signingKey);
            $signingPublicPem = $signingDetails['key'];

            // ── Generate Encryption Key pair ───────────────────────────────────────
            $encKey = openssl_pkey_new([
                'curve_name' => 'prime256v1',
                'private_key_type' => OPENSSL_KEYTYPE_EC,
            ]);

            if (!$encKey) {
                session()->flash('error', 'Enc key generation failed: ' . openssl_error_string());
                return;
            }

            openssl_pkey_export($encKey, $encPrivatePem);
            $encDetails = openssl_pkey_get_details($encKey);
            $encPublicPem = $encDetails['key'];

            $setting->signing_private_key = $signingPrivatePem;
            $setting->signing_public_key = $signingPublicPem;
            $setting->enc_private_key = $encPrivatePem;
            $setting->enc_public_key = $encPublicPem;
            $setting->keys_generated_at = now();
        }

        // Persist DB (only updates client_id, environment, is_enabled if keys already existed)
        $setting->fill([
            'team_id' => $this->teamId,
            'location_id' => $this->locationId,
            'client_id' => $this->client_id,
            'environment' => $this->environment,
            'is_enabled' => $this->is_enabled,
            'created_by' => $setting->exists ? $setting->created_by : Auth::id(),
        ])->save();

        // ── Clear JWKS cache ───────────────────────────────────────────────────
        Cache::store('file')->forget('singpass_jwks_all');
        Cache::store('file')->forget('singpass_jwks_' . $this->teamId . '_' . $this->locationId);

        // ── Refresh UI state ───────────────────────────────────────────────────
        $this->signing_key_exists = true;
        $this->enc_key_exists = true;
        $this->keys_generated_at = $setting->keys_generated_at ? $setting->keys_generated_at->format('d M Y H:i') : null;

        session()->flash('success', 'Settings saved successfully. Keys are preserved.');
        $this->dispatch('updated');
    }

    public function regenerateKeys(): void
    {
        $setting = SingpassSettingModel::forTeamLocation($this->teamId, $this->locationId)->first();
        if (!$setting) {
            session()->flash('error', 'Please configure and save basic settings first.');
            return;
        }

        // Force clear old keys
        $setting->signing_private_key = null;
        $setting->enc_private_key = null;
        $setting->save();

        // Call save again to trigger generation and caching
        $this->save();
        
        session()->flash('success', 'Keys successfully regenerated! You MUST re-register the JWKS URL in the Singpass Developer Portal.');
        $this->dispatch('keys-regenerated');
    }

    public function render()
    {
        return view('livewire.singpass-setting');
    }
}
