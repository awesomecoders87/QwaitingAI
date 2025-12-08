<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use App\Models\Booking;
use App\Models\Queue;
use App\Models\QueueStorage;
use App\Models\StripeResponse;
use App\Models\User;
use App\Models\Rating;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class DailyOverviewMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $date;
    protected array $bookings; 
    protected array $tickets;
    protected array $revenue;
    protected array $departmentSummary;
    protected array $staffReport;
    protected array $staffFeedback;


    /**
     * Create a new message instance.
     */
    public function __construct($team_id)
    {

            $this->date = Carbon::today()->format('d M, Y');

            $this->bookings = [
                'booked' => Booking::whereDate('created_at', Carbon::today())->where('team_id', $team_id)->count(),
                'completed' => Booking::whereDate('created_at', Carbon::today())->where('is_convert', 'Yes')->where('team_id', $team_id)->count(),
                'confirmed' => Booking::whereDate('created_at', Carbon::today())->where('status', 'Confirmed')->where('team_id', $team_id)->count(),
                'pending' => Booking::whereDate('created_at', Carbon::today())->where('status', 'Pending')->where('team_id', $team_id)->count(),
                'cancelled' => Booking::whereDate('created_at', Carbon::today())->where('status', 'Cancelled')->where('team_id', $team_id)->count(),
                'total' => Booking::whereDate('created_at', Carbon::today())->count()
            ];

            $this->tickets = [
                'total' => Queue::whereDate('created_at', Carbon::today())->where('team_id', $team_id)->count(),
                'completed' => Queue::whereDate('created_at', Carbon::today())->where('team_id', $team_id)->where('status', 'Close')->count(),
                'pending' => Queue::whereDate('created_at', Carbon::today())->where('team_id', $team_id)->where('status', 'Pending')->count(),
                'cancelled' => Queue::whereDate('created_at', Carbon::today())->where('team_id', $team_id)->where('status', 'Cancelled')->count(),
                'ticket_generate_from_booking' => Queue::whereDate('created_at', Carbon::today())->where('team_id', $team_id)->where('ticket_mode', '!=', 'Walk-IN')->count(),
            ];

            $this->revenue = [
                'total_transactions' => StripeResponse::whereDate('created_at', Carbon::today())->where('team_id', $team_id)->count(),
                'total_revenue' => StripeResponse::whereDate('created_at', Carbon::today())->where('team_id', $team_id)->where('status', 'succeeded')->sum('amount'),
            ];

            // Query initialization
            $query = QueueStorage::query();

            // Filter for today's records only (override other date filters)
            $query->whereDate('created_at', Carbon::today());

            // Aggregations and calculations
           $this->departmentSummary = QueueStorage::query()
            ->join('stripe_responses', 'queues_storage.category_id', '=', 'stripe_responses.category_id')
            ->select([
                'queues_storage.category_id',
                DB::raw('COUNT(queues_storage.id) AS total_calls'),
                DB::raw('SUM(CASE WHEN queues_storage.closed_datetime IS NULL AND queues_storage.is_missed = 0 AND queues_storage.status = "Pending" THEN 1 ELSE 0 END) AS pending_calls'),
                DB::raw('SUM(CASE WHEN queues_storage.status = "Cancelled" THEN 1 ELSE 0 END) AS cancel_calls'),
                DB::raw('SUM(CASE WHEN queues_storage.closed_datetime IS NOT NULL AND queues_storage.status = "Close" THEN 1 ELSE 0 END) AS served_calls'),
                DB::raw('SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(queues_storage.closed_datetime, queues_storage.start_datetime)))) AS total_served_time'),
                DB::raw('TIME_FORMAT(SEC_TO_TIME(AVG(TIME_TO_SEC(TIMEDIFF(queues_storage.closed_datetime, queues_storage.start_datetime)))),"%H:%i:%s") AS average_served_time'),
                DB::raw('SUM(stripe_responses.amount) AS total_revenue'), // Add this line
            ])
            ->where('queues_storage.team_id', $team_id)
            ->groupBy('queues_storage.category_id')
            ->orderBy('queues_storage.category_id')
            ->with('category:id,name') // Assuming category is a relationship on QueueStorage
            // ->whereDate('queues_storage.created_at', Carbon::today())
            // ->whereDate('stripe_responses.created_at', Carbon::today())
            ->get()
            ->toArray();

            $this->staffReport = User::whereHas('queues', function ($query) {
                $query->whereDate('created_at', Carbon::today());
            })->whereNotIn('role_id', [1, 2])->where('team_id', $team_id)->get()->toArray();

            $this->staffFeedback = Rating::with('user')->whereDate('created_at', Carbon::now())->where('team_id', $team_id)->get()->toArray();  
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Daily Overview - Qwaiting',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.daily-overview-mail',
            with: [
                'date' => $this->date,
                'bookings' => $this->bookings, 
                'tickets' => $this->tickets,
                'revenue' => $this->revenue,
                'departmentsSummary' => $this->departmentSummary,
                'staffReport' => $this->staffReport,
                'staffFeedback' => $this->staffFeedback,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
