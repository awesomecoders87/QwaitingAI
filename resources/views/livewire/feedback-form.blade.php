<div class="p-4">
<h2 class="text-xl font-semibold mb-4">{{ __('setting.Feedback Questions') }}</h2>
<div class="rounded-lg shadow border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-6">
    

    <form wire:submit.prevent="save">
        <div class="space-y-4">
            @foreach ($questions as $index => $question)
                <div class="flex items-center space-x-2">
                    <input type="text" wire:model="questions.{{ $index }}.question" class="mt-0 w-full dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-[42px] w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-2 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="{{ __('setting.Question') }}">
                    <button type="button" wire:click="removeQuestion({{ $index }})" class="bg-red-500 text-white px-3 py-2 rounded-md flex gap-x-2 hover:bg-red-600 flex gap-x-2"><i class="ri-delete-bin-line"></i></button>
                </div>

                @error('questions.' . $index . '.question')
                        <p class="text-error-500 text-sm mt-1">{{ $message }}</p>
                @enderror

            @endforeach
        </div>

        @if ($successMessage)
        <div class="rounded-xl border border-success-500 bg-success-50 p-4 dark:border-success-500/30 dark:bg-success-500/15 mt-2" id="alert">
            <div class="flex items-start gap-3">
                <div class="-mt-0.5 text-success-500">
                    <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M3.70186 12.0001C3.70186 7.41711 7.41711 3.70186 12.0001 3.70186C16.5831 3.70186 20.2984 7.41711 20.2984 12.0001C20.2984 16.5831 16.5831 20.2984 12.0001 20.2984C7.41711 20.2984 3.70186 16.5831 3.70186 12.0001ZM12.0001 1.90186C6.423 1.90186 1.90186 6.423 1.90186 12.0001C1.90186 17.5772 6.423 22.0984 12.0001 22.0984C17.5772 22.0984 22.0984 17.5772 22.0984 12.0001C22.0984 6.423 17.5772 1.90186 12.0001 1.90186ZM15.6197 10.7395C15.9712 10.388 15.9712 9.81819 15.6197 9.46672C15.2683 9.11525 14.6984 9.11525 14.347 9.46672L11.1894 12.6243L9.6533 11.0883C9.30183 10.7368 8.73198 10.7368 8.38051 11.0883C8.02904 11.4397 8.02904 12.0096 8.38051 12.3611L10.553 14.5335C10.7217 14.7023 10.9507 14.7971 11.1894 14.7971C11.428 14.7971 11.657 14.7023 11.8257 14.5335L15.6197 10.7395Z" fill=""></path>
                    </svg>
                </div>

                <div>
                    <h4 class="mb-1 text-sm font-semibold text-gray-800 dark:text-white/90">
                        {{ __('Settings Updated Successfully') }}
                    </h4>
                </div>
            </div>
        </div>
        @endif

        <div class="flex gap-3">
        <button type="button" wire:click="addQuestion" class="mt-4 px-3 py-2 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
            {{ __('setting.Add Question') }}
        </button>

        <button type="submit" class="mt-4 px-3 py-2 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
            {{ __('setting.Save') }}
        </button>
        </div>
    </form>
</div>
</div>
<script>
    document.addEventListener('livewire:init', function() {
        Livewire.on('hide-alert', () => {
            setTimeout(() => {
                document.getElementById('alert')?.remove();
                Livewire.emit('resetSuccessMessage'); // Reset the message in Livewire
            }, 3000);
        });
    });
</script>
