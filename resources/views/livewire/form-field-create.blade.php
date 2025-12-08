<div class="p-4">

<h2 class="text-xl font-semibold mb-4">{{ isset($isEdit) ? __('setting.Edit Form Field') :  __('setting.Add Form Field') }}</h2>
<div class="rounded-lg shadow border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-6">
    <form wire:submit="submit" class="space-y-6">
        <div class="-mx-2.5 flex flex-wrap gap-y-5">
        <div class="w-full px-2.5 xl:w-1/2">
        <!-- Title Field - Hidden when title is 'name' or 'phone' -->
        <div class="space-y-2" x-data="{}">
            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-white">{{ __('setting.Title') }} <span class="text-error-500">*</span></label>
            <input type="text" id="title" wire:model="title" maxlength="100" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
            @error('title') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
        </div>
        </div>
        <div class="w-full px-2.5 xl:w-1/2">
        <!-- Type Field - Hidden when title is 'name' or 'phone' -->
        <div class="space-y-2" x-data="{}">
            <label for="type" class="block text-sm font-medium text-gray-700 dark:text-white">{{ __('setting.Type') }}</label>
            <select id="type" wire:model.live="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                @foreach($this->getFieldTypeOptions() as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('type') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
        </div>
        </div>

        <!-- Options for Select field -->
        <div class="space-y-4 w-full px-2.5 xl:w-1/2" x-data="{}" x-show="$wire.get('type') === 'Select'">
            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200  dark:bg-white/[0.03] dark:border-gray-600">
                <h3 class="text-base font-medium text-gray-900 dark:text-white">{{ __('setting.Add options for select field') }}</h3>
                <div class="mt-4 space-y-4">
                    @foreach($options as $index => $option)
                    <div class="flex items-center space-x-3">
                        <div class="flex-1">
                            <input type="text" wire:model="options.{{ $index }}" placeholder="{{ __('setting.Options') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            @error("options.{$index}") <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <button type="button" wire:click="removeOption({{ $index }})" class="inline-flex items-center p-1 border border-transparent rounded-full text-error-500 hover:bg-red-50">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                    @endforeach
                    <button type="button" wire:click="addOption" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200">
                        {{ __('setting.Add option') }}
                    </button>
                </div>
            </div>
        </div>
        <div class="mt-4 flex items-center space-x-2" x-show="$wire.get('type') === 'Select'">
    <input type="checkbox" id="is_multiple_options" wire:model="is_multiple_options" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
    <label for="is_multiple_options" class="text-sm text-gray-700 dark:text-white">
        {{ __('setting.Allow Multiple Selection') }}
    </label>
