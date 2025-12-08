<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VirtualQueue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'location_id',
        'queue_id',
        'customer_id',
        'ticket_number',
        'queue_type',
        'session_id',
        'meeting_link',
        'meeting_room_id',
        'ai_agent_id',
        'selected_language',
        'conversation_summary',
        'transferred_to_human',
        'human_agent_id',
        'transferred_at',
        'transfer_reason',
        'status',
        'connected_at',
        'completed_at',
        'customer_name',
        'customer_email',
        'customer_phone',
        'wait_time_seconds',
        'call_duration_seconds',
        'ai_duration_seconds',
        'human_duration_seconds',
    ];

    protected $casts = [
        'transferred_to_human' => 'boolean',
        'transferred_at' => 'datetime',
        'connected_at' => 'datetime',
        'completed_at' => 'datetime',
        'wait_time_seconds' => 'integer',
        'call_duration_seconds' => 'integer',
        'ai_duration_seconds' => 'integer',
        'human_duration_seconds' => 'integer',
    ];

    // Relationships
    public function queue()
    {
        return $this->belongsTo(Queue::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function humanAgent()
    {
        return $this->belongsTo(User::class, 'human_agent_id');
    }

    public function aiSession()
    {
        return $this->hasOne(AISession::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['ai_connected', 'human_connected']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeAIAgent($query)
    {
        return $query->where('queue_type', 'ai_agent');
    }

    public function scopeHumanAgent($query)
    {
        return $query->where('queue_type', 'human_agent');
    }

    // Helper methods
    public function isAIAgent()
    {
        return $this->queue_type === 'ai_agent';
    }

    public function isHumanAgent()
    {
        return $this->queue_type === 'human_agent';
    }

    public function transferToHuman($reason = null)
    {
        $this->update([
            'transferred_to_human' => true,
            'transferred_at' => now(),
            'transfer_reason' => $reason,
            'queue_type' => 'human_agent',
        ]);
    }

    public function markAsConnected()
    {
        $this->update([
            'status' => $this->isAIAgent() ? 'ai_connected' : 'human_connected',
            'connected_at' => now(),
        ]);
    }

    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'call_duration_seconds' => now()->diffInSeconds($this->connected_at),
        ]);
    }

    public function generateTicketNumber()
    {
        $prefix = 'VQ';
        $date = now()->format('ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 4));
        return "{$prefix}{$date}{$random}";
    }
}
