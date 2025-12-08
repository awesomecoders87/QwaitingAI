<?php

namespace App\Observers;

use App\Models\Queue;
use Filament\Notifications\Notification;

class QueueObserver
{
    /**
     * Handle the Queue "created" event.
     */
    public function created(Queue $queue): void
    {
        // Notification::make()
        // ->title('New call Created and token number is '.$queue->token)
        // ->sendToDatabase($queue);
    }

    /**
     * Handle the Queue "updated" event.
     */
    public function updated(Queue $queue): void
    {
        // Notification::make()
        // ->title('Client updated the call and token number is '.$queue->token)
        // ->sendToDatabase($queue);
    }

    /**
     * Handle the Queue "deleted" event.
     */
    public function deleted(Queue $queue): void
    {
        //
    }

    /**
     * Handle the Queue "restored" event.
     */
    public function restored(Queue $queue): void
    {
        //
    }

    /**
     * Handle the Queue "force deleted" event.
     */
    public function forceDeleted(Queue $queue): void
    {
        //
    }
}
