<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

#[Layout('components.layouts.custom-layout')]
class QRCodeScanner extends Component
{

    public $code;
    public $showData = false;
    public $getData;

     public function updatedCode()
    {

        if (!empty($this->code)) {
            $this->decryptQR();
        }
    }


public function decryptQR()
{

    try {
        $response = Http::post("https://jitendersisodia.qwaiting.com/api/generate-ticket-with-qr", [
            'code' => $this->code,
        ]);

        if ($response->successful()) {
            // Parse the JSON response
            $this->getData = json_decode($response->body(), true);
              if (isset($this->getData['data']) && is_string($this->getData['data'])) {
        $decodedData = json_decode($this->getData['data'], true);
        if ($decodedData !== null) {
            $this->getData['data'] = $decodedData;
        }
    }
            $this->showData = true; // Show the data in the view
            $this->code = '';
        } else {
            // Handle failed response (non-2xx)
            Log::error('QR API failed', [
                'status' => $response->status(),
            ]);
            $this->getData = null;
            $this->showData = false;
        }
    } catch (\Exception $e) {

        // Handle network or server exceptions
        Log::error('QR API exception: ' . $e->getMessage());
        $this->getData = null;
        $this->showData = false;
    }
}

    public function render()
    {
        return view('livewire.qr-code-scanner');
    }
}
