<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\VirtualQueueSetting;
use Illuminate\Support\Facades\Session;

class VirtualQueueSettings extends Component
{
    public $locationId;
    public $teamId;
    public $settings;

    // Feature toggles
    public $enable_virtual_queue;
    public $enable_ai_agent;
    public $enable_human_agent;

    // AI Agent settings
    public $ai_provider;
    public $ai_api_key;
    public $ai_model;
    public $ai_voice;
    public $supported_languages = [];
    public $default_language;

    // Avatar settings
    public $ai_avatar_type;
    public $ai_avatar_url;
    public $heygen_avatar_id;
    public $heygen_api_key;

    // Video call settings
    public $video_provider;
    public $video_api_key;
    public $video_api_secret;

    // Transfer settings
    public $auto_transfer_on_failure;
    public $max_ai_attempts;
    public $transfer_timeout_seconds;

    // Queue settings
    public $max_concurrent_ai_sessions;
    public $max_concurrent_human_sessions;
    public $session_timeout_minutes;

    // Notification settings
    public $send_sms_notification;
    public $send_email_notification;
    public $send_whatsapp_notification;

    // Custom prompts
    public $ai_system_prompt;
    public $ai_greeting_message;
    public $transfer_message;

    protected $rules = [
        'enable_virtual_queue' => 'nullable|boolean',
        'enable_ai_agent' => 'nullable|boolean',
        'enable_human_agent' => 'nullable|boolean',
        'ai_provider' => 'required|string',
        'ai_model' => 'required|string',
        'ai_voice' => 'required|string',
        'default_language' => 'required|string',
        'ai_avatar_type' => 'required|string',
        'video_provider' => 'required|string',
        'max_ai_attempts' => 'required|integer|min:1|max:10',
        'transfer_timeout_seconds' => 'required|integer|min:60|max:600',
        'max_concurrent_ai_sessions' => 'required|integer|min:1|max:100',
        'max_concurrent_human_sessions' => 'required|integer|min:1|max:50',
        'session_timeout_minutes' => 'required|integer|min:5|max:120',
    ];

    public function mount()
    {
        $this->locationId = Session::get('selectedLocation');
        $this->teamId = tenant('id');

        // Load existing settings
        $this->settings = VirtualQueueSetting::getSettings($this->teamId, $this->locationId);
        $this->loadSettings();
    }

    protected function loadSettings()
    {
        $this->enable_virtual_queue = $this->settings->enable_virtual_queue;
        $this->enable_ai_agent = $this->settings->enable_ai_agent;
        $this->enable_human_agent = $this->settings->enable_human_agent;
        
        $this->ai_provider = $this->settings->ai_provider;
        $this->ai_api_key = $this->settings->ai_api_key;
        $this->ai_model = $this->settings->ai_model;
        $this->ai_voice = $this->settings->ai_voice;
        $this->supported_languages = $this->settings->supported_languages ?? ['en'];
        $this->default_language = $this->settings->default_language;
        
        $this->ai_avatar_type = $this->settings->ai_avatar_type;
        $this->ai_avatar_url = $this->settings->ai_avatar_url;
        $this->heygen_avatar_id = $this->settings->heygen_avatar_id;
        $this->heygen_api_key = $this->settings->heygen_api_key;
        
        $this->video_provider = $this->settings->video_provider;
        $this->video_api_key = $this->settings->video_api_key;
        $this->video_api_secret = $this->settings->video_api_secret;
        
        $this->auto_transfer_on_failure = $this->settings->auto_transfer_on_failure;
        $this->max_ai_attempts = $this->settings->max_ai_attempts;
        $this->transfer_timeout_seconds = $this->settings->transfer_timeout_seconds;
        
        $this->max_concurrent_ai_sessions = $this->settings->max_concurrent_ai_sessions;
        $this->max_concurrent_human_sessions = $this->settings->max_concurrent_human_sessions;
        $this->session_timeout_minutes = $this->settings->session_timeout_minutes;
        
        $this->send_sms_notification = $this->settings->send_sms_notification;
        $this->send_email_notification = $this->settings->send_email_notification;
        $this->send_whatsapp_notification = $this->settings->send_whatsapp_notification;
        
        $this->ai_system_prompt = $this->settings->ai_system_prompt;
        $this->ai_greeting_message = $this->settings->ai_greeting_message;
        $this->transfer_message = $this->settings->transfer_message;
    }

    public function save()
    {
        $this->validate();

        $this->settings->update([
            'enable_virtual_queue' => $this->enable_virtual_queue,
            'enable_ai_agent' => $this->enable_ai_agent,
            'enable_human_agent' => $this->enable_human_agent,
            'ai_provider' => $this->ai_provider,
            'ai_api_key' => $this->ai_api_key,
            'ai_model' => $this->ai_model,
            'ai_voice' => $this->ai_voice,
            'supported_languages' => $this->supported_languages,
            'default_language' => $this->default_language,
            'ai_avatar_type' => $this->ai_avatar_type,
            'ai_avatar_url' => $this->ai_avatar_url,
            'heygen_avatar_id' => $this->heygen_avatar_id,
            'heygen_api_key' => $this->heygen_api_key,
            'video_provider' => $this->video_provider,
            'video_api_key' => $this->video_api_key,
            'video_api_secret' => $this->video_api_secret,
            'auto_transfer_on_failure' => $this->auto_transfer_on_failure,
            'max_ai_attempts' => $this->max_ai_attempts,
            'transfer_timeout_seconds' => $this->transfer_timeout_seconds,
            'max_concurrent_ai_sessions' => $this->max_concurrent_ai_sessions,
            'max_concurrent_human_sessions' => $this->max_concurrent_human_sessions,
            'session_timeout_minutes' => $this->session_timeout_minutes,
            'send_sms_notification' => $this->send_sms_notification,
            'send_email_notification' => $this->send_email_notification,
            'send_whatsapp_notification' => $this->send_whatsapp_notification,
            'ai_system_prompt' => $this->ai_system_prompt,
            'ai_greeting_message' => $this->ai_greeting_message,
            'transfer_message' => $this->transfer_message,
        ]);

        session()->flash('success', 'Virtual Queue settings saved successfully!');
    }

    public function render()
    {
        return view('livewire.virtual-queue-settings');
    }
}
