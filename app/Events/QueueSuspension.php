<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;



class QueueSuspension  implements ShouldBroadcastNow

{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $team_id;
    public $location_id;
    /**
     * Create a new event instance.
     */
    public function __construct($team_id,$location_id)
    {

      $this->team_id = $team_id;
      $this->location_id = $location_id;
    }

    public function broadcastOn()
    {
        return new Channel('queue-suspension.'.$this->team_id.'.'.$this->location_id);
    }

     /**
     * Get the broadcast event name.
     */
    public function broadcastAs()
    {
        return 'queue-suspension';
    }


}