</div>
        <div class="w-full px-2.5 xl:w-1/2">
        <!-- Label Field -->
        <div class="space-y-2">
            <label for="label" class="block text-sm font-medium text-gray-700 dark:text-white">{{ __('setting.Label') }} <span class="text-error-500">*</span></label>
            <input type="text" id="label" wire:model="label" maxlength="100" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
            @error('label') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
        </div>
        </div>
        <div class="w-full px-2.5 xl:w-1/2">
        <!-- Placeholder Field -->
        <div class="space-y-2">
            <label for="placeholder" class="block text-sm font-medium text-gray-700 dark:text-white">{{ __('setting.Placeholder') }} <span class="text-error-500">*</span></label>
            <input type="text" id="placeholder" wire:model="placeholder" maxlength="100" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
            @error('placeholder') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
        </div>
        </div>
        <div class="w-full px-2.5 xl:w-1/2">
        <!-- Default Value Field -->
        <div class="space-y-2">
            <label for="default_value" class="block text-sm font-medium text-gray-700 dark:text-white">{{ __('setting.Default Value') }}</label>
            <input type="text" id="default_value" wire:model="default_value" maxlength="100" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
            @error('default_value') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
        </div>
        </div>

        <!-- Number Field Settings -->
        <div class="space-y-4 w-full px-2.5 xl:w-1/2" x-data="{}" x-show="$wire.get('type') === 'Number'">
            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200  dark:bg-white/[0.03] dark:border-gray-600">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label for="minimum_number_allowed" class="block text-sm font-medium text-gray-700 dark:text-white">{{ __('setting.Minimum Number Allowed') }} <span class="text-error-500">*</span></label>
                        <input type="number" id="minimum_number_allowed" wire:model="minimum_number_allowed" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        @error('minimum_number_allowed') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-2">
                        <label for="maximum_number_allowed" class="block text-sm font-medium text-gray-700 dark:text-white">{{ __('setting.Maximum Number Allowed') }} <span class="text-error-500">*</span></label>
                        <input type="number" id="maximum_number_allowed" wire:model="maximum_number_allowed" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        @error('maximum_number_allowed') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>
        <div class="w-full px-2.5 xl:w-1/2">
        <!-- Text Field Length Settings -->
        <div class="space-y-4" x-data="{}" x-show="['Phone'].includes($wire.get('type'))">
            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 dark:bg-white/[0.03] dark:border-gray-600">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label for="minimum_length" class="block text-sm font-medium text-gray-700 dark:text-white">{{ __('setting.Minimum Length') }} <span class="text-error-500">*</span></label>
                        <input type="number" id="minimum_length" wire:model="minimum_number_allowed" min="1" max="255" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        @error('minimum_number_allowed') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-2">
                        <label for="maximum_length" class="block text-sm font-medium text-gray-700 dark:text-white">{{ __('setting.Maximum Length') }} <span class="text-error-500">*</span></label>
                        <input type="number" id="maximum_length" wire:model="maximum_number_allowed" min="1" max="255" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        @error('maximum_number_allowed') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>
        </div>
        <div class="w-full px-2.5 xl:w-1/2">
        <!-- Policy Field -->
        <div class="space-y-4" x-data="{}" x-show="$wire.get('type') === 'Policy'">
            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200  dark:bg-white/[0.03] dark:border-gray-600">
                <div class="space-y-2">
                    <label for="policy" class="block text-sm font-medium text-gray-700 dark:text-white">{{ __('setting.Policy') }}</label>
                    <select id="policy" wire:model.live="policy" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="Text">{{ __('setting.Text Field') }}</option>
                        <option value="URL">{{ __('setting.URL') }}</option>
                    </select>
                    @error('policy') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        </div>
        <div class="w-full px-2.5 xl:w-1/2">
        <!-- Policy Content Field -->
        <div class="space-y-4" x-data="{}" x-show="$wire.get('type') === 'Policy' && $wire.get('policy') === 'Text'">
            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 dark:bg-white/[0.03] dark:border-gray-600">
                <div class="space-y-2">
                    <label for="policy_content" class="block text-sm font-medium text-gray-700 dark:text-white">{{ __('setting.Policy Content') }}</label>
                    <div wire:ignore>
                        <div x-data="{
                            editor: null,
                            init() {
                                const that = this;
                                this.editor = ClassicEditor
                                    .create(document.getElementById('policy_content'), {
                                        toolbar: ['heading', '|', 'bold', 'italic', 'bulletedList', 'numberedList', 'blockQuote', 'link', 'undo', 'redo']
                                    })
                                    .then(editor => {
                                        editor.model.document.on('change:data', () => {
                                            @this.set('policy_content', editor.getData());
                                        });
                                    })
                                    .catch(error => {
                                        console.error(error);
                                    });
                            }
                        }">
                            <textarea id="policy_content" wire:model="policy_content" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                        </div>
                    </div>
                    @error('policy_content') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        </div>

        <!-- Policy URL Field -->
<div class="space-y-4 w-full px-2.5 xl:w-1/2" x-data="{}" x-show="$wire.get('type') === 'Policy' && $wire.get('policy') === 'URL'">
    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 dark:bg-white/[0.03] dark:border-gray-600">
        <div class="space-y-2">
            <label for="policy_url" class="block text-sm font-medium text-gray-700 dark:text-white">{{ __('setting.Policy URL') }}</label>
            <input type="url" id="policy_url" wire:model="policy_url" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            @error('policy_url') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
        </div>
    </div>
