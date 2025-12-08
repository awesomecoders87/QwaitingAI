<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VirtualQueueSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'location_id',
        'enable_virtual_queue',
        'enable_ai_agent',
        'enable_human_agent',
        'ai_provider',
        'ai_api_key',
        'ai_model',
        'ai_voice',
        'supported_languages',
        'default_language',
        'ai_avatar_type',
        'ai_avatar_url',
        'heygen_avatar_id',
        'heygen_api_key',
        'video_provider',
        'video_api_key',
        'video_api_secret',
        'auto_transfer_on_failure',
        'max_ai_attempts',
        'transfer_timeout_seconds',
        'max_concurrent_ai_sessions',
        'max_concurrent_human_sessions',
        'session_timeout_minutes',
        'send_sms_notification',
        'send_email_notification',
        'send_whatsapp_notification',
        'ai_system_prompt',
        'ai_greeting_message',
        'transfer_message',
    ];

    protected $casts = [
        'enable_virtual_queue' => 'boolean',
        'enable_ai_agent' => 'boolean',
        'enable_human_agent' => 'boolean',
        'supported_languages' => 'array',
        'auto_transfer_on_failure' => 'boolean',
        'max_ai_attempts' => 'integer',
        'transfer_timeout_seconds' => 'integer',
        'max_concurrent_ai_sessions' => 'integer',
        'max_concurrent_human_sessions' => 'integer',
        'session_timeout_minutes' => 'integer',
        'send_sms_notification' => 'boolean',
        'send_email_notification' => 'boolean',
        'send_whatsapp_notification' => 'boolean',
    ];

    // Helper methods
    public static function getSettings($teamId, $locationId)
    {
        return self::firstOrCreate(
            ['team_id' => $teamId, 'location_id' => $locationId],
            [
                'enable_virtual_queue' => false,
                'enable_ai_agent' => false,
                'enable_human_agent' => true,
                'ai_provider' => 'openai',
                'ai_model' => 'gpt-4',
                'ai_voice' => 'alloy',
                'default_language' => 'en',
                'supported_languages' => ['en', 'es', 'fr', 'de', 'ar', 'hi'],
                'video_provider' => 'twilio',
                'max_ai_attempts' => 3,
                'transfer_timeout_seconds' => 300,
                'max_concurrent_ai_sessions' => 10,
                'max_concurrent_human_sessions' => 5,
                'session_timeout_minutes' => 30,
            ]
        );
    }

    public function isVirtualQueueEnabled()
    {
        return $this->enable_virtual_queue;
    }

    public function isAIAgentEnabled()
    {
        return $this->enable_ai_agent && $this->enable_virtual_queue;
    }

    public function isHumanAgentEnabled()
    {
        return $this->enable_human_agent && $this->enable_virtual_queue;
    }

    public function getDefaultGreeting($language = 'en')
    {
        if ($this->ai_greeting_message) {
            return $this->ai_greeting_message;
        }

        $greetings = [
            'en' => 'Hello! I am your AI assistant. How can I help you today?',
            'es' => '¡Hola! Soy tu asistente de IA. ¿Cómo puedo ayudarte hoy?',
            'fr' => 'Bonjour! Je suis votre assistant IA. Comment puis-je vous aider aujourd\'hui?',
            'de' => 'Hallo! Ich bin Ihr KI-Assistent. Wie kann ich Ihnen heute helfen?',
            'ar' => 'مرحبا! أنا مساعدك الذكي. كيف يمكنني مساعدتك اليوم؟',
            'hi' => 'नमस्ते! मैं आपका AI सहायक हूं। आज मैं आपकी कैसे मदद कर सकता हूं?',
        ];

        return $greetings[$language] ?? $greetings['en'];
    }

    public function getTransferMessage($language = 'en')
    {
        if ($this->transfer_message) {
            return $this->transfer_message;
        }

        $messages = [
            'en' => 'I\'m transferring you to a human agent who can better assist you. Please wait...',
            'es' => 'Te estoy transfiriendo a un agente humano que puede ayudarte mejor. Por favor espera...',
            'fr' => 'Je vous transfère vers un agent humain qui pourra mieux vous aider. Veuillez patienter...',
            'de' => 'Ich verbinde Sie mit einem menschlichen Agenten, der Ihnen besser helfen kann. Bitte warten...',
            'ar' => 'أقوم بتحويلك إلى وكيل بشري يمكنه مساعدتك بشكل أفضل. يرجى الانتظار...',
            'hi' => 'मैं आपको एक मानव एजेंट से जोड़ रहा हूं जो आपकी बेहतर सहायता कर सकता है। कृपया प्रतीक्षा करें...',
        ];

        return $messages[$language] ?? $messages['en'];
    }
}
