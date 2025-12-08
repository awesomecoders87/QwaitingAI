<div class="p-4">
    <style>
        .tool-description{
           position:absolute;
           left:50% !important;
           top:auto !important;
           bottom:100% !important;
           transform:translateX(-50%);
           margin-bottom:0.5rem;
           color:white !important;
           background-color:#374151;
           font-size:0.875rem;
           padding:0.25rem 0.5rem;
           border-radius:0.25rem;
           width:14rem;
           text-align:left;
           z-index:50;
        }
        .cursor-pointer {
            z-index: 999;
        }
        .tooltip {
            z-index: 99;
        }
    </style>

    <form wire:submit.prevent="save" class="space-y-4">
        <div class="block">
            <div x-data="{ activeTab: 'Print' }" class="border-b border-gray-200 dark:border-gray-700">

                <div class="mb-4 border-b border-gray-200">
                    <ul class="flex text-sm font-semibold text-center text-gray-500 tabs-nav">
                        <li class="mr-2">

                            <a href="javascript:void(0)"
                                :class="activeTab === 'Print' ?
                                    'inline-block px-4 py-2 text-white bg-blue-600 rounded-lg active-tab active' :
                                    'inline-block px-4 py-2 rounded-lg hover:text-gray-600 hover:bg-gray-100 bg-white tex-blue-600 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500'"
                                @click="activeTab = 'Print'">{{ __('setting.Print Settings') }}</a>
                        </li>
                        <li class="mr-2">
                            <a href="javascript:void(0)"
                                :class="activeTab === 'Ticket' ?
                                    'inline-block px-4 py-2 text-white bg-blue-600 rounded-lg active-tab active' :
                                    'inline-block px-4 py-2 rounded-lg hover:text-gray-600 hover:bg-gray-100 bg-white tex-blue-600 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500'"
                                @click="activeTab = 'Ticket'">{{ __('setting.Ticket Screen Settings') }}</a>
                        </li>
                        <li class="mr-2">
                            <a href="javascript:void(0)"
                                :class="activeTab === 'LabelInput' ?
                                    'inline-block px-4 py-2 text-white bg-blue-600 rounded-lg active-tab active' :
                                    'inline-block px-4 py-2 rounded-lg hover:text-gray-600 hover:bg-gray-100 bg-white tex-blue-600 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500'"
                                @click="activeTab = 'LabelInput'">{{ __('setting.Label / Input Settings') }}</a>
                        </li>

                    </ul>
                </div>

                <!-- Print Tab Content -->
                <div x-show="activeTab === 'Print'" class="bg-white shadow p-6 rounded dark:bg-white/[0.03]"
                    style="display:block">
                    <div class="max-w-7xll mx-auto">

                        <div class="grid grid-cols-2 gap-4">



                            <div class="mb-4 relative">
                                <label class="flex text-sm font-medium text-gray mt-2 mb-2 items-center">
                                    {{ __('setting.Disable/enable print options (print redirect, kiosk)') }}

                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.After ticket printing, the kiosk will auto-redirect to the start screen') }}
                                        </span>
                                        </div>
                                </label>

                                <select wire:model.live="print_mode"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                    <option value="default">{{ __('setting.Default') }}</option>
                                    <option value="silent">{{ __('setting.Silent') }}</option>
                                </select>
                            </div>

                        </div>





                        <div class="grid grid-cols-2 gap-4 mt-2">
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="is_qr_code" class="mr-2" value="1">
                                <label class="flex text-sm font-medium text-gray items-center">
                                    {{ __('setting.Show QR code on ticket screen') }}

                                    <!-- Info Icon (same structure as call-screen settings) -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                               {{ __('setting.Enable this to display a QR code on the ticket screen for easy scanning and quick check-in') }}
                                        </span>
                                        </div>
                                    </span>
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="is_qrcode_ticket" class="mr-2" value="1">
                                <label class="flex text-sm font-medium text-gray items-center">
                                    {{ __('setting.Show QR Code on Ticket Preview') }}

                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Show QR code in the ticket preview screen before printing') }}
                                        </span>
                                        </div>
                                </label>
                            </div>
                        </div>

                        <!-- enable on print -->
                        <div class="grid grid-cols-2 gap-4 mt-2">
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="is_logo_on_print" class="mr-2" value="1">
                                <label class="flex text-sm font-medium text-gray items-center">
                                    {{ __('setting.Show Logo on the Ticket Print') }}

                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Print your company logo on tickets') }}
                                        </span>
                                        </div>
                                </label>

                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="is_name_on_print" class="mr-2" value="1">
                                <label class="flex text-sm font-medium text-gray items-center">
                                    {{ __('setting.Show User name on the Ticket Print') }}

                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Print customer’s name on the ticket') }}
                                        </span>
                                        </div>
                                    </span>
                                </label>

                            </div>

                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-2">
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="is_category_on_print" class="mr-2"
                                    value="1">
                                <label class="flex text-sm font-medium text-gray items-center">
                                    {{ __('setting.Show Service on the Ticket Print') }}

                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Print the selected service name on the ticket') }}
                                        </span>
                                        </div>
                                </label>

                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="is_location_on_print" class="mr-2"
                                    value="1">
                                <label class="flex text-sm font-medium text-gray items-center">
                                    {{ __('setting.Show Location on the Ticket Print') }}

                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Print the branch/location name on the ticket') }}
                                        </span>
                                        </div>
                                </label>

                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-2">
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="is_arrived_on_print" class="mr-2"
                                    value="1">
                                <label class="flex text-sm font-medium text-gray items-center">
                                    {{ __('setting.Show Arrived date and time on the Ticket Print') }}

                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Add arrival date & time on ticket for record') }}
                                        </span>
                                        </div>
                                </label>

                            </div>

                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-2">

                            <div class="flex items-center">
                                <input type="checkbox" wire:model="is_token_on_print" class="mr-2" value="1">
                                <label class="flex text-sm font-medium text-gray items-center">
                                    {{ __('setting.Show Token on the Ticket Print') }}

                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Print the token number clearly on the ticket') }}
                                        </span>
                                        </div>
                                </label>

                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="disable_print" class="mr-2" value="1">
                                <label class="flex text-sm font-medium text-gray items-center">{{ __('Disable print') }}

                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Turn off ticket printing completely (only mobile ticket will work)') }}
                                        </span>
                                        </div>

                                </label>
                            </div>

                        </div>

                          <div class="mt-3 mt-2">
                            <h2  style="font-weight:bold">{{ __('setting.Show Services on ticket print') }}</h2>
                        </div>

                           <div class="grid grid-cols-1 gap-4 mt-2">
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="show_category_first" class="mr-2"
                                    value="1">
                                <label class="flex text-sm font-medium text-gray items-center">
                                    {{ __('setting.show first service') }}

                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-0 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Show first service on the ticket print)') }}
                                        </span>
                                        </div>
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="show_category_second" class="mr-2"
                                    value="1">
                                <label class="flex text-sm font-medium text-gray items-center">
                                    {{ __('setting.show second service') }}

                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-0 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Show second service on the ticket print') }}
                                        </span>
                                        </div>
                                </label>
                            </div>
                             <div class="flex items-center">
                                <input type="checkbox" wire:model="show_category_third" class="mr-2"
                                    value="1">
                                <label class="flex text-sm font-medium text-gray items-center">
                                    {{ __('setting.show third service') }}

                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-0 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Show third service on the ticket print') }}
                                        </span>
                                        </div>
                                </label>
                            </div>
                        </div>


                    </div>
                </div>

                <!-- Ticket Tab Content -->
                <div x-show="activeTab === 'Ticket'" class="bg-white shadow p-6 rounded dark:bg-white/[0.03]"
                    style="display:block">
                    <div class="max-w-7xll mx-auto">
                         <h2 class="text-2xl">  {{ __('setting.Service Font, Border, Family setting') }}</h2>
                          <div class="grid grid-cols-2 gap-4">


                            <div>
                                <label
                                    class="flex text-sm font-medium text-gray mt-2 mb-2">{{ __('setting.Service Border Size') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-0 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Adjust the thickness of the ticket border. A bigger number makes the border bolder and more visible') }}
                                        </span>
                                        </div>

                                </label>
                                <select wire:model="category_border_size"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                    <option value="">{{ __('setting.Select Option') }}</option>
                                    @foreach (\App\Models\SiteDetail::getBorderSize() as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label
                                    class="flex text-sm font-medium text-gray mt-2 mb-2">{{ __('setting.Service Font Size') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-0 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Choose how big the service name will appear on the ticket. A larger size makes it more visible for customers') }}
                                        </span>
                                        </div>
                                </label>
                                <select wire:model="category_text_font_size"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                    <option value="">{{ __('setting.Select Option') }}</option>
                                    @foreach (\App\Models\SiteDetail::getFontSize() as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>



                        <div class="grid grid-cols-2 gap-4">

                            <div>
                                <label
                                    class="flex text-sm font-medium text-gray mt-2 mb-2">{{ __('setting.Service Font Family') }}

                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-0 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Select the style of letters (like Arial, Sans, Serif). This changes the overall look of the ticket text') }}
                                        </span>
                                        </div>
                                </label>
                                <select wire:model="ticket_font_family"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                    <option value="">{{ __('setting.Select Option') }}</option>
                                    @foreach (\App\Models\SiteDetail::getFontFamily() as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

<hr class="mt-3 mb-3" style="color:#42424240;">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="mb-4">
                                <label
                                    class="flex text-sm font-medium text-gray mt-2 mb-2">{{ __('setting.Background Image') }}

                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-0 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Upload an image to use as background for the ticket screen') }}
                                        </span>
                                        </div>
                                </label>
                                <input type="file" wire:model="background_image"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />

                                @error('background_image')
                                    <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror

                                @if ($background_image)
                                    <div class="relative mt-2 w-fit">
                                        @if (is_object($background_image))
                                            <img src="{{ $background_image->temporaryUrl() }}"
                                                class="h-32 rounded shadow" />
                                        @else
                                            <img src="{{ Storage::url($background_image) }}"
                                                class="h-32 rounded shadow" />
                                        @endif
                                        <button type="button" wire:click="deleteBackgroundImage"
                                            class="absolute top-0 right-0 bg-red-600 text-white text-xs px-2 py-1 rounded-full">
                                            ✕
                                        </button>
                                    </div>
                                @endif
                            </div>

                            <div class="mb-4">
                                <label
                                    class="flex text-sm font-medium text-gray mt-2 mb-2">{{ __('setting.Background Size') }}

                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-0 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Decide how the background image should fit (Cover = fill the screen, Contain = fit inside screen)') }}
                                        </span>
                                        </div>
                                </label>
                                <select wire:model="background_size"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                    <option value="cover">{{ __('setting.Cover') }}</option>
                                    <option value="contain">{{ __('setting.Contain') }}</option>
                                    <option value="auto">{{ __('setting.Auto') }}</option>
                                </select>
                            </div>

                        </div>

                        <div class="grid grid-cols-2 gap-4">


                            <div class="mb-4">
                                <label
                                    class="flex text-sm font-medium text-gray mt-2 mb-2">{{ __('setting.Background Repeat') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-0 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Choose whether the background image repeats or shows only once') }}
                                        </span>
                                        </div>
                                </label>
                                <select wire:model="background_repeat"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                    <option value="no-repeat">{{ __('setting.No Repeat') }}</option>
                                    <option value="repeat">{{ __('setting.Repeat') }}</option>
                                    <option value="repeat-x">{{ __('setting.Repeat X') }}</option>
                                    <option value="repeat-y">{{ __('setting.Repeat Y') }}</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label
                                    class="flex text-sm font-medium text-gray mt-2 mb-2">{{ __('setting.Background Position') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-0 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Position of the background image (Center, Top, Left, etc.)') }}
                                        </span>
                                        </div>
                                </label>
                                <select wire:model="background_position"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                    <option value="center">{{ __('setting.Center') }}</option>
                                    <option value="top">{{ __('setting.Top') }}</option>
                                    <option value="bottom">{{ __('setting.Bottom') }}</option>
                                    <option value="left">{{ __('setting.Left') }}</option>
                                    <option value="right">{{ __('setting.Right') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">


                             <div class="mb-4 relative">
                                <label class="flex text-sm font-medium text-gray mt-2 mb-2 items-center">
                                    {{ __('setting.Disable/enable print options (print redirect, kiosk)') }}

                                    <!-- Info Icon (match call-screen tooltip UI) -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.After ticket printing, the kiosk will auto-redirect to the start screen') }}
                                        </span>
                                        </div>
                                </label>

                                <select wire:model="layout_show"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                    <option value="row">{{ __('setting.row') }}</option>
                                    <option value="column">{{ __('setting.column') }}</option>
                                </select>
                            </div>

                             <div class="mb-4 relative">
                                <label class="flex text-sm font-medium text-gray mt-2 mb-2">
                                    {{ __('setting.Logo Size') }}

                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('Set Logo size on the queue page') }}
                                        </span>
                                        </div>
                                </label>

                                <select wire:model="logo_size"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                    <option value="small">{{ __('setting.small') }}</option>
                                    <option value="medium">{{ __('setting.medium') }}</option>
                                    <option value="large">{{ __('setting.large') }}</option>
                                </select>
                            </div>
                           
                             <div class="mb-4 relative">
                                <label class="flex text-sm font-medium text-gray mt-2 mb-2">
                                    {{ __('setting.Enable/Disable Ticket Limit') }}
                             <div class="relative group inline-block">
                                    <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                    </div>
                                       <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.enable/disable the ticket limit') }}
                                        </span>
                                      
                                </label>

                                <select wire:model="is_ticket_limit_enabled"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                    <option value="0">{{ __('setting.no') }}</option>
                                    <option value="1">{{ __('setting.yes') }}</option>
                                </select>
                            </div>

                            <div class="mb-4 relative">
                                <label class="flex text-sm font-medium text-gray mt-2 mb-2">
                                    {{ __('setting.Enter Ticket Limit') }}

                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Enter Ticket Limit') }}
                                        </span>
                                        </div>
                                </label>

                                <input wire:model="ticket_limit"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            </div>


                        </div>

                         <div class="grid grid-cols-2 gap-4">
                            <div class="mb-4">
                                <label
                                    class="flex text-sm font-medium text-gray mt-2 mb-2">{{ __('setting.Ticket Image') }}

                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-0 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Upload an image for the ticket') }}
                                        </span>
                                        </div>
                                </label>
                                <input type="file" wire:model="ticket_image"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />

                                @error('ticket_image')
                                    <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror

                                @if ($ticket_image)
                                    <div class="relative mt-2 w-fit">
                                        @if (is_object($ticket_image))
                                            <img src="{{ $ticket_image->temporaryUrl() }}"
                                                class="h-32 rounded shadow" />
                                        @else
                                            <img src="{{ Storage::url($ticket_image) }}"
                                                class="h-32 rounded shadow" />
                                        @endif
                                        <button type="button" wire:click="deleteticketBackgroundImage"
                                            class="absolute top-0 right-0 bg-red-600 text-white text-xs px-2 py-1 rounded-full">
                                            ✕
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-2">
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="user_detail" class="mr-2" value="1">
                                <label
                                    class="flex text-sm font-medium text-gray">{{ __('setting.Display user detail on the mobile ticket view') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Show customer details (like name/phone) on mobile ticket view') }}
                                        </span>
                                        </div>
                                </label>

                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="bottom_btn_enable" class="mr-2" value="1">
                                <label
                                    class="flex text-sm font-medium text-gray">{{ __('setting.Display Bottom mobile Tickets view  Buttons') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Show action buttons (like Confirm, Cancel) at the bottom of mobile ticket screen') }}
                                        </span>
                                        </div>
                                </label>

                            </div>

                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-2">
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="ticket_text_enable" class="mr-2"
                                    value="1">
                                <label
                                    class="flex text-sm font-medium text-gray">{{ __('setting.Enable Ticket Text') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-0 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Allow ticket messages (custom text) to appear on the printed ticket') }}
                                        </span>
                                        </div>
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="show_cat_icon" class="mr-2" value="1">
                                <label
                                    class="flex text-sm font-medium text-gray">{{ __('setting.Show Service Images') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Display service icons/images along with names on tickets') }}
                                        </span>
                                        </div>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-2">
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="queue_form_display" class="mr-2"
                                    value="1">
                                <label
                                    class="flex text-sm font-medium text-gray">{{ __('setting.Queue Form Display') }}

                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-0 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Show a queue form before generating a ticket (for name, phone, etc.)') }}
                                        </span>
                                        </div>
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="late_coming_feature" class="mr-2"
                                    value="1">
                                <label
                                    class="flex text-sm font-medium text-gray">{{ __('setting.Describing a mobile ticket feature where customers can indicate that they are running late and adjust their place in a queue accordingly') }}


                                </label>
                            </div>
                        </div>

                          <div class="grid grid-cols-2 gap-4 mt-2">
                             <div class="flex items-center">
                                <input type="checkbox" wire:model="enable_priority_pattern" class="mr-2"
                                    value="1">
                                <label
                                    class="flex text-sm font-medium text-gray-700">{{ __('setting.Enable Priority Pattern') }}

                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-0 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Generate tickets in the queue based on the defined sorting pattern') }}
                                        </span>
                                        </div>
                                    </span>
                                </label>
                            </div>
                        </div>


                        <div class="grid grid-cols-2 gap-4 mt-2">
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="is_enable_waitlist_message" class="mr-2"
                                    value="1">
                                <label
                                    class="flex text-sm font-medium text-gray-700">{{ __('setting.Enable mobile ticket waitlist Text') }}

                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Allow waitlist messages to show on mobile tickets') }}
                                        </span>
                                        </div>
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="is_waitlist_table" class="mr-2" value="1">
                                <label
                                    class="flex text-sm font-medium text-gray-700">{{ __('setting.Show waitlist table (mobile + print)') }}

                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Show the waitlist table in the mobile ticket screen') }}
                                        </span>
                                        </div>
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="enable_waitlist_list" class="mr-2"
                                    value="1">
                                <label
                                    class="flex text-sm font-medium text-gray-700">{{ __('setting.Show waitlist list') }}

                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-0 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Show the waitlist list button in the mobile ticket screen') }}
                                        </span>
                                        </div>
                                </label>
                            </div>
                        </div>


                        <div class="grid grid-cols-2 gap-4 mt-2">
                              <div class="flex items-center">
                                <input type="checkbox" wire:model="enable_callDepartment" class="mr-2"
                                    value="1">
                                <label
                                    class="flex text-sm font-medium text-gray">{{ __('setting.Enable Call Department') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Ticket genearte according to service workflow') }}
                                        </span>
                                        </div>
                                </label>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-2">
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="use_staff_priority" class="mr-2"
                                    value="1">
                                <label
                                    class="flex text-sm font-medium text-gray">{{ __('setting.Use staff priority according to third level of staff') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Assign ticket priority based on staff level (third-level priority system)') }}
                                        </span>
                                        </div>
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="is_redirect_print_page" class="mr-2"
                                    value="1">
                                <label
                                    class="flex text-sm font-medium text-gray">{{ __('setting.enable print page redirect on kiosk screen') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.After ticket printing, the kiosk will auto-redirect to the start screen') }}
                                        </span>
                                        </div>
                                </label>
                            </div>
                        </div>


                    </div>
                </div>


                <!-- LabelInput Tab Content -->
                <div x-show="activeTab === 'LabelInput'" class="bg-white shadow p-6 rounded dark:bg-white/[0.03]"
                    style="display:block">
                    <div class="max-w-7xll mx-auto">
                        <div class="grid grid-cols-2 gap-4 mt-2">
                            <div>
                                <label
                                    class="flex text-sm font-medium text-gray">{{ __('setting.Queue Heading First') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-0 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Heading text shown on the top of the queue ticket (e.g., “Welcome to Nike”)') }}
                                        </span>
                                        </div>
                                </label>
                                <input type="text" wire:model="queue_heading_first"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            </div>
                            <div>
                                <label
                                    class="flex text-sm font-medium text-gray">{{ __('setting.Queue Heading Second') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Secondary heading text shown below the first queue heading') }}
                                        </span>
                                        </div>
                                </label>
                                <input type="text" wire:model="queue_heading_second"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-2">
                            <div>
                                <label
                                    class="flex text-sm font-medium text-gray">{{ __('setting.Waitlist Heading First') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-0 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Heading text for waitlist tickets (e.g., “Waitlist”)') }}
                                        </span>
                                        </div>
                                </label>
                                <input type="text" wire:model="waitlist_heading"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            </div>
                            <div>
                                <label
                                    class="flex text-sm font-medium text-gray">{{ __('setting.Mobile App Heading First') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.First title shown at the top of the mobile app ticket screen') }}
                                        </span>
                                        </div>
                                </label>
                                <input type="text" wire:model="app_heading_first"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            </div>

                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-2">

                            <div>
                                <label
                                    class="flex text-sm font-medium text-gray">{{ __('setting.Mobile App Heading Second') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Second title shown below the first heading in the mobile app') }}
                                        </span>
                                        </div>
                                </label>
                                <input type="text" wire:model="app_heading_second"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            </div>

                        </div>


                        <!-- Ticket Messages -->
                        <div>
                            <label class="flex text-sm font-medium text-gray">{{ __('setting.Ticket Message 1') }}
                                <!-- Tooltip Icon -->
                                <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-0 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                        {{ __('setting.ticket message 1 tooltip') }}
                                        </span>
                                    </div>
                            </label>
                            <small class="block text-xs text-gray-500">{{ __('setting.Use Keyword') }}: <code
                                    class="font-mono bg-gray-200 px-1 py-0.5 rounded">@{{ QUEUE COUNT }}</code></small>
                            <textarea wire:model="ticket_text"
                                class="w-full dark:bg-dark-900 h-30 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"></textarea>
                        </div>

                        <div>
                            <label class="flex text-sm font-medium text-gray">{{ __('setting.Ticket Message 2') }}
                                <!-- Tooltip Icon -->
                                <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-0 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                        {{ __('setting.ticket message 2 tooltip') }}
                                        </span>
                                    </div>
                            </label>
                            <small class="block text-xs text-gray-500">{{ __('setting.Use Keyword') }}: <code
                                    class="font-mono bg-gray-200 px-1 py-0.5 rounded">@{{ QUEUE COUNT }}
                                    @{{ Waiting Time }}</code></small>
                            <textarea wire:model="ticket_text_2"
                                class="w-full dark:bg-dark-900 h-30 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"></textarea>
                        </div>

                        <!-- Ticket Waitlist Messages -->
                        <div>
                            <label
                                class="flex text-sm font-medium text-gray">{{ __('setting.Ticket Waitlist Message 1') }}
                                <!-- Tooltip Icon -->
                                <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-0 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                        {{ __('setting.ticket message waitlist 1 tooltip') }}
                                        </span>
                                    </div>
                            </label>
                            <small class="block text-xs text-gray-500">{{ __('setting.Use Keyword') }}: <code
                                    class="font-mono bg-gray-200 px-1 py-0.5 rounded">@{{ QUEUE COUNT }}</code></small>
                            <textarea wire:model="waitlist_message_first"
                                class="w-full dark:bg-dark-900 h-30 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"></textarea>
                        </div>

                        <div>
                            <label
                                class="flex text-sm font-medium text-gray">{{ __('setting.Ticket Waitlist Message 2') }}
                                <!-- Tooltip Icon -->
                                <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-0 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                        {{ __('setting.ticket message waitlist 2 tooltip') }}
                                        </span>
                                    </div>
                            </label>
                            <small class="block text-xs text-gray-500">{{ __('setting.Use Keyword') }}: <code
                                    class="font-mono bg-gray-200 px-1 py-0.5 rounded">@{{ Waiting Time }}</code></small>
                            <textarea wire:model="waitlist_message_second"
                                class="w-full dark:bg-dark-900 h-30 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="flex text-sm font-medium text-gray mt-2">{{ __('setting.Name label on print') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-0 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.The word shown before the customer’s name on the printed ticket (e.g., “Name: John”)') }}
                                        </span>
                                        </div>
                                </label>
                                <input type="text" wire:model="print_name_label"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            </div>
                            <div>
                                <label
                                    class="flex text-sm font-medium text-gray mt-2">{{ __('setting.Token label on print') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.The label shown before the token number on the printed ticket (e.g., “Token: A001”)') }}
                                        </span>
                                        </div>
                                </label>
                                <input type="text" wire:model="print_token_label"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="flex text-sm font-medium text-gray mt-2">{{ __('setting.Arrived label on print') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-0 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-72">
                                            {{ __('setting.The label used when showing arrival status on ticket. Example: “Arrived.”') }}
                                        </span>
                                        </div>
                                </label>
                                <input type="text" wire:model="arrived_time_label"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            </div>
                            <div>
                                <label
                                    class="flex text-sm font-medium text-gray mt-2">{{ __('setting.Confirm Button label on print') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-0 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-72">
                                            {{ __('setting.Text written on the confirm button (e.g., Confirm / Okay)') }}
                                        </span>
                                        </div>
                                </label>
                                <input type="text" wire:model="confirm_btn_label"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="flex text-sm font-medium text-gray mt-2">{{ __('setting.Submit Button Label') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-0 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-72">
                                            {{ __('setting.The text shown on the submit button (e.g., Submit / Proceed)') }}
                                        </span>
                                        </div>
                                </label>
                                <input type="text" wire:model="submit_btn_text"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            </div>
                            <div>
                                <label
                                    class="flex text-sm font-medium text-gray mt-2">{{ __('setting.Back Button Label') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.The text shown on the back button (e.g., Back / Go Back)') }}
                                        </span>
                                        </div>
                                </label>
                                <input type="text" wire:model="back_btn_text"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            </div>
                        </div>

                        <!-- QR Code Fields -->
                        <div class="grid grid-cols-2 gap-4 mt-2">
                            <div>
                                <label class="flex text-sm font-medium text-gray">{{ __('setting.QR Code Tagline') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-0 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-72">
                                            {{ __('setting.Add the first line of text that appears below the QR code. Usually used for instructions or branding') }}
                                        </span>
                                        </div>
                                </label>
                                <input type="text" wire:model="qrcode_tagline"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            </div>

                            <div>
                                <label
                                    class="flex text-sm font-medium text-gray">{{ __('setting.QR Code Tagline Second') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Add a second line of text below the QR code. Can be used for extra info or contact details') }}
                                        </span>
                                        </div>
                                </label>
                                <input type="text" wire:model="qrcode_tagline_second"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            </div>

                        </div>

                        <!-- Second Row -->
                        <div class="grid grid-cols-2 gap-4">

                            <div>
                                <label
                                    class="flex text-sm font-medium text-gray mt-2 mb-2">{{ __('setting.Token Number Digit') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-0 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-72">
                                            {{ __('setting.Decide how many digits the token number should have. For example, “4 digits” will show numbers like 0001, 0002, etc') }}
                                        </span>
                                        </div>
                                </label>
                                <select wire:model="token_digit"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                    @foreach (\App\Models\SiteDetail::getTokenDigit() as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label
                                    class="flex text-sm font-medium text-gray mt-2 mb-2">{{ __('setting.Token Start From') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Set the first number your tokens will begin with (for example, 01 or 1001)') }}
                                        </span>
                                        </div>
                                </label>
                                <input type="number" wire:model="token_start"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            </div>
                        </div>

                        <!-- Third Row -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="flex text-sm font-medium text-gray mt-2 mb-2">{{ __('setting.Calculate Estimate Waiting Time & Queue Count By Service') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.System will calculate waiting time and queue size automatically for each service') }}
                                        </span>
                                        </div>
                                </label>
                                <select wire:model="category_estimated_time"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                    <option value="1">{{ __('setting.Yes') }}</option>
                                    <option value="0">{{ __('setting.No') }}</option>
                                </select>
                            </div>

                              <div>
                                <label
                                    class="flex text-sm font-medium text-gray mt-2 mb-2">{{ __('setting.Calculate waiting time based on: Service or Both (Service + Staff)') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Select how the system should calculate waiting time: by Service or by both Service and Staff together') }}
                                        </span>
                                        </div>
                                </label>

                                <select wire:model="estimate_time_mode"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                    <option value="1">{{ __('setting.Service with Staff') }}</option>
                                    <option value="2">{{ __('setting.Service') }}</option>
                                    {{-- <option value="3">{{ __('setting.Staff') }}</option> --}}
                                </select>
                            </div>

                              <div>
                                <label
                                    class="flex text-sm font-medium text-gray mt-2 mb-2">{{ __('setting.Count All Pending Queues') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-0 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-72">
                                            {{ __('setting.Count all pending queues for the team, regardless of their service') }}
                                        </span>
                                        </div>
                                </label>
                                <select wire:model="count_all_services"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                    <option value="1">{{ __('setting.No') }}</option>
                                    <option value="2">{{ __('setting.Yes') }}</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="flex items-center">
                                    <input type="checkbox" wire:model="count_by_service" class="form-checkbox h-4 w-4 text-blue-600 transition duration-150 ease-in-out">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('setting.Count by Service') }}
                                        <span class="group relative cursor-help">
                                            <svg class="h-4 w-4 text-gray-400 inline-block ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <div class="absolute left-0 -ml-2 mt-6 w-64 p-2 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-50 tool-description hidden group-hover:block">
                                                {{ __('setting.When enabled, the system will count and display the number of tickets serially') }}
                                            </div>
                                        </span>
                                    </span>
                                </label>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="flex text-sm font-medium text-gray mt-2 mb-2">{{ __('setting.To count, select a service level. Wait Time and Number in Queue') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Choose how the system should calculate queue size and waiting time. You can set it by service level, wait time, or number of people in line') }}
                                        </span>
                                        </div>
                                </label>
                                <select wire:model="category_level_est"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                    <option value="">{{ __('setting.Select Option') }}</option>
                                    @foreach (\App\Models\SiteDetail::getCategoryLevelEstimaion() as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label
                                    class="flex text-sm font-medium text-gray mt-2 mb-2">{{ __('setting.Service Estimate Time') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-0 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-72">
                                            {{ __('setting.Enter the average time (in minutes) needed to serve one customer. This helps calculate waiting time') }}
                                        </span>
                                        </div>
                                </label>
                                <input type="number" wire:model="estimate_time"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            </div>
                            <div>
                                <label
                                    class="flex text-sm font-medium text-gray mt-2 mb-2">{{ __('setting.Enable Time Slot') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Choose if customers get tickets by time slots or simply by token numbers') }}
                                        </span>
                                        </div>
                                </label>
                                <select wire:model.live="enable_time_slot"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                    <option value="">{{ __('setting.Select Option') }}</option>
                                    @foreach ($enableTimeSlots as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>


                        <div class="grid grid-cols-2 gap-4">


                            @if ($enable_time_slot == 'category')
                                <div>
                                    <label
                                        class="flex text-sm font-medium text-gray mt-2 mb-2">{{ __('setting.Service Level For Time Slot') }}
                                        <!-- Tooltip Icon -->
                                       <span class="ml-2 cursor-pointer relative group">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4 text-gray-500 hover:text-gray-700" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M18 10A8 8 0 112 10a8 8 0 0116 0zM9 7h2v2H9V7zm0 4h2v4H9v-4z"
                                                clip-rule="evenodd" />
                                        </svg>

                                        <!-- Tooltip Box -->
                                        <div
                                            class="absolute left-6 top-0 w-64 bg-gray-800 text-white text-xs rounded-lg px-3 py-2 shadow-lg
               opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-50 tool-description hidden group-hover:block">
                                                {{ __('setting.You can set it by service level') }}
                                            </div>
                                        </span>
                                    </label>
                                    <select wire:model="category_slot_level"
                                        class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                        <option value="">{{ __('setting.Select Option') }}</option>
                                        @foreach ($categoryLevels as $key => $value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="flex text-sm font-medium text-gray mt-2 mb-2">{{ __('setting.Select TimeZone') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-0 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-72">
                                            {{ __('setting.Select the time zone that matches your branch location. This is important for accurate ticket timing') }}
                                        </span>
                                        </div>
                                </label>
                                <select wire:model="select_timezone"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                    <option value="">{{ __('setting.Select TimeZone') }}</option>
                                    @foreach ($listTimeZones as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label
                                    class="flex text-sm font-medium text-gray mt-2 mb-2">{{ __('setting.Select Country Code Mode') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Select country mode.in which show only default selected phone code or dropdown of phone code') }}
                                        </span>
                                        </div>
                                </label>
                                <select wire:model="country_options"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                    @foreach ($countryMode as $key=>$value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>

                             <div>
                                <label
                                    class="flex text-sm font-medium text-gray mt-2 mb-2">{{ __('setting.Select Country Code for Phone') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.Default country code used for phone numbers in mobile ticket bookings. Example: +91 for India') }}
                                        </span>
                                        </div>
                                </label>
                                <select wire:model="country_code"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                    @foreach ($countryCode as $value)
                                        <option value="{{ $value['phonecode'] }}">{{ $value['name'] }} (+{{ $value['phonecode'] }})</option>
                                    @endforeach
                                </select>
                                 @error('country_code')
                                    <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                        </div>


                        <div class="grid grid-cols-2 gap-4 mt-2">
                            <!-- <div class="flex items-center">
        <input type="checkbox" wire:model="ticket_mode" class="mr-2" value="1">
        <label class="text-sm font-medium text-gray">{{ __('setting.enable virtual meeting on kiosk screen') }}</label>
     </div> -->

                        </div>

                        <div class="grid grid-cols-2 gap-4">
                               <div class="flex items-center">
        <input type="checkbox" wire:model="enable_doc_file" class="mr-2" value="1">
        <label class="text-sm font-medium text-gray">{{ __('setting.enable document upload field on queue screen') }}</label>
     </div>
                            <div>
                                <label
                                    class="flex text-sm font-medium text-gray mt-2">{{ __('setting.label on document field') }}
                                    <!-- Tooltip Icon -->
                                    <div class="relative group inline-block">
                                        <button type="button" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <span
                                            class="absolute left-1/2 -translate-x-1/2 z-50 mb-2 hidden group-hover:block 
                                                   bg-gray-700 text-white text-sm px-2 py-1 rounded 
                                                   w-56">
                                            {{ __('setting.This field will appear on the kiosk and mobile custom form for users to upload file (type:pdf,doc,docx,jpg,png and max-size:2MB)') }}
                                        </span>
                                        </div>
                                </label>
                                <input type="text" wire:model="doc_file_label"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            </div>
                            </div>


                    </div>
                </div>
            </div>
        </div>



        <!--end code -->


        <div class="py-4">

            @if ($successMessage)
                <div class="rounded-xl border border-success-500 bg-success-50 p-4 dark:border-success-500/30 dark:bg-success-500/15 mt-2"
                    id="alert">
                    <div class="flex items-start gap-3">
                        <div class="-mt-0.5 text-success-500">
                            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M3.70186 12.0001C3.70186 7.41711 7.41711 3.70186 12.0001 3.70186C16.5831 3.70186 20.2984 7.41711 20.2984 12.0001C20.2984 16.5831 16.5831 20.2984 12.0001 20.2984C7.41711 20.2984 3.70186 16.5831 3.70186 12.0001ZM12.0001 1.90186C6.423 1.90186 1.90186 6.423 1.90186 12.0001C1.90186 17.5772 6.423 22.0984 12.0001 22.0984C17.5772 22.0984 22.0984 17.5772 22.0984 12.0001C22.0984 6.423 17.5772 1.90186 12.0001 1.90186ZM15.6197 10.7395C15.9712 10.388 15.9712 9.81819 15.6197 9.46672C15.2683 9.11525 14.6984 9.11525 14.347 9.46672L11.1894 12.6243L9.6533 11.0883C9.30183 10.7368 8.73198 10.7368 8.38051 11.0883C8.02904 11.4397 8.02904 12.0096 8.38051 12.3611L10.553 14.5335C10.7217 14.7023 10.9507 14.7971 11.1894 14.7971C11.428 14.7971 11.657 14.7023 11.8257 14.5335L15.6197 10.7395Z"
                                    fill=""></path>
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

            <button type="submit"
                class="px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">{{ __('setting.Save') }}</button>
            <a href="{{ route('tenant.ticket-generate-settings') }}"
                class="px-4 py-3 text-sm font-medium text-white rounded-lg bg-error-500 shadow-theme-xs hover:bg-error-600">
                {{ __('setting.Cancel') }}
            </a>
        </div>
    </form>



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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {

        Livewire.on('updated', () => {
            Swal.fire({
                title: 'Success!',
                text: ' Updated successfully.',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    location.reload(); // Refresh the page when OK is clicked
                }
            });
        });
        Livewire.on('deleted', () => {
            Swal.fire({
                title: 'Success!',
                text: ' Deleted successfully.',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then((result) => {
                // if (result.isConfirmed) {
                //     location.reload(); // Refresh the page when OK is clicked
                // }
            });
        });
    });

    // function toggleTooltip(icon) {
    //     let tooltip = icon.nextElementSibling;
    //     tooltip.classList.toggle("hidden");
    // }

    // function hideTooltip(el) {
    //     el.classList.add("hidden");
    // }
</script>
</div>
