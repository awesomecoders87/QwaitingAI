<div class="p-4 space-y-10">
    <form wire:submit.prevent="save" class="">

        <div>
            <h2 class="text-xl font-semibold dark:text-white/90 mb-4">
                {{ __('setting.saleforce setting') }}
            </h2>
        </div>


        <div class="bg-white shadow rounded p-6 mb-4 dark:bg-white/[0.03] dark:text-gray-300">

            <div class="space-y-4">

                <img src=" {{ url('images/salesforce-logo.png') }}" alt="salesforce-logo" class="w-25 h-25 mt-4">

                <div>
                    {{ __('setting.You need to make Connection with salesforce') }}
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="grid grid-cols-1">
                        <input type="text" placeholder="{{ __('setting.Client Id') }}"wire:model="client_id"
                            class="w-full border-gray-300 rounded-md">
                        @error('client_id')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="grid grid-cols-1">
                        <input type="text"
                            placeholder="{{ __('setting.Client Secret Key') }}"wire:model="client_secret"
                            class="w-full border-gray-300 rounded-md">
                        @error('client_secret')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="grid grid-cols-1">
                        <input type="text" placeholder="{{ __('setting.redirect Uri') }}" wire:model="redirect_uri"
                            class="w-full border-gray-300 bg-gray-300  rounded-md" readonly>
                        @error('redirect_uri')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="mt-4 text-sm text-gray-600">
                 @if($enableConnectionBtn)
                    <a href="{{ route('salesforce.authorize') }}"
                        class="w-1/5 px-3 py-2 font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">Make
                        Connection</a>
                         @endif
                         @if($enableFetchUserBtn)
                    <a href="{{ route('salesforce.getUserList') }}"
                        class="w-1/5 px-3 py-2 mb-3 font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                       Sync User</a>

                      @if(session('success'))
                        <div class="mt-3 p-4 text-sm text-green-800 rounded-lg bg-green-100 border border-green-300">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mt-3 p-4 text-sm text-red-800 rounded-lg bg-red-100 border border-red-300">
                            {{ session('error') }}
                        </div>
                    @endif
                    @endif

                       
                </div>
            </div>
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
      
        Livewire.on('addons-invalid-verify-code', count => {
            Swal.fire({
                title: "Error!",
                text: "Invalid code!",
                icon: "error"
            });
        });
    });
</script>
