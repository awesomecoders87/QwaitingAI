<style> body{background:#ececec;}.rounded-25{border-radius:25px} </style>
<div class="container mx-auto flex justify-center items-center md:min-h-screen">
    <div class="rounded-25 shadow-lg md:p-6 p-2 w-full max-w-lg border mb-3 bg-white">
        <div class="text-center text-2xl text-black">
            <div class="mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="50" height="50" x="0" y="0" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512" xml:space="preserve" class="m-auto"><g><g fill="#000"><path d="M255.575 476.292a219.93 219.93 0 0 1-156.036-64.53c-86.052-86.051-86.052-226.057 0-312.108a219.255 219.255 0 0 1 156.054-64.653c58.95 0 114.37 22.951 156.036 64.653 41.684 41.684 64.653 97.103 64.653 156.054s-22.952 114.37-64.653 156.054a219.989 219.989 0 0 1-156.054 64.53zm.018-405.98a184.107 184.107 0 0 0-131.09 54.306c-35.01 35.011-54.29 81.567-54.29 131.09s19.28 96.062 54.29 131.09c72.28 72.28 189.899 72.298 262.162 0 35.01-35.01 54.307-81.567 54.307-131.09s-19.28-96.062-54.307-131.09a184.192 184.192 0 0 0-131.072-54.307z" fill="#ff0000" opacity="1" data-original="#000000" class=""></path><path d="M180.677 348.25a17.64 17.64 0 0 1-16.334-10.888 17.64 17.64 0 0 1 3.852-19.249l149.804-149.804a17.65 17.65 0 0 1 24.964 0 17.65 17.65 0 0 1 0 24.964L193.159 343.078a17.53 17.53 0 0 1-12.482 5.172z" fill="#ff0000" opacity="1" data-original="#000000" class=""></path><path d="M330.491 348.25a17.59 17.59 0 0 1-12.482-5.172L168.204 193.273a17.654 17.654 0 0 1 24.965-24.964l149.804 149.804a17.632 17.632 0 0 1 3.852 19.249 17.645 17.645 0 0 1-6.512 7.927 17.642 17.642 0 0 1-9.822 2.961z" fill="#ff0000" opacity="1" data-original="#000000" class=""></path></g></g></svg>
</div>
            {{ __('text.booking cancelled') }}</div>
        <div class="mb-6">
            <div class="my-4">

                <div class="text-center py-2 text-black-800 text-xl font-bold">
                    <h4 class="text-xl text-black-800 font-bold"> {{ __('text.Hello') }} {{ !empty($booking->name) ? $booking->name . ',' : '' }}</h4>
                    
                </div>

                <div class="text-center text-black-700">
                    {{ __('text.We are sorry to say that your booking could not be confirmed and has been cancelled.') }}.

                </div>
            </div>
            <div class="my-4">
                <div class="text-center text-gray-500">
                    {{ __('text.The details of the cancelled booking can be found below.') }} </div>
            </div>
            <h4 class="text-xl text-black-800 my-4 border-b pb-3">{{ __('text.booking details') }}</h4>

            <div class="my-4">
                {{-- <div class="flex justify-between py-1 flex-wrap gap-3">
                    <div class="text-gray-600">{{ __('text.Email') }}</div>
                    <div class="font-semibold text-right">{{ $booking->email ?? 'N/A' }}</div>
                </div> --}}
                <div class="flex justify-between py-1 flex-wrap gap-3">
                    <div class="text-gray-600">{{ __('text.appointment date') }} </div>
                    <div class="font-semibold text-right">
                        {{ $booking->booking_date ? Carbon\Carbon::parse($booking->booking_date)->format('d M, Y') : 'N/A' }}
                    </div>
                </div>
                <div class="flex justify-between py-1 flex-wrap gap-3">
                    <div class="text-gray-600">{{ __('text.appointment time') }} </div>
                    <div class="font-semibold text-right">{{ $booking->booking_time ?? 'N/A' }}</div>
                </div>
                @if (!empty($booking->category_id))
                    <div class="flex justify-between py-1 flex-wrap gap-3">
                        <div class="text-gray-600">{{ $level1 }}  </div>
                        <div class="font-semibold text-right">{{ $booking->categories?->name ?? 'N/A' }}</div>
                    </div>
                @endif
                @if (!empty($booking->sub_category_id))
                    <div class="flex justify-between py-1 flex-wrap gap-3">
                        <div class="text-gray-600">{{ $level2 }} </div>
                        <div class="font-semibold text-right">{{ $booking->book_sub_category?->name ?? 'N/A' }}</div>
                    </div>
                @endif
                @if (!empty($booking->child_category_id))
                    <div class="flex justify-between py-1 flex-wrap gap-3">
                        <div class="text-gray-600">{{ $level3 }}  </div>
                        <div class="font-semibold text-right">{{ $booking->book_child_category?->name ?? 'N/A' }}</div>
                    </div>
                @endif
                <h4 class="text-xl text-black-800 my-4 border-b pb-3"> {{ __('text.Contact Details') }} </h4>

                @forelse($userDetails as $key => $userD)
                    <div class="flex justify-between py-1 flex-wrap gap-3">
                        <div class="text-gray-600">{{ App\Models\FormField::viewLabel($teamId, $key) }}</div>
                        <div class="font-semibold text-right">{{ $userD }}</div>
                    </div>
                @empty
                    No user details
                @endforelse
            </div>
            <div class="text-center mb-3">
                <p class="text-gray-600">
                    {{ __('text.Please contact us if you have any question or concerns.') }}
                </p>
            </div>
        </div>

    </div>
</div>
