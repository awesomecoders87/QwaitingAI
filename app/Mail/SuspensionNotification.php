<?php 

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SuspensionNotification extends Mailable
{
    use Queueable, SerializesModels;

    // Renamed $message to $notificationMessage to avoid potential conflicts
    public $notificationMessage;
    public $type;
    public $data;

    /**
     * Create a new message instance.
     *
     * @param string $notificationMessage The main message content.
     * @param string $type The type of notification (e.g., 'Appointment Cancellation').
     * @param array $data Additional data related to the booking.
     * @return void
     */
    public function __construct($notificationMessage, $type, $data)
    {
        $this->notificationMessage = $notificationMessage; // Use the new variable name
        $this->type = $type;
        $this->data = $data ?? []; // Ensure data is initialized as an array
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Convert the $this->data array to a JSON string before logging
        Log::error("Email template info: " . $this->notificationMessage . ',' . $this->type . ',' . json_encode($this->data));

        return $this->subject("Your {$this->type} Has Been Cancelled")
                ->view('emails.suspension-notification')
                ->with([
                    'notificationMessage' => $this->notificationMessage ?? '', // Pass with the new variable name
                    'type' => $this->type ?? '',
                    'data' => (array) $this->data ?? [], // Ensure data is cast to array for the view
                ]);
    }
}