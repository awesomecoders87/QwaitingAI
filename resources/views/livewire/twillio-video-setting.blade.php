<div class="p-4 space-y-10">
    <form wire:submit.prevent="save" class="">

        <div>
            <h2 class="text-xl font-semibold dark:text-white/90 mb-4">
                {{ __('setting.Twillio Video Setting') }}
            </h2>
        </div>


        <!-- twillio video Section -->
        <div class="bg-white shadow rounded p-6 mb-4  dark:bg-white/[0.03] dark:text-gray-300">
            {{-- <h2 class="text-xl font-semibold mb-4">{{ __('setting.Okta Integration') }}</h2> --}}

            <div class="space-y-4">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="text" placeholder="{{ __('setting.twillio key') }}" wire:model.defer="twillio_video_key" class="w-full border-gray-300 rounded-md">
                    <input type="text" placeholder="{{ __('setting.twillio Secret') }}" wire:model.defer="twillio_video_secret" class="w-full border-gray-300 rounded-md">
                    <input type="text" placeholder="{{ __('setting.twillio accountSid') }}" wire:model.defer="twillio_video_accountSid" class="w-full border-gray-300 rounded-md">
                </div>
            </div>
        </div>



        <div class="text-right">
            <button type="submit" class="flex items-center justify-center px-3 py-2 font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">{{ __('setting.Save changes') }}</button>
        </div>
    </form>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        Livewire.on('addons-updated', count => {
            Swal.fire({
                title: "Success!",
                text: "Updated successfully!",
                icon: "success"
            });
        });

    });
</script>