</div>
<div class="w-full px-2.5 xl:w-1/2">
    <!-- Mandatory Toggle -->
    <div class="space-y-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-white">{{ __('setting.Mandatory') }} <span class="text-error-500">*</span></label>
        <div class="flex bg-gray-200 rounded-lg p-1 dark:bg-white/[0.03] dark:border-gray-600">
            <button type="button" wire:click="$set('mandatory', true)" class="px-4 py-2 rounded-md text-sm font-medium {{ $mandatory ? 'bg-white shadow-sm text-gray-800' : 'text-gray-500' }}">
                {{ __('setting.Yes') }}
            </button>
            <button type="button" wire:click="$set('mandatory', false)" class="px-4 py-2 rounded-md text-sm font-medium {{ !$mandatory ? 'bg-white shadow-sm text-gray-800' : 'text-gray-500' }}">
                {{ __('setting.No') }}
            </button>
        </div>
        @error('mandatory') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
    </div>
</div>
<div class="w-full px-2.5 xl:w-1/2">
    <!-- Ticket Screen Toggle -->
    <div class="space-y-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-white">{{ __('setting.Ticket Screen') }}</label>
        <div class="flex bg-gray-200 rounded-lg p-1 dark:bg-white/[0.03] dark:border-gray-600">
            <button type="button" wire:click="$set('ticket_screen', true)" class="px-4 py-2 rounded-md text-sm font-medium {{ $ticket_screen ? 'bg-white shadow-sm text-gray-800' : 'text-gray-500' }}">
                {{ __('setting.Yes') }}
            </button>
            <button type="button" wire:click="$set('ticket_screen', false)" class="px-4 py-2 rounded-md text-sm font-medium {{ !$ticket_screen ? 'bg-white shadow-sm text-gray-800' : 'text-gray-500' }}">
                {{ __('setting.No') }}
            </button>
        </div>
        @error('ticket_screen') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
    </div>
</div>
<div class="w-full px-2.5 xl:w-1/2">
    <!-- Before Appointment Form Toggle -->
    <div class="space-y-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-white">{{ __('setting.Appointment Form ( SHOW/HIDE )') }}</label>
        <div class="flex bg-gray-200 rounded-lg p-1 dark:bg-white/[0.03] dark:border-gray-600">
            <button type="button" wire:click="$set('before_appointment_form', true)" class="px-4 py-2 rounded-md text-sm font-medium {{ $before_appointment_form ? 'bg-white shadow-sm text-gray-800' : 'text-gray-500' }}">
                {{ __('setting.Yes') }}
            </button>
            <button type="button" wire:click="$set('before_appointment_form', false)" class="px-4 py-2 rounded-md text-sm font-medium {{ !$before_appointment_form ? 'bg-white shadow-sm text-gray-800' : 'text-gray-500' }}">
                {{ __('setting.No') }}
            </button>
        </div>
        @error('before_appointment_form') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
    </div>
</div>
<div class="w-full px-2.5 xl:w-1/2">
    <!-- After Appointment Form Toggle -->
    <div class="space-y-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-white">{{ __('setting.Conversion Form ( SHOW/HIDE )') }}</label>
        <div class="flex bg-gray-200 rounded-lg p-1 dark:bg-white/[0.03] dark:border-gray-600">
            <button type="button" wire:click="$set('after_appointment_form', true)" class="px-4 py-2 rounded-md text-sm font-medium {{ $after_appointment_form ? 'bg-white shadow-sm text-gray-800' : 'text-gray-500' }}">
                {{ __('setting.Yes') }}
            </button>
            <button type="button" wire:click="$set('after_appointment_form', false)" class="px-4 py-2 rounded-md text-sm font-medium {{ !$after_appointment_form ? 'bg-white shadow-sm text-gray-800' : 'text-gray-500' }}">
                {{ __('setting.No') }}
            </button>
        </div>
        @error('after_appointment_form') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
    </div>
