<div class="p-4">

    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">{{ __('setting.WhatsApp Message Templates') }}</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Template Selector -->
        <div class="bg-gray-100 dark:bg-white/[0.03] dark:border-gray-600 dark:text-white rounded-md shadow p-4">
            <h3 class="text-lg font-bold mb-2">{{ __('setting.Select WhatsApp Template') }}</h3>
            <ul class="space-y-2">
            @foreach ($templates as $key => $template)
               <li class="flex items-center space-x-2 cursor-pointer"> 
                <label class="flex items-center cursor-pointer">
                    <input type="radio" wire:model.live="selectedTemplate" value="{{ $key }}"
                        class="text-blue-600 focus:ring focus:ring-blue-300 dark:bg-gray-800 dark:border-gray-600">
                    <span class="ml-2 text-gray-900 dark:text-gray-200">{{ __('text.'.$template['name']) }}</span>
                </label>
            </li>
            @endforeach
            </ul>
        </div>

        <!-- Editor -->
        <div class="bg-gray-100 dark:bg-white/[0.03] dark:border-gray-600 dark:text-white rounded-md shadow p-4">
            <h3 class="text-lg font-bold mb-2">{{ __('text.'.$templates[$selectedTemplate]['name']) }}</h3>

             @if($isTemplate)
            <div class="mb-4">
                <label class="block text-gray-700 dark:text-gray-200 text-sm font-bold mb-2">{{ __('setting.Template') }}</label>
                <input type="text" wire:model="template_name"
                    class="w-full p-2 border rounded-md bg-white focus:ring focus:ring-blue-300 dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                
            </div>
            @endif

            <label class="block text-sm font-medium mb-1">{{ __('setting.WhatsApp Message Body') }}</label>
            <textarea wire:model="body" rows="6" class="w-full p-2 rounded-md border  dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"></textarea>

            <div class="mt-4">
                <label class="block text-sm font-medium mb-1">{{ __('setting.Enable/Disable') }}</label>
                <label class="flex items-center">
                    <input type="checkbox" wire:model="status" class="mr-2">
                    <span>{{ $status ? __('setting.Enabled') : __('setting.Disabled') }}</span>
                </label>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium mb-1">{{ __('setting.Insert Variable') }}</label>
                <select wire:model="selectedVariable" class="w-full rounded-md border p-2  dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <option value="">{{ __('setting.Select Variable') }}</option>
                    @foreach ($variables as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <button wire:click="appendVariableToBody"
                        class="mt-2 px-4 py-2 bg-brand-500 text-white rounded-lg hover:bg-brand-600">
                    {{ __('setting.Append Variable') }}
                </button>
            </div>

            @if ($successMessage)
            <div id="alert" class="mt-4 bg-green-100 text-green-800 p-4 rounded">
                {{ $successMessage }}
            </div>
            @endif

            <div class="mt-4 text-right">
                <button wire:click="saveTemplate" class="px-4 py-2 bg-brand-500 text-white rounded hover:bg-brand-600">
                    {{ __('setting.Save') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('hide-alert', () => {
            setTimeout(() => {
                document.getElementById('alert')?.remove();
            }, 3000);
        });
    });
</script>
