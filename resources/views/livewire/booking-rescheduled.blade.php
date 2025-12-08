<div class="container mx-auto flex justify-center items-center md:min-h-screen">
    <div class="bg-zinc-100 rounded-lg shadow-lg md:p-6 p-2 w-full max-w-xl border rounded-lg mb-3">
        <div class="bg-blue-800 font-bold text-center text-2xl text-white py-4 rounded-md">
            {{ __('text.booking rescheduled') }}</div>
        <div class="mb-6">
            <h4 class="text-xl font-bold text-blue-300 my-4">{{ __('text.booking details') }}</h4>
            <div class="my-4">
                <div class="flex justify-between py-2 flex-wrap gap-3">
                    <div class="text-gray-600">{{ $bookingSetting?->con_app_input_placeholder ? $bookingSetting->con_app_input_placeholder :  __('text.ID') .'( '. __('text.Email').' )' }} :</div>
                    <div class="font-semibold text-right">{{ $booking->refID ?? 'N/A' }}</div>
                </div>
                <div class="flex justify-between py-2 flex-wrap gap-3">
                    <div class="text-gray-600">{{ __('text.appointment date') }}: </div>
                    <div class="font-semibold text-right">
                        {{ $booking->booking_date ? Carbon\Carbon::parse($booking->booking_date)->format('d M, Y') : 'N/A' }}
                    </div>
                </div>
                <div class="flex justify-between py-2 flex-wrap gap-3">
                    <div class="text-gray-600">{{ __('text.appointment time') }}: </div>
                    <div class="font-semibold text-right">{{ $booking->booking_time ?? 'N/A' }}</div>
                </div>
                @if (!empty($booking->category_id))
                    <div class="flex justify-between py-2 flex-wrap gap-3">
                        <div class="text-gray-600">{{ __('text.Level') }} 1: </div>
                        <div class="font-semibold text-right">{{ $booking->categories?->name ?? 'N/A' }}</div>
                    </div>
                @endif
                @if (!empty($booking->sub_category_id))
                    <div class="flex justify-between py-2 flex-wrap gap-3">
                        <div class="text-gray-600">{{ __('text.Level') }} 2: </div>
                        <div class="font-semibold text-right">{{ $booking->sub_category?->name ?? 'N/A' }}</div>
                    </div>
                @endif
                @if (!empty($booking->child_category_id))
                    <div class="flex justify-between py-2 flex-wrap gap-3">
                        <div class="text-gray-600">{{ __('text.Level') }} 3: </div>
                        <div class="font-semibold text-right">{{ $booking->child_category?->name ?? 'N/A' }}</div>
                    </div>
                @endif
                <h4 class="text-xl font-bold text-blue-300">{{ __('text.Contact Details') }}</h4>

                @forelse($userDetails as $key => $userD)
                    <div class="flex justify-between py-2 flex-wrap gap-3">
                        <div class="text-gray-600">{{ App\Models\FormField::viewLabel($teamId, $key) }}</div>
                        <div class="font-semibold text-right">{{ $userD }}</div>
                    </div>
                @empty
                    No user details
                @endforelse
            </div>
            <div class="text-center mb-3 flex justify-center gap-3 flex-wrap">
                <a
                    href="{{ url('/main/booking') }}"class="px-4 py-1 text-white bg-blue-500 rounded hover:bg-blue-700">{{ __('text.New Booking') }}</a>

                <a href="{{ url('/edit-booking/' . $encrypedBookingID) }}"
                    class="px-4 py-1 text-white bg-green-500 rounded hover:bg-yellow-700">{{ __('text.Reschedule') }}
                </a>
                @if ($bookingSetting->cancel_booking_cus == App\Models\AccountSetting::STATUS_ACTIVE)
                    <a href="{{ url('/booking-cancelled/' . $encrypedBookingID) }}"
                        class="px-4 py-1 text-white bg-red-500 rounded hover:bg-red-700">{{ __('text.Cancel') }}</a>
                @endif
                <button class="px-4 py-1 text-white bg-yellow-500 rounded hover:bg-red-700" wire:click="printBooked()"
                    wire:loading.class="opacity-50">{{ __('text.Print') }}</button>
            </div>
        </div>

    </div>
</div>
