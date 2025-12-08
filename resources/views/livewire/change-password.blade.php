<div class="p-4">
<div class="w-full mx-auto p-6 bg-white rounded-lg shadow-md dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
    <h2 class="text-xl font-semibold text-gray-700 dark:text-white mb-4">{{ __('text.Change Password') }}</h2>

    @if (session()->has('success'))
    <div class="mb-3 text-green-500">{{ session('success') }}</div>
    @endif

    <form wire:submit.prevent="updatePassword">
        <!-- Current Password -->
        <div class="mb-4 relative">
            <label class="block text-gray-600 text-sm mb-1  dark:text-white">{{ __('text.Current Password') }}</label>
            <input type="password" wire:model="current_password" id="current_password"
                class="w-full px-3 py-2 border rounded-lg focus:ring focus:ring-blue-300 dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
            <button type="button" class="absolute top-11 right-3"
                onclick="togglePassword('current_password', 'eyeOpen_current', 'eyeClosed_current')">
                <svg id="eyeOpen_current" class="fill-gray-500 dark:fill-gray-400 hidden" width="20" height="20"
                    viewBox="0 0 20 20">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M10 13.86c-2.77 0-5.14-1.73-6.08-4.16C4.86 7.27 7.23 5.54 10 5.54s5.14 1.73 6.08 4.16c-.94 2.43-3.31 4.16-6.08 4.16zM10 4.04c-3.52 0-6.5 2.27-7.58 5.42-.06.16-.06.33 0 .49 1.08 3.15 4.06 5.42 7.58 5.42s6.5-2.27 7.58-5.42c.06-.16.06-.33 0-.49C16.5 6.31 13.52 4.04 10 4.04zm-.01 3.8c-1.03 0-1.86.83-1.86 1.86s.83 1.86 1.86 1.86h.01c1.03 0 1.86-.83 1.86-1.86s-.83-1.86-1.86-1.86h-.01z">
                    </path>
                </svg>

                <svg id="eyeClosed_current" class="fill-gray-500 dark:fill-gray-400" width="20" height="20"
                    viewBox="0 0 20 20">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M4.64 3.58a.85.85 0 00-1.06 1.06l1.28 1.28C3.75 6.84 2.89 8.06 2.42 9.46a.84.84 0 000 .49c1.08 3.15 4.06 5.42 7.58 5.42 1.26 0 2.45-.29 3.5-.84l1.86 1.86a.85.85 0 001.06-1.06L4.64 3.58zM12.36 13.42L10.45 11.5a2 2 0 01-1.31.06L5.92 6.98c-.88.71-1.58 1.65-2 2.72 1.08 3.15 4.06 5.42 7.58 5.42 1.26 0 2.46-.29 3.5-.84L12.36 13.42zm3.71-3.71c-.3.75-.74 1.44-1.28 2.05l1.06 1.06a7.66 7.66 0 002.7-3.78c.06-.16.06-.33 0-.49-1.08-3.15-4.06-5.42-7.58-5.42-1.26 0-2.46.14-3.5.43l1.23 1.23c.41-.09.84-.14 1.27-.14 2.77 0 5.14 1.73 6.08 4.16z">
                    </path>
                </svg>
            </button>
            @error('current_password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- New Password -->
        <div class="mb-4 relative">
            <label class="block text-gray-600 dark:text-white text-sm mb-1">{{ __('text.New Password') }}</label>
            <input type="password" wire:model="new_password" id="new_password"
                class="w-full px-3 py-2 border rounded-lg focus:ring focus:ring-blue-300 dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
            <button type="button" class="absolute top-11 right-3"
                onclick="togglePassword('new_password', 'eyeOpen_new_password', 'eyeClosed_new_password')">
                <svg id="eyeOpen_new_password" class="fill-gray-500 dark:fill-gray-400  hidden" width="20" height="20"
                    viewBox="0 0 20 20">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M10 13.86c-2.77 0-5.14-1.73-6.08-4.16C4.86 7.27 7.23 5.54 10 5.54s5.14 1.73 6.08 4.16c-.94 2.43-3.31 4.16-6.08 4.16zM10 4.04c-3.52 0-6.5 2.27-7.58 5.42-.06.16-.06.33 0 .49 1.08 3.15 4.06 5.42 7.58 5.42s6.5-2.27 7.58-5.42c.06-.16.06-.33 0-.49C16.5 6.31 13.52 4.04 10 4.04zm-.01 3.8c-1.03 0-1.86.83-1.86 1.86s.83 1.86 1.86 1.86h.01c1.03 0 1.86-.83 1.86-1.86s-.83-1.86-1.86-1.86h-.01z">
                    </path>
                </svg>

                <svg id="eyeClosed_new_password" class="fill-gray-500 dark:fill-gray-400" width="20" height="20"
                    viewBox="0 0 20 20">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M4.64 3.58a.85.85 0 00-1.06 1.06l1.28 1.28C3.75 6.84 2.89 8.06 2.42 9.46a.84.84 0 000 .49c1.08 3.15 4.06 5.42 7.58 5.42 1.26 0 2.46-.29 3.5-.84l1.86 1.86a.85.85 0 001.06-1.06L4.64 3.58zM12.36 13.42L10.45 11.5a2 2 0 01-1.31.06L5.92 6.98c-.88.71-1.58 1.65-2 2.72 1.08 3.15 4.06 5.42 7.58 5.42 1.26 0 2.46-.29 3.5-.84L12.36 13.42zm3.71-3.71c-.3.75-.74 1.44-1.28 2.05l1.06 1.06a7.66 7.66 0 002.7-3.78c.06-.16.06-.33 0-.49-1.08-3.15-4.06-5.42-7.58-5.42-1.26 0-2.46.14-3.5.43l1.23 1.23c.41-.09.84-.14 1.27-.14 2.77 0 5.14 1.73 6.08 4.16z">
                    </path>
                </svg>
            </button>
            @error('new_password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Confirm Password -->
        <div class="mb-4 relative">
            <label class="block text-gray-600 dark:text-white text-sm mb-1">{{ __('text.Confirm Password') }}</label>
            <input type="password" wire:model="new_password_confirmation" id="new_password_confirmation"
                class="w-full px-3 py-2 border rounded-lg focus:ring focus:ring-blue-300 dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
            <button type="button" class="absolute top-11 right-3"
                onclick="togglePassword('new_password_confirmation', 'eyeOpen_new_password_confirmation', 'eyeClosed_new_password_confirmation')">
                <svg id="eyeOpen_new_password_confirmation" class="fill-gray-500 dark:fill-gray-400 hidden" width="20" height="20"
                    viewBox="0 0 20 20">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M10 13.86c-2.77 0-5.14-1.73-6.08-4.16C4.86 7.27 7.23 5.54 10 5.54s5.14 1.73 6.08 4.16c-.94 2.43-3.31 4.16-6.08 4.16zM10 4.04c-3.52 0-6.5 2.27-7.58 5.42-.06.16-.06.33 0 .49 1.08 3.15 4.06 5.42 7.58 5.42s6.5-2.27 7.58-5.42c.06-.16.06-.33 0-.49C16.5 6.31 13.52 4.04 10 4.04zm-.01 3.8c-1.03 0-1.86.83-1.86 1.86s.83 1.86 1.86 1.86h.01c1.03 0 1.86-.83 1.86-1.86s-.83-1.86-1.86-1.86h-.01z">
                    </path>
                </svg>

                <svg id="eyeClosed_new_password_confirmation" class="fill-gray-500 dark:fill-gray-400" width="20" height="20"
                    viewBox="0 0 20 20">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M4.64 3.58a.85.85 0 00-1.06 1.06l1.28 1.28C3.75 6.84 2.89 8.06 2.42 9.46a.84.84 0 000 .49c1.08 3.15 4.06 5.42 7.58 5.42 1.26 0 2.45-.29 3.5-.84l1.86 1.86a.85.85 0 001.06-1.06L4.64 3.58zM12.36 13.42L10.45 11.5a2 2 0 01-1.31.06L5.92 6.98c-.88.71-1.58 1.65-2 2.72 1.08 3.15 4.06 5.42 7.58 5.42 1.26 0 2.46-.29 3.5-.84L12.36 13.42zm3.71-3.71c-.3.75-.74 1.44-1.28 2.05l1.06 1.06a7.66 7.66 0 002.7-3.78c.06-.16.06-.33 0-.49-1.08-3.15-4.06-5.42-7.58-5.42-1.26 0-2.46.14-3.5.43l1.23 1.23c.41-.09.84-.14 1.27-.14 2.77 0 5.14 1.73 6.08 4.16z">
                    </path>
                </svg>
            </button>
            @error('new_password_confirmation') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <button type="submit"
            class="px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
            {{ __('text.Save') }}
        </button>
    </form>
</div>
</div>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('livewire:init', function() {
    Livewire.on('swal:success', message => {
        Swal.fire({
            title: 'Success!',
            text: message,
            icon: 'success',
            confirmButtonText: 'OK'
        });
    });

    Livewire.on('swal:error', message => {
        Swal.fire({
            title: 'Error!',
            text: message,
            icon: 'error',
            confirmButtonText: 'Try Again'
        });
    });
});
</script>