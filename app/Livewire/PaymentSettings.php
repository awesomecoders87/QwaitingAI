<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PaymentSetting;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;
use Auth;

class PaymentSettings extends Component
{

    #[Title('Payment Settings')]

   public string $activeTab = 'payment'; // default tab

   public $team_id;
   public $location_id;
   public $paymentStatus;
   
   // Payment Gateway Tab
    public string $currency = 'USD';

    // Stripe Tab
    public string $apiKey = '';
    public string $apiSecret = '';
    public  $stripeEnable;

    // intergration Tab
    public $third_party_enable;
    public $third_api_key;
    public $third_webhook_url;

    // General Setting Tab
    public bool $enablePayment =false;
    public string $categoryLevel = '1';
    public string $applicableTo = 'appointment';


     public function mount()
    {
        $this->team_id = tenant('id');
        $this->location_id = Session::get('selectedLocation');

        $settings = PaymentSetting::where('team_id', $this->team_id)
            ->where('location_id', $this->location_id)
            ->first();

            if ($settings) {
            $this->currency = $settings->currency ?? 'USD';
            $this->apiKey = $settings->api_key ?? '';
            $this->apiSecret = $settings->api_secret ?? '';
            $this->enablePayment = (bool) $settings->stripe_enable;
            $this->stripeEnable = (bool) $settings->stripe_enable;
            $this->categoryLevel = (string) $settings->category_level;
            $this->applicableTo = $settings->payment_applicable_to ?? 'appointment';
            
            $this->paymentStatus = $this->enablePayment == 1 ? __('setting.Enabled') : __('setting.Disabled');
            
            $this->third_party_enable = (bool) $settings->third_party_enable;
            $this->third_api_key =  $settings->third_api_key;
            $this->third_webhook_url =  $settings->third_webhook_url;
        }
        else
        {
            $this->paymentStatus = __('setting.Disabled');
        }
    }

    public function switchTab(string $tab)
    {
        $this->activeTab = $tab;
    }

  public function savePaymentSettings()
    {
        $this->validate([
            'currency' => 'required|in:USD,EUR,GBP,INR,AUD',
        ]);

        $this->saveOrUpdate([
            'currency' => $this->currency,
        ]);

        $this->notifySuccess('Payment settings saved successfully.');
    }

    public function saveIntegrationSettings()
    {
        $this->validate([
         'third_party_enable' => 'boolean',
            'third_api_key' => $this->third_party_enable ? 'required|string' : 'nullable|string',
            'third_webhook_url' => $this->third_party_enable ? 'required|string' : 'nullable|string',
        ]);

        $this->saveOrUpdate([
            'third_party_enable' => $this->third_party_enable,
            'third_api_key' => $this->third_api_key,
            'third_webhook_url' => $this->third_webhook_url,
        ]);

        $this->notifySuccess('Integration settings saved successfully.');
    }
    public function saveStripeSettings()
    {

        $this->validate([
           'stripeEnable' => 'boolean',
            'apiKey' => $this->stripeEnable ? 'required|string|max:255' : 'nullable|string|max:255',
            'apiSecret' => $this->stripeEnable ? 'required|string|max:255' : 'nullable|string|max:255',
        ]);

        $this->saveOrUpdate([
            'stripe_enable' => $this->stripeEnable,
            'api_key' => $this->apiKey,
            'api_secret' => $this->apiSecret,
        ]);

        $this->notifySuccess('Integration settings saved successfully.');
    }

    public function saveGeneralSettings()
    {
        $this->validate([
            'enablePayment' => 'boolean',
            'categoryLevel' => 'required|integer|in:1,2,3',
            'applicableTo' => 'required|in:appointment,walkin,both',
        ]);

        $this->saveOrUpdate([
            'enable_payment' => $this->enablePayment,
            'category_level' => $this->categoryLevel,
            'payment_applicable_to' => $this->applicableTo,
        ]);

        $this->notifySuccess('General settings saved successfully.');
    }

    protected function saveOrUpdate(array $data)
    {
        PaymentSetting::where('team_id', $this->team_id)
            ->where('location_id', $this->location_id)->updateOrCreate(
            ['team_id'=> $this->team_id,'location_id'=> $this->location_id], 
            $data
        );
    }

     protected function notifySuccess($message)
    {
        // session()->flash('message', $message);
        $this->dispatch('swal:success', [
            'title' => 'Success!',
            'text' => $message,
        ]);
    }

    public function render()
    {
        return view('livewire.payment-settings');
    }
}
