<div class="max-w-md mx-auto mt-10">
    <div class="bg-white shadow-lg rounded-2xl p-6 space-y-6">
        @if (session('success'))
    <div class="text-green-600 bg-green-100 p-2 rounded mb-4">
        {{ session('success') }}
    </div>
@endif
        <h2 class="text-xl font-semibold text-gray-800">OTP Verification</h2>

        <form wire:submit.prevent="verifyOtp" class="space-y-4">
            <div>
                <label class="block text-gray-600 mb-1">Enter OTP</label>
                <input type="text" wire:model.lazy="otp" maxlength="6" required
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('otp')
                    <span class="text-sm text-red-500">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-medium transition">
                Verify OTP
            </button>
        </form>

        <div class="text-center">
          <div wire:poll.1s="decrementCountdown">
    @if ($countdown > 0)
        <p>You can resend OTP in {{ $countdown }} seconds</p>
    @else
        <button wire:click="resendOtp" class="bg-blue-500 text-white px-4 py-2 rounded">
            Resend OTP
        </button>
    @endif
</div>
</div>
        </div>
    </div>
</div>
