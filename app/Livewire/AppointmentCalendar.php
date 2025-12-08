<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Booking;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Livewire\Attributes\Title;

class AppointmentCalendar extends Component
{
    #[Title('Booking Calendar View')]

    public $teamId;
    public $location;
    public $bookings;

	public function mount()
	{
		$this->teamId = tenant('id');
		$this->location = Session::get('selectedLocation');

		// Fetch only required fields for performance
		$rawBookings = Booking::select([
				'id', 'name', 'refID', 'booking_date', 'start_time', 'end_time'
			])
			->where('team_id', $this->teamId)
			->where('location_id', $this->location)
			->whereNotNull('booking_date')
			->whereNotNull('start_time')
			->whereNotNull('end_time')
			->get();

		$this->bookings = $rawBookings->map(function ($booking) {

			try {
				$bookingDate = $booking->booking_date;
				$startTime   = $booking->start_time;
				$endTime     = $booking->end_time;

				// Skip invalid entries early
				if (!$bookingDate || !$startTime || !$endTime) {
					return null;
				}

				// ---------- MULTI-FORMAT PARSING ----------
				$formats = [
					'Y-m-d h:i A',
					'Y-m-d H:i:s',
					'Y-m-d H:i',
					'd/m/Y h:i A',
					'd-m-Y h:i A'
				];

				$start = $end = null;

				foreach ($formats as $format) {
					try {
						$startCandidate = Carbon::createFromFormat($format, "$bookingDate $startTime");
						$endCandidate   = Carbon::createFromFormat($format, "$bookingDate $endTime");

						if ($startCandidate && $endCandidate) {
							$start = $startCandidate;
							$end   = $endCandidate;
							break;
						}
					} catch (\Exception $e) {
						// Try next format silently
					}
				}

				// ---------- FALLBACK PARSING ----------
				if (!$start || !$end) {
					try {
						$dateObj   = Carbon::parse($bookingDate);
						$startObj  = Carbon::parse($startTime);
						$endObj    = Carbon::parse($endTime);

						$start = $dateObj->copy()->setTime($startObj->hour, $startObj->minute);
						$end   = $dateObj->copy()->setTime($endObj->hour, $endObj->minute);
					} catch (\Exception $e) {
						// Final fallback failed → log below
					}
				}

				// If still invalid, log and skip
				if (!$start || !$end) {
					Log::warning("Calendar Booking Skipped — Invalid Date Format", [
						'booking_id'   => $booking->id,
						'booking_date' => $bookingDate,
						'start_time'   => $startTime,
						'end_time'     => $endTime,
					]);
					return null;
				}

				// ---------- BUILD FINAL EVENT ----------
				return [
					'id'    => $booking->id,
					'title' => ($booking->name ?? 'Booking') . ' - ' . ($booking->refID ?? 'N/A'),
					'start' => $start->toIso8601String(),
					'end'   => $end->toIso8601String(),
				];

			} catch (\Exception $e) {

				// Keep ALL LOGGING
				Log::warning('Failed to process booking for calendar', [
					'booking_id'   => $booking->id,
					'error'        => $e->getMessage(),
					'booking_date' => $booking->booking_date,
					'start_time'   => $booking->start_time,
					'end_time'     => $booking->end_time
				]);

				return null;
			}
		})
		->filter()     // remove failed bookings
		->values();    // reindex array
	}


    public function render()
    {
        return view('livewire.appointment-calendar');
    }
}
