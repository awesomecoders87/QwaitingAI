<style>
    body{background:#ececec}
    .rounded-25{border-radius:15px}
    </style>
<div class="container mx-auto flex justify-center items-start md:min-h-screen">
    <div class="rounded-25 shadow-lg md:p-6 p-2 w-full max-w-lg border mb-3 bg-white">
        <div class="text-center text-2xl text-black py-4">
            <div class="mb-3"><svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="50" height="50" x="0" y="0" viewBox="0 0 96 96" style="enable-background:new 0 0 512 512" xml:space="preserve" class="m-auto"><g><path d="M48 5.5C24.565 5.5 5.5 24.565 5.5 48S24.565 90.5 48 90.5 90.5 71.435 90.5 48 71.435 5.5 48 5.5zm0 80c-20.678 0-37.5-16.822-37.5-37.5S27.322 10.5 48 10.5 85.5 27.322 85.5 48 68.678 85.5 48 85.5z" fill="#000000" opacity="1" data-original="#000000" class=""></path><path d="M67.394 34.32 45.336 56.377 34.019 45.061a2.5 2.5 0 1 0-3.536 3.535L43.568 61.68c.488.489 1.128.733 1.768.733s1.28-.244 1.768-.733l23.825-23.825a2.5 2.5 0 1 0-3.535-3.535z" fill="#000000" opacity="1" data-original="#000000" class=""></path></g></svg></div>
            {{ __('text.booking confirmed') }}</div>
        <div class="mb-6">
            <h4 class="text-xl text-black-800 my-4 border-b pb-3 text-center">{{ __('text.booking details') }}</h4>

            <div class="flex justify-center items-center">{!! $qrCode !!}</div>

            <div class="my-4">
                <div class="flex justify-between py-1 flex-wrap gap-3">
                    <div class="text-gray-500">
                        {{-- {{ $bookingSetting?->con_app_input_placeholder ? $bookingSetting->con_app_input_placeholder : __('text.ID') . '( ' . __('text.Email') . ' )' }} --}}
                        {{__('text.Booking ID')}}
                    </div>
                    <div class="font-semibolds text-right">{{ $booking->refID ?? 'N/A' }}</div>
                </div>
                <div class="flex justify-between py-1 flex-wrap gap-3">
                    <div class="text-gray-500">{{ __('text.appointment date') }} </div>
                    <div class="font-semibolds text-right">
                        {{ $booking->booking_date ? Carbon\Carbon::parse($booking->booking_date)->format('d M, Y') : 'N/A' }}
                    </div>
                </div>
                <div class="flex justify-between py-1 flex-wrap gap-3">
                    <div class="text-gray-500">{{ __('text.appointment time') }} </div>
                    <div class="font-semibolds text-right">{{ $booking->booking_time ?? 'N/A' }}</div>
                </div>
                <div class="flex justify-between py-1 flex-wrap gap-3">
                    <div class="text-gray-500">{{ __('text.branch name') }} </div>
                    <div class="font-semibolds text-right">{{ $locationName ?? 'N/A' }}</div>
                </div>
                @if (!empty($booking->category_id))
                    <div class="flex justify-between py-1 flex-wrap gap-3">
                        <div class="text-gray-500">{{ $level1 }}  </div>
                        <div class="font-semibolds text-right">{{ $booking->categories?->name ?? 'N/A' }}</div>
                    </div>
                @endif
                @if (!empty($booking->sub_category_id))
                    <div class="flex justify-between py-1 flex-wrap gap-3">
                        <div class="text-gray-500">{{ $level2 }} </div>
                        <div class="font-semibolds text-right">{{ $booking->book_sub_category?->name ?? '' }}</div>
                    </div>
                @endif
                @if (!empty($booking->child_category_id))
                    <div class="flex justify-between py-1 flex-wrap gap-3">
                        <div class="text-gray-500">{{ $level3 }}  </div>
                        <div class="font-semibolds text-right">{{ $booking->book_child_category?->name ?? '' }}</div>
                    </div>
                @endif
                <h4 class="text-xl text-black-800 mt-3 border-b pb-3">{{ __('text.Contact Details') }}</h4>

                @forelse($userDetails as $key => $userD)
                    <div class="flex justify-between py-1 flex-wrap gap-3">
                        <div class="text-gray-500">{{ App\Models\FormField::viewLabel($teamId, $key) }}</div>
                        <div class="font-semibolds text-right"></div>
                    </div>
                @empty
                    No user details
                @endforelse
            </div>
            <div class="mb-3 flexs flex-cols justify-centers gap-3 flex-wrap border-t pt-4">
                @php
                    $encodedLocation = base64_encode($location);
                @endphp
                    <div class=" mb-3"><a href="{{ url('book-appointment/' . $encodedLocation) }}"class="text-gray-900 hover:text-gray-400 flex"><i class="fa mr-3"><svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="18" height="18" x="0" y="0" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M256 .001C114.842.001.001 114.842.001 256S114.842 511.999 256 511.999 511.999 397.159 511.999 256 397.158.001 256 .001zm0 479.998C132.487 479.999 32.001 379.513 32.001 256S132.487 32.001 256 32.001 479.999 132.486 479.999 256c0 123.513-100.486 223.999-223.999 223.999zM398 256c0 8.837-7.164 16-16 16H272v110c0 8.837-7.164 16-16 16s-16-7.163-16-16V272H130c-8.836 0-16-7.163-16-16s7.164-16 16-16h110V130c0-8.837 7.164-16 16-16s16 7.163 16 16v110h110c8.836 0 16 7.164 16 16z" fill="#000000" opacity="1" data-original="#000000" class=""></path></g></svg></i> {{ __('text.New Booking') }}</a></div>
                <!-- <a href="{{ url('add-calendar/'.$encrypedBookingID) }}" target="_blank" class="px-4 py-1 text-white bg-blue-500 rounded hover:bg-blue-700">{{ __('Add Calendar') }}</a> -->
                <div class="relative block text-left">

                @if ($bookingSetting->google_calendar == App\Models\AccountSetting::STATUS_ACTIVE || $bookingSetting->outlook_calendar == App\Models\AccountSetting::STATUS_ACTIVE)
                    <div class="mb-3">
                        <button type="button" class="text-gray-900 hover:text-gray-400 flex" id="dropdownMenuButton" aria-expanded="true">
                           <i class="fa mr-3"><svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="18" height="18" x="0" y="0" viewBox="0 0 32 32" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g transform="matrix(1.0499999999999998,0,0,1.0499999999999998,-0.7999999880790583,-0.7999999999999972)"><path d="M20 19h-3v-3a1 1 0 0 0-2 0v3h-3a1 1 0 0 0 0 2h3v3a1 1 0 0 0 2 0v-3h3a1 1 0 0 0 0-2z" fill="#000000" opacity="1" data-original="#000000" class=""></path><path d="M26 3V2a1 1 0 0 0-2 0v1h-7V2a1 1 0 0 0-2 0v1H8V2a1 1 0 0 0-2 0v1a5.006 5.006 0 0 0-5 5v18a5.006 5.006 0 0 0 5 5h20a5.006 5.006 0 0 0 5-5V8a5.006 5.006 0 0 0-5-5zM6 5v1a1 1 0 0 0 2 0V5h7v1a1 1 0 0 0 2 0V5h7v1a1 1 0 0 0 2 0V5a3 3 0 0 1 3 3v1H3V8a3 3 0 0 1 3-3zm20 24H6a3 3 0 0 1-3-3V11h26v15a3 3 0 0 1-3 3z" fill="#000000" opacity="1" data-original="#000000" class=""></path></g></svg></i> {{ __('Add to calendar') }}
                        </button>
                    </div>
                <!-- Dropdown Menu -->
                <div class="dropdown-menu absolute hidden bg-white text-black shadow-lg rounded mt-2 w-48" aria-labelledby="dropdownMenuButton">
                @if ($bookingSetting->google_calendar == App\Models\AccountSetting::STATUS_ACTIVE)    
                <a href="{{ url('add-calendar/'.$encrypedBookingID) }}" target="_blank" class="block px-4 py-2 text-sm">
                        {{ __('Google Calendar') }}
                    </a>
                @endif
                @if ( $bookingSetting->outlook_calendar == App\Models\AccountSetting::STATUS_ACTIVE)
                    <a href="{{ url('add-calendar-outlook/'.$encrypedBookingID) }}" target="_blank" class="block px-4 py-2 text-sm">
                        {{ __('Outlook') }}
                    </a>
                </div>
                @endif
                @endif
            </div>
                @if ($bookingSetting->allow_reschedule == App\Models\AccountSetting::STATUS_ACTIVE && $booking->is_convert == 'No' && !App\Models\QueueStorage::isBookExist($booking->id))
               

                    <div class=" mb-3"><a href="{{ url('edit-booking/' . $encrypedBookingID) }}"
                            class="text-gray-900 hover:text-gray-400 flex"><i class="fa mr-3"><svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="18" height="18" x="0" y="0" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M256 .001C114.842.001.001 114.842.001 256S114.842 511.999 256 511.999 511.999 397.159 511.999 256 397.158.001 256 .001zm0 479.998C132.487 479.999 32.001 379.513 32.001 256S132.487 32.001 256 32.001 479.999 132.486 479.999 256c0 123.513-100.486 223.999-223.999 223.999zM398 256c0 8.837-7.164 16-16 16H272v110c0 8.837-7.164 16-16 16s-16-7.163-16-16V272H130c-8.836 0-16-7.163-16-16s7.164-16 16-16h110V130c0-8.837 7.164-16 16-16s16 7.163 16 16v110h110c8.836 0 16 7.164 16 16z" fill="#000000" opacity="1" data-original="#000000" class=""></path></g></svg></i> {{ __('text.Reschedule') }}</a>
                        </div>
                @endif
                @if ($bookingSetting->cancel_booking_cus == App\Models\AccountSetting::STATUS_ACTIVE && $booking->is_convert == 'No')
                    @if ($currentTimestamp >= $cancellationDeadlineTimestamp)
                        <div class=" mb-3"><a href="{{ url('booking-cancelled/' . $encrypedBookingID) }}"
                            class="text-gray-900 hover:text-gray-400 flex"><i class="mr-3"><svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="18" height="18" x="0" y="0" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M437.016 74.984c-99.979-99.979-262.075-99.979-362.033.002-99.978 99.978-99.978 262.073.004 362.031 99.954 99.978 262.05 99.978 362.029-.002 99.979-99.956 99.979-262.051 0-362.031zm-30.168 331.86c-83.318 83.318-218.396 83.318-301.691.004-83.318-83.299-83.318-218.377-.002-301.693 83.297-83.317 218.375-83.317 301.691 0s83.316 218.394.002 301.689z" fill="#000000" opacity="1" data-original="#000000" class=""></path><path d="M361.592 150.408c-8.331-8.331-21.839-8.331-30.17 0l-75.425 75.425-75.425-75.425c-8.331-8.331-21.839-8.331-30.17 0s-8.331 21.839 0 30.17l75.425 75.425L150.43 331.4c-8.331 8.331-8.331 21.839 0 30.17 8.331 8.331 21.839 8.331 30.17 0l75.397-75.397 75.419 75.419c8.331 8.331 21.839 8.331 30.17 0 8.331-8.331 8.331-21.839 0-30.17l-75.419-75.419 75.425-75.425c8.331-8.331 8.331-21.838 0-30.17z" fill="#000000" opacity="1" data-original="#000000" class=""></path></g></svg></i> {{ __('text.Cancel') }}</a>
                        </div>
                    @endif

                @endif
               
                <a href="javascript:void(0)"   id="printButton" class="text-gray-900 hover:text-gray-400 flex" 
                    wire:loading.class="opacity-50"><i class="mr-3"><svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="18" height="18" x="0" y="0" viewBox="0 0 248.059 248.059" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M230.559 62.498h-27.785V17.133a7.5 7.5 0 0 0-7.5-7.5H52.785a7.5 7.5 0 0 0-7.5 7.5v45.365H17.5c-9.649 0-17.5 7.85-17.5 17.5v96.225c0 9.649 7.851 17.5 17.5 17.5h27.785v37.203a7.5 7.5 0 0 0 7.5 7.5h142.488a7.5 7.5 0 0 0 7.5-7.5v-37.203h27.785c9.649 0 17.5-7.851 17.5-17.5V79.998c.001-9.649-7.85-17.5-17.499-17.5zM60.285 24.633h127.488v37.865H60.285V24.633zm127.488 198.793H60.285v-74.404h127.488v74.404zm45.286-47.203c0 1.355-1.145 2.5-2.5 2.5h-27.785v-37.201a7.5 7.5 0 0 0-7.5-7.5H52.785a7.5 7.5 0 0 0-7.5 7.5v37.201H17.5c-1.355 0-2.5-1.145-2.5-2.5V79.998c0-1.356 1.145-2.5 2.5-2.5h213.058c1.355 0 2.5 1.144 2.5 2.5v96.225z" fill="#000000" opacity="1" data-original="#000000" class=""></path><circle cx="195.273" cy="105.76" r="10.668" fill="#000000" opacity="1" data-original="#000000" class=""></circle><path d="M158.151 163.822H89.907a7.5 7.5 0 0 0-7.5 7.5 7.5 7.5 0 0 0 7.5 7.5h68.244a7.5 7.5 0 0 0 7.5-7.5 7.5 7.5 0 0 0-7.5-7.5zM158.151 193.623H89.907a7.5 7.5 0 0 0-7.5 7.5 7.5 7.5 0 0 0 7.5 7.5h68.244a7.5 7.5 0 0 0 7.5-7.5c0-4.143-3.357-7.5-7.5-7.5z" fill="#000000" opacity="1" data-original="#000000" class=""></path></g></svg></i>{{ __('text.Print') }}</a>

            </div>
        </div>

    </div>
</div>
<script>
    document.getElementById('dropdownMenuButton').addEventListener('click', function() {
    const menu = document.querySelector('.dropdown-menu');
    menu.classList.toggle('hidden');
});

document.getElementById('printButton').addEventListener('click', function () {
        // Optional: custom logic before dispatch

        // Trigger Livewire event
        Livewire.dispatch('print-button-clicked');
    });

</script>