</div>
<div class="w-full px-2.5 xl:w-1/2">
    <!-- After Scan Screen Toggle -->
    <div class="space-y-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-white">{{ __('setting.After Scanning ( SHOW/HIDE on APP )') }}</label>
        <div class="flex bg-gray-200 rounded-lg p-1 dark:bg-white/[0.03] dark:border-gray-600">
            <button type="button" wire:click="$set('after_scan_screen', true)" class="px-4 py-2 rounded-md text-sm font-medium {{ $after_scan_screen ? 'bg-white shadow-sm text-gray-800' : 'text-gray-500' }}">
                {{ __('setting.Yes') }}
            </button>
            <button type="button" wire:click="$set('after_scan_screen', false)" class="px-4 py-2 rounded-md text-sm font-medium {{ !$after_scan_screen ? 'bg-white shadow-sm text-gray-800' : 'text-gray-500' }}">
                {{ __('setting.No') }}
            </button>
        </div>
        @error('after_scan_screen') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
    </div>
</div>
</div>
<!-- Advanced Settings Section -->
<div class="space-y-4">
    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 dark:bg-white/[0.03] dark:border-gray-600">
        <p class="text-sm text-gray-500 dark:text-white">{{ __('setting.Please enter a valid regular expression example: /^[0-9]+$/ for only numeric') }}</p>
        <div class="mt-4 space-y-4">
            <div class="flex items-center">
                <label for="advanced_setting" class="flex items-center">
                    <div class="relative">
                        <input type="checkbox" id="advanced_setting" wire:model.live="advanced_setting" class="rounded-md">
                    </div>
                    <div class="ml-3 text-sm font-medium text-gray-700 dark:text-white">
                        {{ __('setting.Do you want advanced setting?') }}
                    </div>
                </label>
            </div>

            <div class="space-y-2" x-data="{}" x-show="$wire.get('advanced_setting')">
                <label for="validation" class="block text-sm font-medium text-gray-700 dark:text-white">{{ __('setting.Validation') }}</label>
                <input type="text" id="validation" wire:model="validation" maxlength="100" placeholder="{{ __('setting.Enter a valid regex') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                @error('validation') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>
</div>

<!-- Categories Section -->
<div class="space-y-4">
    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 dark:bg-white/[0.03] dark:border-gray-600">
        <h3 class="text-base font-medium text-gray-900 dark:text-white">{{ __('setting.Required Services') }}</h3>
        <div class="mt-4" wire:ignore>
            <div x-data="{
                categories: [],
                selectedCategories: [],
                showTree: false,
                toggleTree() {
                    this.showTree = !this.showTree;
                },
                selectCategory(id) {
                    if (this.selectedCategories.includes(id)) {
                        this.selectedCategories = this.selectedCategories.filter(i => i !== id);
                    } else {
                        this.selectedCategories.push(id);
                    }
                    $wire.set('categories', this.selectedCategories);
                },
                init() {
                    this.categories = $wire.get('categoriesData');
                    this.selectedCategories = $wire.get('categories') || [];
                }
            }">
                <div class="relative">
                    <button type="button" @click="toggleTree" class="w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-4 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                        <span x-text="selectedCategories.length ? selectedCategories.length + ' selected' : '{{ __('setting.Select Services') }}'"></span>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </button>
                    <div x-show="showTree" @click.away="showTree = false" class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                        <template x-for="category in categories" :key="category.id">
                            <div class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-white">
                                <div class="flex items-center">
                                    <input type="checkbox" :id="'category-' + category.id" :checked="selectedCategories.includes(category.id)" @click="selectCategory(category.id)" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    <label :for="'category-' + category.id" class="ml-3 block text-sm font-medium text-gray-700 dark:text-white" x-text="category.name"></label>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
        @error('categories') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
    </div>
</div>

<input type="hidden" wire:model="is_edit_remove" value="1">

<div class="flex justify-start">
    <button type="submit" class="inline-flex items-center px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
        {{ __('setting.Save') }}
    </button>
</div>
</form>
</div>
</div>
