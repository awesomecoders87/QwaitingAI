<div class="p-4">
    <h2 class="text-xl font-semibold text-gray-700">{{ __('setting.SMS API Integration') }}</h2>
<div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-6">
    <form wire:submit.prevent="save" class="space-y-4">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block font-medium text-gray-700">{{ __('setting.Type') }}</label>
                <select wire:model.live="type" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    <option value="">{{ __('setting.Select Type') }}</option>
                    <option value="twillio">{{ __('setting.Twillio') }}</option>
                    <option value="custom">{{ __('setting.Custom') }}</option>
                </select>
                @error('type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                @if ($type == 'twillio')
                <div class="rounded-lg bg-yellow-50 dark:bg-yellow-900/20 p-4 mt-2 text-sm text-yellow-700 dark:text-yellow-300 border border-yellow-200 dark:border-yellow-700">
                    <strong>{{ __('setting.Note') }}:</strong> {{ __('setting.Please provide the following parameters for Twilio integration') }}:
                    <b><code>account_sid</code></b>,
                    <b><code>auth_token</code></b>, {{ __('setting.and') }}
                    <b><code>from</code></b>
                </div>
                @endif

                @if ($type == 'msg91')
                <div class="rounded-lg bg-yellow-50 dark:bg-yellow-900/20 p-4 mt-2 text-sm text-yellow-700 dark:text-yellow-300 border border-yellow-200 dark:border-yellow-700">
                    <strong>{{ __('setting.Note') }}:</strong> {{ __('setting.For Msg91 integration, please provide the following parameters') }}:
                    <ul class="list-disc list-inside mt-2">
                        <li><code>authkey</code>: {{ __('setting.Your Msg91 authentication key') }}.</li>
                        <li><code>senderid</code>: {{ __('setting.Sender ID for SMS messages') }}.</li>
                        <li><code>route</code>: {{ __('setting.Route type') }}.</li>
                        <li><code>templateId</code>: <strong>{{ __('setting.Required only for WhatsApp messages') }}</strong>; {{ __('setting.corresponds to your approved WhatsApp template ID') }}.</li>
                    </ul>
                    <p class="mt-2">
                        <em>{{ __('setting.Note') }}:</em> {{ __('setting.WhatsApp messages must use pre-approved templates') }}. {{ __('setting.You can create and manage templates in your') }} <a href="https://msg91.com/help/how-to-create-a-template-for-whatsapp" class="text-blue-600 underline" target="_blank">{{ __('setting.Msg91 dashboard') }}</a>.
                    </p>
                </div>
                @endif
                @if ($type == 'custom')
                <div class="mt-3">
                 <label class="block font-medium text-gray-700">{{ __('setting.Method') }}</label>
                </div>
                <div class="text-sm text-yellow-700 dark:text-yellow-300 flex" style="justify-content: start; align-items: end;">
                    <select wire:model.defer="request_method" class="h-11 w-1/5 rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 w-48">
                        <option value="">{{ __('setting.Select Method') }}</option>
                        <option value="get">{{ __('setting.GET') }}</option>
                        <option value="post">{{ __('setting.POST') }}</option>
                    </select>
                    @error('request_method') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                    <input type="text" wire:model.defer="sms_api_url" placeholder="{{ __('setting.Enter API URL') }}" class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    @error('sms_api_url') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mt-3">
                <label class="block font-medium text-gray-700">{{ __('setting.Authentication') }}</label>
                </div>

                <div class="text-sm text-yellow-700 dark:text-yellow-300 flex" style="justify-content: start; align-items: end;">
                    <select wire:model.live="authentication" class="h-11 w-1/5 rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 w-48">
                        <option value="no_auth">{{ __('setting.No Auth') }}</option>
                        <option value="bearer_token">{{ __('setting.Bearer Token') }}</option>
                    </select>


                    @if($authentication === 'bearer_token')
                    <input type="text" wire:model="token" placeholder="{{ __('setting.Enter Bearer Token') }}"
                        class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                         @error('token') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    @endif


                </div>

                <div class="flex gap-2 items-center mt-2">
                    <input type="text" wire:model="contact" placeholder="{{ __('setting.Contact') }}" class="w-1/2 dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    <input type="text" wire:model="message" placeholder="{{ __('setting.Message') }}" class="w-1/2 dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                </div>
                @endif

                @if ($type == 'oneway')
                <div class="rounded-lg bg-yellow-50 dark:bg-yellow-900/20 p-4 mt-2 text-sm text-yellow-700 dark:text-yellow-300 border border-yellow-200 dark:border-yellow-700">
                    <strong class="block mb-1 font-semibold">{{ __('setting.Important') }}:</strong>
                    {{ __('setting.Please provide the following parameters for OneWay integration') }}:
                    <span class="font-medium"><code>api_url</code></span> {{ __('setting.and') }}
                    <span class="font-medium"><code>api_key</code></span> {{ __('setting.has parameter') }}.
                </div>
                @endif
            </div>

            <div class="flex gap-2 items-center mt-2">
            <div>
                <label class="block font-medium text-gray-700">{{ __('setting.Status') }}</label>
                <label class="inline-flex items-center">
                    <input type="checkbox" wire:model="status" class="form-checkbox">
                    <span class="ml-2">{{ __('setting.Enabled') }}</span>
                </label>
                @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block font-medium text-gray-700">{{ __('setting.Template') }}</label>
                <label class="inline-flex items-center">
                    <input type="checkbox" wire:model="is_template" class="form-checkbox">
                    <span class="ml-2">{{ __('setting.Enabled') }}</span>
                </label>
                @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            </div>
        </div>

        @if ($type == 'oneway')
        <div class="col-span-8 flex gap-2 items-center mt-4">
            <select wire:model.defer="request_method" class="h-11 w-1/5 rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10">
                <option value="">{{ __('setting.Select Method') }}</option>
                <option value="get">{{ __('setting.GET') }}</option>
                <option value="post">{{ __('setting.POST') }}</option>
            </select>
            @error('request_method') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

            <input type="text" wire:model.defer="sms_api_url" placeholder="{{ __('setting.Enter API URL') }}" class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
            @error('sms_api_url') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        @endif

        <h3 class="text-lg font-semibold text-gray-700 mt-4">{{ __('setting.Parameter of SMS') }}</h3>
        <div class="space-y-2">
            @foreach ($parameter_of_sms as $index => $parameter)
            <div class="flex gap-2 items-center mt-2">
                <input type="text" wire:model="parameter_of_sms.{{ $index }}.parameter_key" placeholder="{{ __('setting.Key') }}" class="w-1/2 dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                <input type="text" wire:model="parameter_of_sms.{{ $index }}.parameter_value" placeholder="{{ __('setting.Value') }}" class="w-1/2 dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                <button type="button" wire:click="removeParameter({{ $index }})" class="px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-error-500 shadow-theme-xs hover:bg-error-600">&times;</button>
            </div>
            @error("parameter_of_sms.{$index}.parameter_key") <span class="text-error-500 text-sm">{{ $message }}</span> @enderror
            @error("parameter_of_sms.{$index}.parameter_value") <span class="text-error-500 text-sm">{{ $message }}</span> @enderror
            @endforeach
        </div>

        @if ($successMessage)
        <div class="rounded-xl border border-success-500 bg-success-50 p-4 dark:border-success-500/30 dark:bg-success-500/15 mt-2" id="alert">
            <div class="flex items-start gap-3">
                <div class="-mt-0.5 text-success-500">
                    <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M3.70186 12.0001C3.70186 7.41711 7.41711 3.70186 12.0001 3.70186C16.5831 3.70186 20.2984 7.41711 20.2984 12.0001C20.2984 16.5831 16.5831 20.2984 12.0001 20.2984C7.41711 20.2984 3.70186 16.5831 3.70186 12.0001ZM12.0001 1.90186C6.423 1.90186 1.90186 6.423 1.90186 12.0001C1.90186 17.5772 6.423 22.0984 12.0001 22.0984C17.5772 22.0984 22.0984 17.5772 22.0984 12.0001C22.0984 6.423 17.5772 1.90186 12.0001 1.90186ZM15.6197 10.7395C15.9712 10.388 15.9712 9.81819 15.6197 9.46672C15.2683 9.11525 14.6984 9.11525 14.347 9.46672L11.1894 12.6243L9.6533 11.0883C9.30183 10.7368 8.73198 10.7368 8.38051 11.0883C8.02904 11.4397 8.02904 12.0096 8.38051 12.3611L10.553 14.5335C10.7217 14.7023 10.9507 14.7971 11.1894 14.7971C11.428 14.7971 11.657 14.7023 11.8257 14.5335L15.6197 10.7395Z"></path>
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

        <button type="button" wire:click="addParameter" class="px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">{{ __('setting.Add Parameter') }}</button>

        <button type="submit" class="px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">{{ __('setting.Save') }}</button>
    </form>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('livewire:init', function() {
        Livewire.on('hide-alert', () => {
            setTimeout(() => {
                document.getElementById('alert')?.remove();
                Livewire.emit('resetSuccessMessage');
            }, 3000);
        });
    });
</script>
<script>
     document.addEventListener("DOMContentLoaded", function () {

    Livewire.on('updated', () => {
        Swal.fire({
            title: 'Success!',
            text: 'updated successfully.',
            icon: 'success',
             confirmButtonText: 'OK'
        }).then((result) => {
             if (result.isConfirmed) {
                window.location.reload(); // Refresh the page when OK is clicked
             }
        });
    });
    });
</script>