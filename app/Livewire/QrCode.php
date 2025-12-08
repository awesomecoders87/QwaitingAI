<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\GenerateQrCode;
use App\Models\Domain;
use App\Models\Queue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\HtmlString;
use Carbon\Carbon;
use Livewire\Attributes\Rule;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;


class QrCode extends Component
{
    #[Title('Qr-Code')]

    public $teamId;
    public $location;
    public $customizeUrl = '';
    public $siteDetail;
    
    // Form properties
    #[Rule('required')]
    public $qrcode_url_status;
    
    #[Rule('nullable|max:50|regex:/^[a-zA-Z0-9-_]+$/')]
    public $url_validity_str;
    
    #[Rule('required')]
    public $status;
    
    public $qr_update_time;
    
    #[Rule('required|max:200')]
    public $url;
    
    #[Rule('required|numeric|min:0|max:999999')]
    public $scan_valid_distance;
    
    #[Rule('required')]
    public $level_ecc;
    
    #[Rule('required')]
    public $size;
    
    public $created_by;
    public $location_id;
    public $teamName;
    public $domain;


    public function mount()
    {

        $user = Auth::user();
        if (!$user->hasPermissionTo('QR Code Setting')) {
            abort(403);
        }

        $this->teamId = tenant('id');
        $this->teamName = tenant('name');
    
        $this->location = Session::get('selectedLocation') ?? '';

        $this->siteDetail = GenerateQrCode::where('team_id', $this->teamId)
            ->where('location_id', $this->location)
            ->first();

        if(empty($this->siteDetail)){
          Queue::timezoneSet();

         $timezone = Session::get('timezone_set') ?? 'UTC';
        $current_time = Carbon::now($timezone);
        $timeInSeconds = strtotime($current_time->toDateTimeString());
        $domain = Domain::where('team_id',$this->teamId)->first();
       
        $customizeUrl = 'https://' . $domain->domain .'/mobile/queue/' . $this->location . '/' . $timeInSeconds;
        GenerateQrCode::where('team_id',$this->teamId)->where('location_id',$this->location)->first();
        GenerateQrCode::create([
                'team_id' => $this->teamId,
                'location_id' => $this->location,
                'qrcode_url_status' =>GenerateQrCode::STATUS_INACTIVE,
                'url_validity_str' => 'queue',
                'url' => $customizeUrl,
                'level_ecc' => 'L',
                'scan_valid_distance' =>  100,
                'size' => 4,
                'created_by' => auth()->user()->id,
                'qr_update_time' => 15,
                'qrupdated_at' => $current_time,
                'status' => 1,
            ]);

             $this->siteDetail = GenerateQrCode::where('team_id', $this->teamId)
            ->where('location_id', $this->location)
            ->first();

            }
        
        $qrUpdated_time = $this->siteDetail->qrupdated_at ?? date('Y-m-d H:i:s');
        $seconds = strtotime($qrUpdated_time);
     
        $this->domain = Domain::where('team_id',$this->teamId)->first();
    
        // $this->customizeUrl = 'https://' . lcfirst(tenant('name')) . '.' . env('DOMAIN') . '/mobile/queue/' . $this->location . '/' . $seconds;
       if(empty($this->siteDetail->url)){
        $this->customizeUrl = 'https://' . $this->domain->domain .'/mobile/queue/' . $this->location . '/' . $seconds;

        if ($this->siteDetail?->qrcode_url_status == GenerateQrCode::STATUC_ACTIVE) {
            if (!empty($this->siteDetail->url_validity_str)) {
                // $this->customizeUrl = 'https://' . lcfirst(tenant('name')) . '.' . env('DOMAIN') . '/mobile/' . $this->siteDetail->url_validity_str . '/' . $this->location . '/' . $seconds;
                $this->customizeUrl = 'https://' . $this->domain->domain .'/mobile/' . $this->siteDetail->url_validity_str . '/' . $this->location . '/' . $seconds;
            }
        }

       }else{
        $this->customizeUrl =$this->siteDetail->url;
       }
      
        // Initialize form fields
        $this->qrcode_url_status = (bool)$this->siteDetail?->qrcode_url_status ?? GenerateQrCode::STATUS_INACTIVE;
        $this->url_validity_str = $this->siteDetail?->url_validity_str ?? null;
        $this->url = $this->customizeUrl;
        $this->level_ecc = $this->siteDetail?->level_ecc ?? 'L';
        $this->scan_valid_distance = $this->siteDetail?->scan_valid_distance ?? 100;
        $this->size = $this->siteDetail?->size ?? 4;
        $this->location_id = $this->location;
        $this->created_by = auth()->user()?->id;
        $this->qr_update_time = $this->siteDetail?->qr_update_time ?? null;
        $this->status = $this->siteDetail?->status ?? 1;
    }

