<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\StaffBreak;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class BreakEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public StaffBreak $breakReason;
    /**
     * Create a new event instance.
     */
    public function __construct(StaffBreak $breakReason)
    {
        $this->breakReason = $breakReason;
    }

    public function broadcastOn()
    {
        return new Channel('break-reason.' . $this->breakReason->user_id);
    }

    public function broadcastAs()
    {
        return 'break-reason';
    }
}
