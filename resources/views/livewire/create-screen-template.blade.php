<div class="p-4">
    <h2 class="text-xl font-semibold mb-4">{{ __('setting.Create Screen Template') }}</h2>
    <div class="p-6 bg-white shadow-md rounded dark:bg-white/[0.03]">

        @if (session()->has('message'))
        <div class="p-3 mb-3 bg-green-500 text-white rounded-md">
            {{ session('message') }}
        </div>
        @endif

        <form wire:submit.prevent="save">
            <div class="mb-4">
                <label class="block font-semibold">{{ __('setting.Screen Name') }}</label>
                <input type="text" wire:model="name" class="w-full dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:placeholder:text-white/30 dark:focus:border-brand-800">
                @error('name') <span class="text-error-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block font-semibold mb-2">{{ __('setting.Type') }}</label>
                <select wire:model="type" class="w-full dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    <option value="">{{ __('setting.Select Type') }}</option>
                    <option value="Counter">{{ __('setting.Counter') }}</option>
                    <option value="Category">{{ __('setting.Service') }}</option>
                </select>
                @error('type') <span class="text-error-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block font-semibold mb-2">{{ __('setting.Template') }}</label>
                <select wire:model.live="template" class="w-full dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    <option value="">{{ __('setting.Select Template') }}</option>
                    @foreach ($templates as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
                @error('template') <span class="text-error-500 text-sm">{{ $message }}</span> @enderror
            </div>
        
          
            <div x-data="{ type: @entangle('type') }" x-show="type === 'Counter'" class="mb-4" wire:ignore>
                <label class="block font-semibold">{{ __('setting.Select Counter') }}</label>
                <select wire:model="counter_ids"   class="w-full border p-2 rounded" multiple="multiple" id="counter_select">
                    @foreach ($counters as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
                
            </div>
            @error('counter_ids') <span class="text-error-500 text-sm">{{ $message }}</span> @enderror
            @if($template === 'single_counter')
            <p class="text-sm text-gray-500 mb-3">Note: When you select <b>'Single Counter'</b> as the template, you can only select one counter at a time. If you select multiple, only the first one will be used.</p>
            @endif
           
            <div x-data="{ type: @entangle('type') }" x-show="type === 'Category'" class="mb-4" wire:ignore>
                <label class="block font-semibold">{{ __('setting.Select Services') }}</label>
                <!-- Select All Checkbox -->
                <div class="mb-3">
                    <input type="checkbox" wire:model="selectAll" wire:click="toggleSelectAll">
                    <label class="select-all">{{ __('setting.Select All') }}</label>
                </div>

                <ul class="px-5 flex gap-5 flex-col">
                    @foreach($getcategories as $category)
                    <li>
                        <input type="checkbox" wire:model="selectedCategories" value="{{ $category['id'] }}">
                        {{ $category['name'] }}

                        @if (!empty($category['children']))
                        <ul class="ml-4 flex gap-3 mt-2 flex-col" style="margin-left: 25px;">
                            @foreach ($category['children'] as $subCategory)
                            @include('livewire.partials.category-item', ['category' => $subCategory])
                            @endforeach
                        </ul>
                        @endif
                    </li>
                    @endforeach
                </ul>
            </div>

            <div class="space-y-2 mb-4">
                <label class="block font-semibold">{{ __('setting.Display Screen Tune') }}</label>
                @foreach ($voiceMessages as $key => $label)
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="radio" wire:model="display_screen_tune" value="{{ $key }}"
                        class="text-blue-600 focus:ring focus:ring-blue-300 dark:bg-gray-800 dark:border-gray-600">
                    <span class="text-gray-900 dark:text-gray-200">{{ $label }}</span>
                </label>
                @endforeach
            </div>
            <div class="w-full xl:w-1/2 flex gap-3">
            <button type="submit" class="text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 primary-btn py-2 px-3 font-medium text-white transition-colors rounded-md bg-brand-500 hover:bg-brand-600 flex gap-x-2">{{ __('setting.Save') }}</button>
            <a href="{{ url('screen-templates') }}"
                class="px-4 py-3 text-sm font-medium text-white rounded-lg bg-error-500 shadow-theme-xs hover:bg-error-600">
                {{ __('setting.Cancel') }}
            </a>
            </div>
        </form>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                $(document).ready(function() {
                    $('#category_select').select2({
                        width: '100%'
                    });

                    $('#counter_select').select2({
                        width: '100%'
                    });
                    $('#counter_select').on("change", function(e) {
                        let data = $(this).val();
                        console.log(data);
                        @this.set('counter_ids', data);
                    });
                });
            });
        </script>
    </div>
</div>