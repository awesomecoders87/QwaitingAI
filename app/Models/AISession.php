<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AISession extends Model
{
    use HasFactory;

    protected $table = 'ai_sessions';  // Specify exact table name

    protected $fillable = [
        'team_id',
        'virtual_queue_id',
        'session_id',
        'ai_model',
        'ai_voice',
        'language',
        'avatar_url',
        'conversation_history',
        'customer_query',
        'ai_response',
        'query_resolved',
        'escalated',
        'escalation_reason',
        'escalated_at',
        'customer_sentiment',
        'satisfaction_score',
        'message_count',
        'ai_response_time_ms',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'conversation_history' => 'array',
        'query_resolved' => 'boolean',
        'escalated' => 'boolean',
        'escalated_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'message_count' => 'integer',
        'ai_response_time_ms' => 'integer',
        'satisfaction_score' => 'integer',
    ];

    // Relationships
    public function virtualQueue()
    {
        return $this->belongsTo(VirtualQueue::class);
    }

    // Helper methods
    public function addMessage($role, $content)
    {
        $history = $this->conversation_history ?? [];
        $history[] = [
            'role' => $role,
            'content' => $content,
            'timestamp' => now()->toIso8601String(),
        ];

        $this->update([
            'conversation_history' => $history,
            'message_count' => count($history),
        ]);
    }

    public function escalate($reason)
    {
        $this->update([
            'escalated' => true,
            'escalation_reason' => $reason,
            'escalated_at' => now(),
        ]);

        // Also update the virtual queue
        $this->virtualQueue->transferToHuman($reason);
    }

    public function analyzeSentiment($text)
    {
        // Simple sentiment analysis - can be replaced with AI service
        $negativeWords = ['angry', 'frustrated', 'unhappy', 'disappointed', 'terrible', 'bad', 'worst'];
        $positiveWords = ['happy', 'satisfied', 'great', 'excellent', 'good', 'wonderful', 'amazing'];

        $lowerText = strtolower($text);
        $negativeCount = 0;
        $positiveCount = 0;

        foreach ($negativeWords as $word) {
            if (str_contains($lowerText, $word)) {
                $negativeCount++;
            }
        }

        foreach ($positiveWords as $word) {
            if (str_contains($lowerText, $word)) {
                $positiveCount++;
            }
        }

        if ($negativeCount > $positiveCount) {
            return 'negative';
        } elseif ($positiveCount > $negativeCount) {
            return 'positive';
        }

        return 'neutral';
    }

    public function updateSentiment($text)
    {
        $sentiment = $this->analyzeSentiment($text);
        $this->update(['customer_sentiment' => $sentiment]);
        return $sentiment;
    }

    public function getDurationInMinutes()
    {
        if ($this->started_at && $this->ended_at) {
            return $this->started_at->diffInMinutes($this->ended_at);
        }
        return 0;
    }
}