    public function save()
    {
        $this->validate();
          Queue::timezoneSet();

         $timezone = Session::get('timezone_set') ?? 'UTC';
        $current_time = Carbon::now($timezone);
        $timeInSeconds = strtotime($current_time->toDateTimeString());
        // $this->customizeUrl = 'https://' . lcfirst($this->teamName) . '.' . env('DOMAIN') . '/mobile/queue/' . $this->location . '/' . $timeInSeconds;
        $this->customizeUrl = 'https://' . $this->domain->domain .'/mobile/queue/' . $this->location . '/' . $timeInSeconds;
     
        if ($this->qrcode_url_status == GenerateQrCode::STATUC_ACTIVE) {
            if (!empty($this->url_validity_str)) {
                // $this->customizeUrl = 'https://' . lcfirst($this->teamName) . '.' . env('DOMAIN') . '/mobile/' . $this->url_validity_str . '/' . $this->location . '/' . $timeInSeconds;
                $this->customizeUrl = 'https://' . $this->domain->domain .'/mobile/' . $this->url_validity_str . '/' . $this->location . '/' . $timeInSeconds;
            } else {
                $this->customizeUrl = 'https://' . $this->domain->domain .'/mobile/queue/' . '/' . $this->location . '/' . $timeInSeconds;
            }
        } else {
            $this->url_validity_str = 'queue';
        }

        $qrUpdateTime = ($this->status == 1) ? $this->qr_update_time : null;

        $data = GenerateQrCode::updateOrCreate(
            [
                'team_id' => $this->teamId,
                'location_id' => $this->location,
            ],
            [
                'qrcode_url_status' => $this->qrcode_url_status ?? GenerateQrCode::STATUS_INACTIVE,
                'url_validity_str' => $this->url_validity_str,
                'url' => $this->customizeUrl,
                'level_ecc' => $this->level_ecc,
                'scan_valid_distance' => $this->scan_valid_distance ?? 100,
                'size' => $this->size ?? 4,
                'location_id' => $this->location ?? '',
                'created_by' => auth()->user()?->id,
                'qr_update_time' => $qrUpdateTime,
                'qrupdated_at' => $current_time,
                'status' => $this->status ?? 1,
            ]
        );

        $updateTime = Carbon::parse($data->qrupdated_at);
        $currenttime = Carbon::now();
        $minutesDifference = $currenttime->diffInMinutes($updateTime);
        $qrupdating_time = $data->qr_update_time;
        
        if ($minutesDifference >= $qrupdating_time) {
            // Logic for updating QR code if needed
        }

        $this->url = $this->customizeUrl;
        $this->dispatch('saved');
    
    }

    public function printQrCode()
    {
        $this->dispatch('print-qr-code');
    }

    public function render()
    {
        return view('livewire.qr-code', [
            'levelEccOptions' => GenerateQrCode::getLevelEcc(),
            'sizeOptions' => GenerateQrCode::getSize(),
            'updateTimeOptions' => GenerateQrCode::getupdatetime(),
        ]);
    }
}