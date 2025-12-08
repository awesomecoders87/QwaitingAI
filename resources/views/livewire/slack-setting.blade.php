<div class="p-4 space-y-10">
    <form wire:submit.prevent="save" class="">

        <div>
            <h2 class="text-xl font-semibold dark:text-white/90 mb-4">
                {{ __('setting.slack setting') }}
            </h2>
        </div>


        <div class="bg-white shadow rounded p-6 mb-4 dark:bg-white/[0.03] dark:text-gray-300">

            <div class="space-y-4">

                {{-- <img src=" {{ url('images/salesforce-logo.png') }}" alt="salesforce-logo" class="h-25 mt-4"> --}}

               
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="grid grid-cols-1">
                        <input type="text" placeholder="{{ __('setting.slack user auth token') }}"wire:model="slack_user_auth_token"
                            class="w-full border-gray-300 rounded-md">
                        @error('slack_user_auth_token')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="grid grid-cols-1">
                        <input type="text"
                            placeholder="{{ __('setting.slack user bot auth token') }}"wire:model="slack_user_bot_auth_token"
                            class="w-full border-gray-300 rounded-md">
                        @error('slack_user_bot_auth_token')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                  
                </div>

            </div>

                 <label class="block mt-4 flex items-center">
                    <input type="checkbox" wire:model.defer="status" class="mr-2">
                    {{ __('text.Active/Inactive') }}
                </label>
        </div>

        

        <div class="text-right">
            <button type="submit"
                class="flex items-center justify-center px-3 py-2 font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">{{ __('setting.Save changes') }}</button>
        </div>
    </form>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        Livewire.on('updated', count => {
            Swal.fire({
                title: "Success!",
                text: "Updated successfully!",
                icon: "success"
            });
        });
    });
</script>
