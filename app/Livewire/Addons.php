<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\Addon;
use App\Models\Domain;
use Illuminate\Support\Facades\Cache;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use Session;
use Auth;

class Addons extends Component
{

    #[Title('Addons')]

    public int $google_auth_enabled = 0;
    public string $domainurl;
    public string $verification_code = '';
    public string $secretKey = '';

    public int $okta_enabled = 0;
    public string $okta_client_id = '';
    public string $okta_secret = '';
    public string $okta_meta_url = '';
    public string $okta_signout_url = '';

    public int $office_enabled = 0;
    public string $office_client_id = '';
    public string $office_secret = '';
    public string $office_tenant_id = '';
    public string $qrCode;

    public function mount()
    {

        $addon = Addon::where('team_id', auth()->user()->team_id ?? null)->first();
        $name = tenant('name');

        $domain = Domain::where('team_id',tenant('id'))->first();

        $this->domainurl = 'https://'.$domain->domain.'/office365/callback';

        $google2fa = new Google2FA();

        $this->secretKey = $google2fa->generateSecretKey();

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            $name,
            Auth::user()->email,
            $this->secretKey
        );

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $this->qrCode = $writer->writeString($qrCodeUrl);

        if ($addon) {
            $this->google_auth_enabled = $addon->google_auth_enabled;
            $this->verification_code = $addon->verification_code;
            $this->okta_enabled = $addon->okta_enabled;
            $this->okta_client_id = $addon->okta_client_id;
            $this->okta_secret = $addon->okta_secret;
            $this->okta_meta_url = $addon->okta_meta_url;
            $this->okta_signout_url = $addon->okta_signout_url;
            $this->office_enabled = $addon->office_enabled;
            $this->office_client_id = $addon->office_client_id;
            $this->office_secret = $addon->office_secret;
            $this->office_tenant_id = $addon->office_tenant_id;
        }
    }

    public function verifyCode()
    {
        $google2fa = new Google2FA();

        if ($google2fa->verifyKey($this->secretKey, $this->verification_code)) {
             $addon = Addon::updateOrCreate(
            ['team_id' => auth()->user()->team_id ?? null, 'user_id' => Auth::id()], // adjust as needed

            [
                // optional
                'google_auth_enabled' => $this->google_auth_enabled,
                'secretKey' => $this->secretKey,
                 'location_id' => Session::get('selectedLocation'),
                ]
        );
            $this->dispatch('addons-verify-code');
            // Save the key to user or settings model
        } else {
            $this->dispatch('addons-invalid-verify-code');
        }
    }


    public function save()
    {
        $addon = Addon::updateOrCreate(
            ['team_id' => auth()->user()->team_id ?? null, 'user_id' => Auth::id()], // adjust as needed

            [
                'location_id' => Session::get('selectedLocation'),
                // optional
                'google_auth_enabled' => $this->google_auth_enabled,
                'verification_code' => $this->verification_code,
                'secretKey' => $this->secretKey,
                'okta_enabled' => $this->okta_enabled,
                'okta_client_id' => $this->okta_client_id,
                'okta_secret' => $this->okta_secret,
                'okta_meta_url' => $this->okta_meta_url,
                'okta_signout_url' => $this->okta_signout_url,
                'office_enabled' => $this->office_enabled,
                'office_client_id' => $this->office_client_id,
                'office_secret' => $this->office_secret,
                'office_tenant_id' => $this->office_tenant_id,
            ]
        );

        $this->dispatch('addons-updated');
    }

    public function render()
    {
        return view('livewire.addons');
    }
}
