<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;


class DesktopNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public  QueueStorage $queue;
    public function __construct(QueueStorage $queue)
    {
        $this->queue = $queue;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */


    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('desktop-notification.'.$this->queue->team_id),
        ];
    }     
    public function broadcastWith()
    {
        return [
            'title' => 'New Ticket Created',
            'body' => 'Queue No. ' . $this->data['token'],
            'token_notify' => $this->data['token_notify'],
        ];
    }
}
