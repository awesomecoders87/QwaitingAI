<div class="p-4">

    @php
    $allLanguages = [
    'ar' => 'Arabic',
    'zh_CN' => 'Chinese',
    'da' => 'Danish',
    'fr' => 'French',
    'hi' => 'Hindi',
    'ja' => 'Japanese',
    'ms' => 'Malay',
    'pt_BR' => 'Português Brasileiro',
    'ru' => 'Russian',
    'sk' => 'Slovak',
    'es' => 'Spanish',
    'ta' => 'Tamil',
    'th' => 'Thai',
    'tr' => 'Turkish',
    'ur' => 'Urdu',
    ];

    @endphp

    <div class="flex flex-col md:flex-row">
        <!-- Tabs Sidebar -->
        <div class="w-full md:w-1/4 border-r mb-4 md:mb-0 pr-4">
            <button wire:click="$set('activeTab', 'language')"
                class="block w-full text-left px-4 py-2 font-semibold border-l-4 focus:outline-none mb-2
                {{ $activeTab === 'language' ? 'border-brand-500 text-brand-600 bg-gray-100 dark:bg-gray-700' : 'border-transparent text-gray-600 hover:bg-gray-50 dark:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-gray-100' }}">
                Language Settings
            </button>

            <button wire:click="$set('activeTab', 'category')"
                class="block w-full text-left px-4 py-2 font-semibold border-l-4 focus:outline-none
                {{ $activeTab === 'category' ? 'border-brand-500 text-brand-600 bg-gray-100 dark:bg-gray-700' : 'border-transparent text-gray-600 hover:bg-gray-50 dark:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-gray-100' }}">
              Service Page
            </button>

             <button wire:click="$set('activeTab', 'Category Page - Category Other Names')"
                class="block w-full text-left px-4 py-2 font-semibold border-l-4 focus:outline-none
                {{ $activeTab === 'Category Page - Category Other Names' ? 'border-brand-500 text-brand-600 bg-gray-100 dark:bg-gray-700' : 'border-transparent text-gray-600 hover:bg-gray-50 dark:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-gray-100' }}">
             Service Page - Service Other Names
            </button>

            <button wire:click="$set('activeTab', 'Category Page - Category Description')"
                class="block w-full text-left px-4 py-2 font-semibold border-l-4 focus:outline-none
                {{ $activeTab === 'Category Page - Category Description' ? 'border-brand-500 text-brand-600 bg-gray-100 dark:bg-gray-700' : 'border-transparent text-gray-600 hover:bg-gray-50 dark:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-gray-100' }}">
              Service Page - Service Description
            </button>

            <button wire:click="$set('activeTab', 'Category Page - Category Service Notes')"
                class="block w-full text-left px-4 py-2 font-semibold border-l-4 focus:outline-none
                {{ $activeTab === 'Category Page - Category Service Notes' ? 'border-brand-500 text-brand-600 bg-gray-100 dark:bg-gray-700' : 'border-transparent text-gray-600 hover:bg-gray-50 dark:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-gray-100' }}">
              Service Page - Service Service Notes

            </button>

             <button wire:click="$set('activeTab', 'Manage Form Inputs Page')"
                class="block w-full text-left px-4 py-2 font-semibold border-l-4 focus:outline-none
                {{ $activeTab === 'Manage Form Inputs Page' ? 'border-brand-500 text-brand-600 bg-gray-100 dark:bg-gray-700' : 'border-transparent text-gray-600 hover:bg-gray-50 dark:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-gray-100' }}">
               Manage Form Inputs Page

            </button>

            <button wire:click="$set('activeTab', 'Manage Form Inputs Page - Placeholders')"
                class="block w-full text-left px-4 py-2 font-semibold border-l-4 focus:outline-none
                {{ $activeTab === 'Manage Form Inputs Page - Placeholders' ? 'border-brand-500 text-brand-600 bg-gray-100 dark:bg-gray-700' : 'border-transparent text-gray-600 hover:bg-gray-50 dark:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-gray-100' }}">
                      Manage Form Inputs Page - Placeholders

            </button>

            <button wire:click="$set('activeTab', 'Ticket Page')"
                class="block w-full text-left px-4 py-2 font-semibold border-l-4 focus:outline-none
                {{ $activeTab === 'Ticket Page' ? 'border-brand-500 text-brand-600 bg-gray-100 dark:bg-gray-700' : 'border-transparent text-gray-600 hover:bg-gray-50 dark:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-gray-100' }}">
                     Ticket Page

            </button>
        </div>

        <!-- Content Area -->
        <div class="w-full md:w-5/6 pl-0 md:pl-4">
            @if ($activeTab === 'language')
            <div class="bg-white shadow rounded p-6 dark:bg-white/[0.03]">
                <form wire:submit.prevent="save">
                    <!-- Enable Language Switcher -->
                    <div class="mb-6">
                        <label class="flex items-center space-x-3">
                            <input type="checkbox" id="enableSwitcher" wire:model="enabled_language_settings"
                                class="form-checkbox h-5 w-5 text-blue-600">
                            <span class="text-gray-700 text-lg dark:text-white"> Enable Language Switcher</span>
                        </label>
                    </div>



                    <!-- Available Languages -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3 dark:text-white">
                             Select Available Languages
                        </label>

                        <div class="grid grid-cols-2 gap-4">
                            @if (!empty($allLanguages))
                            @foreach ($allLanguages as $code => $label)
                                <label class="inline-flex items-center">
                                    <input type="checkbox" wire:model.live="available_languages" value="{{ $code }}"
                                        class="form-checkbox text-blue-600">
                                    <span class="ml-2 text-gray-700  dark:text-white">{{ $label }}</span>
                                </label>
                            @endforeach
                        @endif
                        </div>
                    </div>

                    <!-- Default Language Dropdown -->
                    <div class="mb-6">
                        <label for="defaultLanguage" class="block text-sm font-semibold text-gray-700 mb-2 dark:text-white">
                              Set Default Language
                        </label>
                        <select id="defaultLanguage" wire:model="default_language"
                            class="bg-white w-full p-3 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                            <option value="">Select Default Language</option>
                            <option value="en">English</option>
                            @if (!empty($available_languages))
                                @foreach ($available_languages as $code)
                                    <option value="{{ $code }}">{{ $allLanguages[$code] ?? strtoupper($code) }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- Save Button -->
                    <div class="text-right">
                        <button type="submit"
                            class="text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 primary-btn py-2 px-3 font-medium text-white transition-colors rounded-md bg-brand-500 hover:bg-brand-600 flex gap-x-2">
                                 Save
                        </button>
                    </div>
                </form>
            </div>
            @elseif ($activeTab === 'category')
            <div class="bg-white shadow rounded p-6 dark:bg-white/[0.03]">
                <h2 class="text-lg font-semibold mb-4"> {{ __('setting.Service Page') }}</h2>

                <form wire:submit.prevent="saveTranslation">
                    <div class="space-y-3 overflow-x-auto">
                        <!-- Language headers row -->
                        <div class="flex items-center gap-4 font-semibold mb-2">
                            <div class="w-1/3 min-w-[120px]"></div> {{-- Empty for category label --}}
                            <div class="w-2/3 flex gap-4">
                                @if(!empty($availableLanguages))
                                @foreach($availableLanguages[0] as $langIndex => $lang)
                                <div class="w-full text-left min-w-[160px]">
                                    {{ $allLanguages[$lang] }}
                                </div>
                                @endforeach
                                @endif
                            </div>
                        </div>

                        <!-- Categories with inputs -->
                        @if(!empty($categories))
                            @foreach($categories as $categoryIndex => $category)
                            <div class="flex items-center gap-4">
                                <div class="w-1/3 font-medium min-w-[120px]">
                                    {{ $category }}
                                </div>
                                <div class="w-2/3 flex gap-4">
                                     @if(!empty($availableLanguages[0]))
                                    @foreach($availableLanguages[0] as $langIndex => $lang)
                                    <input
                                        type="text"
                                        class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-600 dark:text-white  min-w-[160px]"
                                        wire:key="input-{{ $activeTab }}-{{ $category }}-{{ $lang }}"
                                        wire:model.defer="translations.{{ $category }}.{{ $lang }}">
                                    @endforeach
                                @endif
                                </div>
                            </div>
                            @endforeach
                        @endif

                        <!-- Submit button -->
                        <button type="submit"
                            class="text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 primary-btn py-2 px-3 font-medium text-white transition-colors rounded-md bg-brand-500 hover:bg-brand-600 flex gap-x-2 mt-4">
                           Save
                        </button>
                    </div>
                </form>
            </div>

            @elseif ($activeTab === 'Category Page - Category Other Names')
            <div class="bg-white shadow rounded p-6 dark:bg-white/[0.03]">
                <h2 class="text-lg font-semibold mb-4"> {{ __('setting.Service Page - Service Other Names') }}</h2>

                <form wire:submit.prevent="saveTranslation">
                    <div class="space-y-3 overflow-x-auto">
                        <!-- Language headers row -->
                        <div class="flex items-center gap-4 font-semibold mb-2">
                            <div class="w-1/3 min-w-[120px]"></div> {{-- Empty for category label --}}
                            <div class="w-2/3 flex gap-4">
                                 @if(!empty($availableLanguages[0]))
                                @foreach($availableLanguages[0] as $langIndex => $lang)
                                <div class="w-full text-left min-w-[160px]">
                                    {{ $allLanguages[$lang] }}
                                </div>
                                @endforeach
                                @endif
                            </div>
                        </div>

                        <!-- Categories with inputs -->
                        @if(!empty($categories))
                            @foreach($categories as $categoryIndex => $category)
                            <div class="flex items-center gap-4">
                                <div class="w-1/3 font-medium min-w-[120px]">
                                    {{ $category }}
                                </div>
                                <div class="w-2/3 flex gap-4">
                                     @if(!empty($availableLanguages[0]))
                                    @foreach($availableLanguages[0] as $langIndex => $lang)
                                    <input
                                        type="text"
                                        class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-600 dark:text-white min-w-[160px]"
                                         wire:key="input-{{ $activeTab }}-{{ $category }}-{{ $lang }}"
                                        wire:model.defer="translations.{{ $category }}_other_name.{{ $lang }}">
                                    @endforeach
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        @endif

                        <!-- Submit button -->
                        <button type="submit"
                            class="text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 primary-btn py-2 px-3 font-medium text-white transition-colors rounded-md bg-brand-500 hover:bg-brand-600 flex gap-x-2 mt-4">
                             Save
                        </button>
                    </div>
                </form>
            </div>

            @elseif ($activeTab === 'Category Page - Category Description')
            <div class="bg-white shadow rounded p-6 dark:bg-white/[0.03]">
                <h2 class="text-lg font-semibold mb-4"> {{ __('setting.Service Page - Service Description') }}</h2>

                <form wire:submit.prevent="saveTranslation">
                    <div class="space-y-3 overflow-x-auto">
                        <!-- Language headers row -->
                        <div class="flex items-center gap-4 font-semibold mb-2">
                            <div class="w-1/3 min-w-[120px]"></div> {{-- Empty for category label --}}
                            <div class="w-2/3 flex gap-4">
                                 @if(!empty($availableLanguages[0]))
                                @foreach($availableLanguages[0] as $langIndex => $lang)
                                <div class="w-full text-left min-w-[160px]">
                                    {{ $allLanguages[$lang] }}
                                </div>
                                @endforeach
                                @endif
                            </div>
                        </div>

                        <!-- Categories with inputs -->
                        @if(!empty($categories))
                            @foreach($categories as $categoryIndex => $category)
                            <div class="flex items-center gap-4">
                                <div class="w-1/3 font-medium min-w-[120px]">
                                    {{ $category }}
                                </div>
                                <div class="w-2/3 flex gap-4">
                                     @if(!empty($availableLanguages[0]))
                                    @foreach($availableLanguages[0] as $langIndex => $lang)
                                    <input
                                        type="text"
                                        class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-600 dark:text-white min-w-[160px]"
                                        wire:key="input-{{ $activeTab }}-{{ $category }}-{{ $lang }}"
                                        wire:model.defer="translations.{{ $category }}_description.{{ $lang }}">
                                    @endforeach
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        @endif

                        <!-- Submit button -->
                        <button type="submit"
                            class="text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 primary-btn py-2 px-3 font-medium text-white transition-colors rounded-md bg-brand-500 hover:bg-brand-600 flex gap-x-2 mt-4">
                             Save
                        </button>
                    </div>
                </form>
            </div>

            @elseif ($activeTab === 'Category Page - Category Service Notes')
            <div class="bg-white shadow rounded p-6 dark:bg-white/[0.03]">
                <h2 class="text-lg font-semibold mb-4"> {{ __('setting.Service Page - Service Service Notes') }}</h2>

                <form wire:submit.prevent="saveTranslation">
                    <div class="space-y-3 overflow-x-auto">
                        <!-- Language headers row -->
                        <div class="flex items-center gap-4 font-semibold mb-2">
                            <div class="w-1/3 min-w-[120px]"></div> {{-- Empty for category label --}}
                            <div class="w-2/3 flex gap-4">
                                 @if(!empty($availableLanguages[0]))
                                @foreach($availableLanguages[0] as $langIndex => $lang)
                                <div class="w-full text-left min-w-[160px]">
                                    {{ $allLanguages[$lang] }}
                                </div>
                                @endforeach
                                @endif
                            </div>
                        </div>

                        <!-- Categories with inputs -->
                        @if(!empty($categories))
                            @foreach($categories as $categoryIndex => $category)
                            <div class="flex items-center gap-4">
                                <div class="w-1/3 font-medium min-w-[120px]">
                                    {{ $category }}
                                </div>
                                <div class="w-2/3 flex gap-4">
                                     @if(!empty($availableLanguages[0]))
                                    @foreach($availableLanguages[0] as $langIndex => $lang)
                                    <input
                                        type="text"
                                        class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-600 dark:text-white min-w-[160px]"
                                        wire:key="input-{{ $activeTab }}-{{ $category }}-{{ $lang }}"
                                        wire:model.defer="translations.{{ $category }}_note.{{ $lang }}">
                                    @endforeach
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        @endif

                        <!-- Submit button -->
                        <button type="submit"
                            class="text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 primary-btn py-2 px-3 font-medium text-white transition-colors rounded-md bg-brand-500 hover:bg-brand-600 flex gap-x-2 mt-4">
                            Save
                        </button>
                    </div>
                </form>
            </div>

             @elseif ($activeTab === 'Manage Form Inputs Page')
            <div class="bg-white shadow rounded p-6 dark:bg-white/[0.03]">
                <h2 class="text-lg font-semibold mb-4">Manage Form Inputs Page</h2>

                <form wire:submit.prevent="saveTranslation">
                    <div class="space-y-3 overflow-x-auto">
                        <!-- Language headers row -->
                        <div class="flex items-center gap-4 font-semibold mb-2">
                            <div class="w-1/3 min-w-[120px]"></div> {{-- Empty for category label --}}
                            <div class="w-2/3 flex gap-4">
                                 @if(!empty($availableLanguages[0]))
                                @foreach($availableLanguages[0] as $langIndex => $lang)
                                <div class="w-full text-left min-w-[160px]">
                                    {{ $allLanguages[$lang] }}
                                </div>
                                @endforeach
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="w-1/3 font-medium min-w-[120px]">
                               QR Code Tagline
                            </div>
                            <div class="w-2/3 flex gap-4">
                                 @if(!empty($availableLanguages[0]))
                                @foreach($availableLanguages[0] as $langIndex => $lang)
                                <input
                                    type="text"
                                    class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-600 dark:text-white min-w-[160px]"
                                    wire:model.defer="translations.Qrcode Tagline 1.{{ $lang }}">
                                @endforeach
                                @endif
                            </div>
                        </div>

                          <div class="flex items-center gap-4">
                            <div class="w-1/3 font-medium min-w-[120px]">
                             QR Code Tagline Second
                            </div>
                            <div class="w-2/3 flex gap-4">
                                 @if(!empty($availableLanguages[0]))
                                @foreach($availableLanguages[0] as $langIndex => $lang)
                                <input
                                    type="text"
                                    class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-600 dark:text-white min-w-[160px]"
                                    wire:model.defer="translations.Qrcode Tagline 2.{{ $lang }}">
                                @endforeach
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="w-1/3 font-medium min-w-[120px]">
                             Queue Heading First
                            </div>
                            <div class="w-2/3 flex gap-4">
                                 @if(!empty($availableLanguages[0]))
                                @foreach($availableLanguages[0] as $langIndex => $lang)
                                <input
                                    type="text"
                                    class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-600 dark:text-white min-w-[160px]"
                                    wire:model.defer="translations.Queue Heading 1.{{ $lang }}">
                                @endforeach
                                @endif
                            </div>
                        </div>

                          <div class="flex items-center gap-4">
                            <div class="w-1/3 font-medium min-w-[120px]">
                                Queue Heading Second
                            </div>
                            <div class="w-2/3 flex gap-4">
                                 @if(!empty($availableLanguages[0]))
                                @foreach($availableLanguages[0] as $langIndex => $lang)
                                <input
                                    type="text"
                                    class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-600 dark:text-white min-w-[160px]"
                                    wire:model.defer="translations.Queue Heading 2.{{ $lang }}">
                                @endforeach
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="w-1/3 font-medium min-w-[120px]">
                              Submit Button Label
                            </div>
                            <div class="w-2/3 flex gap-4">
                                 @if(!empty($availableLanguages[0]))
                                @foreach($availableLanguages[0] as $langIndex => $lang)
                                <input
                                    type="text"
                                    class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-600 dark:text-white min-w-[160px]"
                                    wire:model.defer="translations.Submit Button Label.{{ $lang }}">
                                @endforeach
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="w-1/3 font-medium min-w-[120px]">
                           Back Button Label
                            </div>
                            <div class="w-2/3 flex gap-4">
                                 @if(!empty($availableLanguages[0]))
                                @foreach($availableLanguages[0] as $langIndex => $lang)
                                <input
                                    type="text"
                                    class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-600 dark:text-white min-w-[160px]"
                                    wire:model.defer="translations.Back Button Label.{{ $lang }}">
                                @endforeach
                                @endif
                            </div>
                        </div>

                         <div class="flex items-center gap-4">
                            <div class="w-1/3 font-medium min-w-[120px]">
                             Arrived label on print
                            </div>
                            <div class="w-2/3 flex gap-4">
                                 @if(!empty($availableLanguages[0]))
                                @foreach($availableLanguages[0] as $langIndex => $lang)
                                <input
                                    type="text"
                                    class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-600 dark:text-white min-w-[160px]"
                                    wire:model.defer="translations.Arrived label on print.{{ $lang }}">
                                @endforeach
                                @endif
                            </div>
                        </div>


                        <!-- Categories with inputs -->
                        @if(!empty($formInputs))
                            @foreach($formInputs as $formInput)
                            <div class="flex items-center gap-4">
                                <div class="w-1/3 font-medium min-w-[120px]">
                                    {{ $formInput }}
                                </div>
                                <div class="w-2/3 flex gap-4">
                                     @if(!empty($availableLanguages[0]))
                                    @foreach($availableLanguages[0] as $langIndex => $lang)
                                    <input
                                        type="text"
                                        class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-600 dark:text-white min-w-[160px]"
                                        wire:key="input-{{ $activeTab }}-{{ $formInput }}-{{ $lang }}"
                                        wire:model.defer="translations.{{ $formInput }}.{{ $lang }}">
                                    @endforeach
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        @endif

                        @if(!empty($formSelect))
                            @foreach($formSelect as $select)
                            <div class="flex items-center gap-4">
                                <div class="w-1/3 font-medium min-w-[120px]">
                                    {{ $select }}
                                </div>
                                <div class="w-2/3 flex gap-4">
                                     @if(!empty($availableLanguages[0]))
                                    @foreach($availableLanguages[0] as $langIndex => $lang)
                                    <input
                                        type="text"
                                        class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-600 dark:text-white min-w-[160px]"
                                        wire:key="input-{{ $activeTab }}-{{ $select }}-{{ $lang }}"
                                        wire:model.defer="translations.{{ $select }}.{{ $lang }}">
                                    @endforeach
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        @endif

                        <!-- Submit button -->
                        <button type="submit"
                            class="text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 primary-btn py-2 px-3 font-medium text-white transition-colors rounded-md bg-brand-500 hover:bg-brand-600 flex gap-x-2 mt-4">
                           Save
                        </button>
                    </div>
                </form>
            </div>

            @elseif ($activeTab === 'Manage Form Inputs Page - Placeholders')
            <div class="bg-white shadow rounded p-6 dark:bg-white/[0.03]">
                <h2 class="text-lg font-semibold mb-4"> Manage Form Inputs Page - Placeholders </h2>

                <form wire:submit.prevent="saveTranslation">
                    <div class="space-y-3 overflow-x-auto">
                        <!-- Language headers row -->
                        <div class="flex items-center gap-4 font-semibold mb-2">
                            <div class="w-1/3 min-w-[120px]"></div> {{-- Empty for category label --}}
                            <div class="w-2/3 flex gap-4">
                                 @if(!empty($availableLanguages[0]))
                                @foreach($availableLanguages[0] as $langIndex => $lang)
                                <div class="w-full text-left min-w-[160px]">
                                    {{ $allLanguages[$lang] }}
                                </div>
                                @endforeach
                                @endif
                            </div>
                        </div>

                        <!-- Categories with inputs -->
                        @if(!empty($formInputs))
                            @foreach($formInputs as $formInput)
                            <div class="flex items-center gap-4">
                                <div class="w-1/3 font-medium min-w-[120px]">
                                    {{ $formInput }}
                                </div>
                                <div class="w-2/3 flex gap-4">
                                     @if(!empty($availableLanguages[0]))
                                    @foreach($availableLanguages[0] as $langIndex => $lang)
                                    <input
                                        type="text"
                                        class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-600 dark:text-white min-w-[160px]"
                                        wire:key="input-{{ $activeTab }}-{{ $formInput }}-{{ $lang }}"
                                        wire:model.defer="translations.{{ $formInput }}_placeholders.{{ $lang }}">
                                    @endforeach
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        @endif

                        @if(!empty($formSelect))
                            @foreach($formSelect as $select)
                            <div class="flex items-center gap-4">
                                <div class="w-1/3 font-medium min-w-[120px]">
                                    {{ $select }}
                                </div>
                                <div class="w-2/3 flex gap-4">
                                     @if(!empty($availableLanguages[0]))
                                    @foreach($availableLanguages[0] as $langIndex => $lang)
                                    <input
                                        type="text"
                                        class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-600 dark:text-white min-w-[160px]"
                                        wire:key="input-{{ $activeTab }}-{{ $select }}-{{ $lang }}"
                                        wire:model.defer="translations.{{ $select }}_placeholders.{{ $lang }}">
                                    @endforeach
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        @endif

                        <!-- Submit button -->
                        <button type="submit"
                            class="text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 primary-btn py-2 px-3 font-medium text-white transition-colors rounded-md bg-brand-500 hover:bg-brand-600 flex gap-x-2 mt-4">
                           Save
                        </button>
                    </div>
                </form>
            </div>

            @elseif ($activeTab === 'Ticket Page')
            <div class="bg-white shadow rounded p-6 dark:bg-white/[0.03]">
                <h2 class="text-lg font-semibold mb-4">Ticket Page</h2>

                <form wire:submit.prevent="saveTranslation">
                    <div class="space-y-3 overflow-x-auto">
                        <!-- Language headers row -->
                        <div class="flex items-center gap-4 font-semibold mb-2">
                            <div class="w-1/3 min-w-[120px]"></div> {{-- Empty for category label --}}
                            <div class="w-2/3 flex gap-4">
                                 @if(!empty($availableLanguages[0]))
                                @foreach($availableLanguages[0] as $langIndex => $lang)
                                <div class="w-full text-left min-w-[160px]">
                                    {{ $allLanguages[$lang] }}
                                </div>
                                @endforeach
                                @endif
                            </div>
                        </div>

                         <div class="flex items-center gap-4">
                            <div class="w-1/3 font-medium min-w-[120px]">
                              Print Name Label
                            </div>
                            <div class="w-2/3 flex gap-4">
                                 @if(!empty($availableLanguages[0]))
                                @foreach($availableLanguages[0] as $langIndex => $lang)
                                <input
                                    type="text"
                                    class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-600 dark:text-white min-w-[160px]"
                                    wire:model.defer="translations.Print Name Label.{{ $lang }}">
                                @endforeach
                                @endif
                            </div>
                        </div>

                         <div class="flex items-center gap-4">
                            <div class="w-1/3 font-medium">
                             Print Token Label
                            </div>
                            <div class="w-2/3 flex gap-4">
                                 @if(!empty($availableLanguages[0]))
                                @foreach($availableLanguages[0] as $langIndex => $lang)
                                <input
                                    type="text"
                                    class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-600 dark:text-white min-w-[160px]"
                                    wire:model.defer="translations.Print Token Label.{{ $lang }}">
                                @endforeach
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="w-1/3 font-medium min-w-[120px]">
                             Arrived Time Label
                            </div>
                            <div class="w-2/3 flex gap-4">
                                 @if(!empty($availableLanguages[0]))
                                @foreach($availableLanguages[0] as $langIndex => $lang)
                                <input
                                    type="text"
                                    class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-600 dark:text-white min-w-[160px]"
                                    wire:model.defer="translations.Arrived Time Label.{{ $lang }}">
                                @endforeach
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="w-1/3 font-medium min-w-[120px]">
                             Ticket Message 1
                               <br />
                               @verbatim
                               <span>
                                      Use Keyword
                                    <code class="font-mono bg-gray-200 px-1 py-0.5 rounded dark:bg-gray-900">
                                        {{QUEUE COUNT}}
                                    </code>
                                </span>
                                @endverbatim

                            </div>
                            <div class="w-2/3 flex gap-4">
                                 @if(!empty($availableLanguages[0]))
                                @foreach($availableLanguages[0] as $langIndex => $lang)
                                <input
                                    type="text"
                                    class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-600 dark:text-white min-w-[160px]"
                                    wire:model.defer="translations.Ticket Message 1.{{ $lang }}">
                                @endforeach
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="w-1/3 font-medium min-w-[120px]">
                              Ticket Message 2
                               <br />
                               @verbatim
                              <span>
                                   Use Keyword
                                    <code class="font-mono bg-gray-200 px-1 py-0.5 rounded  dark:bg-gray-900">
                                        {{QUEUE COUNT}} {{Waiting Time}}
                                    </code>
                                </span>
                                @endverbatim

                            </div>
                            <div class="w-2/3 flex gap-4">
                                 @if(!empty($availableLanguages[0]))
                                @foreach($availableLanguages[0] as $langIndex => $lang)
                                <input
                                    type="text"
                                    class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-600 dark:text-white min-w-[160px]"
                                    wire:model.defer="translations.Ticket Message 2.{{ $lang }}">
                                @endforeach
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="w-1/3 font-medium min-w-[120px]">
                             Confirm Button Label
                            </div>
                            <div class="w-2/3 flex gap-4">
                                 @if(!empty($availableLanguages[0]))
                                @foreach($availableLanguages[0] as $langIndex => $lang)
                                <input
                                    type="text"
                                    class="w-full p-2 border rounded dark:bg-gray-800 dark:border-gray-600 dark:text-white min-w-[160px]"
                                    wire:model.defer="translations.walkin.{{ $lang }}">
                                @endforeach
                                @endif
                            </div>
                        </div>

                        <!-- Submit button -->
                        <button type="submit"
                            class="text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 primary-btn py-2 px-3 font-medium text-white transition-colors rounded-md bg-brand-500 hover:bg-brand-600 flex gap-x-2 mt-4">
                            Save
                        </button>
                    </div>
                </form>
            </div>

            @endif
        </div>
    </div>

    {{-- SweetAlert for success feedback --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Livewire.on('updated', () => {
                Swal.fire({
                    title: 'Success!',
                    text: 'Saved Successfully.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.reload();
                    }
                });
            });
        });
    </script>
</div>
