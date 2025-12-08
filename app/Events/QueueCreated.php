<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\QueueStorage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Auth;

class QueueCreated  implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public $queue;

    /**
     * Create a new event instance.
     */
    public function __construct(QueueStorage $queue)
    {

        $this->queue = $queue; // Initialize the $queue property in the constructor
    }

    public function broadcastOn()
    {
        return new Channel('queue-call.'.$this->queue->team_id);
    }

    public function broadcastAs()
    {
        return 'queue-call';
    }
}
