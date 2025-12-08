<div class="p-4 space-y-10">
    <form wire:submit.prevent="save" class="">

        <div>
            <h2 class="text-xl font-semibold dark:text-white/90 mb-4">
                {{ __('setting.Addons') }}
            </h2>
        </div>

        <!-- Google Authenticator Section -->
        <div class="bg-white shadow rounded p-6 mb-4 dark:bg-white/[0.03] dark:text-gray-300">
            <h2 class="text-xl font-semibold mb-4">{{ __('setting.TWO FACTOR AUTHENTICATION') }}</h2>

            <div class="space-y-4">
                <div>
                    {{-- <label class="block text-gray-700 font-medium mb-2">{{ __('setting.TWO FACTOR AUTHENTICATION') }}</label> --}}
                    <div class="flex space-x-4">
                        <label class="flex items-center space-x-2">
                            <input wire:model.defer="google_auth_enabled" name="google_auth_enabled" type="radio" value="1" class="text-blue-600">
                            <span>{{ __('setting.Yes') }}</span>
                        </label>
                        <label class="flex items-center space-x-2">
                            <input wire:model.defer="google_auth_enabled" name="google_auth_enabled" type="radio" value="0" class="text-blue-600">
                            <span>{{ __('setting.No') }}</span>
                        </label>
                    </div>
                </div>
                <div>
                    {{ __('setting.You need to Enable this option if you want your customers to start using this feature.') }}
                </div>
{{-- 
                <div class="text-sm text-gray-600">
                    <img src=" {{ url('images/google_authenticator.png') }}" alt="Google Authenticator" class="h-16 mt-4">
                </div>

                <div>
                    {{ __('setting.Google Authenticator is a software-based authenticator by Google that implements two-step verification services using the Time-based One-time Password Algorithm and HMAC-based One-time Password algorithm, for authenticating users of software applications.') }}
                </div> --}}

                {{-- <div class="border border-gray rounded-lg p-4 bg-gray-50">
                    <h3 class="text-xl font-semibold mb-2">{{ __('setting.TWO FACTOR AUTHENTICATION') }}</h3>
                    <ol class="list-decimal list-inside text-sm text-gray-600">
                        <li>{{ __('setting.Use a time-based one-time password (TOTP) authenticator app.') }}</li>
                        <li>{{ __('setting.Scan the QR code or enter this code') }}:</li>
                    </ol>

                    <div>{!! $qrCode !!}</div>

                    <div class="font-mono mt-2 text-lg bg-white p-2 border border-gray rounded text-gray-700">
                        {{ $secretKey }}
                    </div>
                </div> --}}
            </div>
        </div>

        <!-- Okta Section -->
        <div class="bg-white shadow rounded p-6 mb-4  dark:bg-white/[0.03] dark:text-gray-300">
            <h2 class="text-xl font-semibold mb-4">{{ __('setting.Okta Integration') }}</h2>

            <div class="space-y-4">
                <label class="block text-gray-700 font-medium mb-2  dark:text-gray-300">{{ __('setting.Enable Okta') }}</label>
                <div>
                    {{ __('setting.You need to Enable this option if you want your customers to start using this feature.') }}
                </div>
                <div class="flex space-x-4">
                    <label class="flex items-center space-x-2">
                        <input wire:model.defer="okta_enabled" name="okta_enabled" type="radio" value="1" class="text-blue-600">
                        <span>{{ __('setting.Yes') }}</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input wire:model.defer="okta_enabled" name="okta_enabled" type="radio" value="0" class="text-blue-600">
                        <span>{{ __('setting.No') }}</span>
                    </label>
                </div>

                <img src=" {{ url('images/okta.png') }}" alt="Okta" class="h-16 mt-4">

                <div>
                    {{ __('setting.You need to Enable this option if you want your customers to start using this feature.') }}
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="text" placeholder="{{ __('setting.Client Id') }}" wire:model.defer="okta_client_id" class="w-full border-gray-300 rounded-md">
                    <input type="text" placeholder="{{ __('setting.Client Secret Key') }}" wire:model.defer="okta_secret" class="w-full border-gray-300 rounded-md">
                    <input type="text" placeholder="{{ __('setting.Meta Data URL') }}" wire:model.defer="okta_meta_url" class="w-full border-gray-300 rounded-md">
                    <input type="text" placeholder="{{ __('setting.Sign Out URL') }}" wire:model.defer="okta_signout_url" class="w-full border-gray-300 rounded-md">
                </div>
            </div>
        </div>

        <!-- Office 365 Section -->
        <div class="bg-white shadow rounded p-6 mb-4 dark:bg-white/[0.03] dark:text-gray-300">
            <h2 class="text-xl font-semibold mb-4">{{ __('setting.Office 365') }}</h2>

            <div class="space-y-4">
                <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">{{ __('setting.Enable Office 365') }}</label>
                <div>
                    {{ __('setting.You need to enable this option if you want your customers to start using the Office 365 integration feature.') }}
                </div>
                <div class="flex space-x-4">
                    <label class="flex items-center space-x-2">
                        <input wire:model.defer="office_enabled" name="office_enabled" type="radio" value="1" class="text-blue-600">
                        <span>{{ __('setting.Yes') }}</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input wire:model.defer="office_enabled" name="office_enabled" type="radio" value="0" class="text-blue-600">
                        <span>{{ __('setting.No') }}</span>
                    </label>
                </div>

                <img src=" {{ url('images/office365.png') }}" alt="office365" class="h-16 mt-4">

                <div>
                    {{ __('setting.You need to enable this option if you want your customers to start using the Office 365 integration feature.') }}
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="text" placeholder="{{ __('setting.Client Id') }}" wire:model.defer="office_client_id" class="w-full border-gray-300 rounded-md">
                    <input type="text" placeholder="{{ __('setting.Client Secret Key') }}" wire:model.defer="office_secret" class="w-full border-gray-300 rounded-md">
                    <input type="text" placeholder="{{ __('setting.Tenant ID') }}" wire:model.defer="office_tenant_id" class="w-full border-gray-300 rounded-md">
                </div>

                <div class="mt-4 text-sm text-gray-600">
                    <p class="font-semibold">{{ __('setting.Note for Azure AD') }}:</p>
                    <ul class="list-disc list-inside">
                        <li>{{ __('setting.Redirect: '.$domainurl) }}</li>
                        <li>{{ __('setting.Ensure the redirect URI matches exactly in Azure AD.') }}</li>
                    </ul>
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
                text: "Addons updated successfully!",
                icon: "success"
            });
        });
       Livewire.on('addons-verify-code', count => {
            Swal.fire({
                title: "Success!",
                text: "Code verified successfully!",
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
