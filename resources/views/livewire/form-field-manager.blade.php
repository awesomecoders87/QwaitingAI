<div class="p-4">
    <h2 class="text-xl font-semibold mb-4">{{ __('setting.Form Fields') }}</h2>
    <div class="mb-4 flex justify-between mb-4 gap-3 flex-wrap">
        <!-- Search Box -->
        <div class="relative w-full lg:w-[300px]">
            <span class="pointer-events-none absolute top-1/2 left-4 -translate-y-2/4">
                <svg class="fill-gray-500 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04199 9.37381C3.04199 5.87712 5.87735 3.04218 9.37533 3.04218C12.8733 3.04218 15.7087 5.87712 15.7087 9.37381C15.7087 12.8705 12.8733 15.7055 9.37533 15.7055C5.87735 15.7055 3.04199 12.8705 3.04199 9.37381ZM9.37533 1.54218C5.04926 1.54218 1.54199 5.04835 1.54199 9.37381C1.54199 13.6993 5.04926 17.2055 9.37533 17.2055C11.2676 17.2055 13.0032 16.5346 14.3572 15.4178L17.1773 18.2381C17.4702 18.531 17.945 18.5311 18.2379 18.2382C18.5308 17.9453 18.5309 17.4704 18.238 17.1775L15.4182 14.3575C16.5367 13.0035 17.2087 11.2671 17.2087 9.37381C17.2087 5.04835 13.7014 1.54218 9.37533 1.54218Z" fill=""></path>
                </svg>
            </span>
            <input type="text" wire:model.live.debounce.500ms="search" placeholder="{{ __('setting.Search') }}..." class="dark:bg-dark-900 bg-white shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-[42px] w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-[42px] text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden xl:w-[300px] dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
        </div>
        <div>
            @can('Form Field Add')
            <a href="{{ route('tenant.create-form-field') }}" class="text-white px-3 py-2 rounded-md bg-brand-500 hover:bg-brand-600 flex gap-x-2">
                <i class="ri-add-circle-line"></i> {{ __('setting.Add New Field') }}
            </a>
            @endcan    
        </div>
    </div>

    <div class="overflow-hidden rounded-lg shadow border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="max-w-full overflow-x-auto p-4">
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-5 py-3 sm:px-6"></th>
                        <th class="px-5 py-3 sm:px-6">{{ __('setting.Name') }}</th>
                        <th class="px-5 py-3 sm:px-6">{{ __('setting.Type') }}</th>
                        <th class="px-5 py-3 sm:px-6">{{ __('setting.Label') }}</th>
                        <th class="px-5 py-3 sm:px-6">{{ __('setting.Placeholder') }}</th>
                        <th class="px-5 py-3 sm:px-6">{{ __('setting.Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800" wire:sortable="updateOrder">
                    @foreach ($formFields as $field)
                    <tr wire:key="field-{{ $field->id }}" wire:sortable.item="{{ $field->id }}">
                        <td class="px-5 py-4 sm:px-6" wire:sortable.handle>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500 cursor-move" viewBox="0 0 320 512">
                                <path d="M41 288h238c21.4 0 32.1 25.9 17 41L177 448c-9.4 9.4-24.6 9.4-33.9 0L24 329c-15.1-15.1-4.4-41 17-41zm255-105L177 64c-9.4-9.4-24.6-9.4-33.9 0L24 183c-15.1 15.1-4.4 41 17 41h238c21.4 0 32.1-25.9 17-41z"/>
                            </svg>     
                        </td>
                        <td class="px-5 py-4 sm:px-6">{{ $field->title }}</td>
                        <td class="px-5 py-4 sm:px-6">{{ $field->type }}</td>
                        <td class="px-5 py-4 sm:px-6">{{ $field->label }}</td>
                        <td class="px-5 py-4 sm:px-6">{{ $field->placeholder }}</td>
                        <td class="p-3">
                            <div>
                                <div x-data="{openDropDown: false}" class="relative">
                                    <button @click="openDropDown = !openDropDown" class="text-gray-500 dark:text-gray-400">
                                        <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.99902 10.245C6.96552 10.245 7.74902 11.0285 7.74902 11.995V12.005C7.74902 12.9715 6.96552 13.755 5.99902 13.755C5.03253 13.755 4.24902 12.9715 4.24902 12.005V11.995C4.24902 11.0285 5.03253 10.245 5.99902 10.245ZM17.999 10.245C18.9655 10.245 19.749 11.0285 19.749 11.995V12.005C19.749 12.9715 18.9655 13.755 17.999 13.755C17.0325 13.755 16.249 12.9715 16.249 12.005V11.995C16.249 11.0285 17.0325 10.245 17.999 10.245ZM13.749 11.995C13.749 11.0285 12.9655 10.245 11.999 10.245C11.0325 10.245 10.249 11.0285 10.249 11.995V12.005C10.249 12.9715 11.0325 13.755 11.999 13.755C12.9655 13.755 13.749 12.9715 13.749 12.005V11.995Z" fill=""></path>
                                        </svg>
                                    </button>
                                    <div x-show="openDropDown" @click.outside="openDropDown = false" class="shadow-theme-lg dark:bg-gray-dark absolute top-full right-0 z-40 w-40 space-y-1 rounded-2xl border border-gray-200 bg-white p-2 dark:border-gray-800">
                                        @can('Form Field Edit')   
                                        <a href="{{ route('tenant.edit-form-field', $field->id ) }}" class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                            {{ __('setting.Edit') }}
                                        </a>
                                        @endcan
                                        @can('Form Field Delete')   
                                        <button class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300" wire:click="delete({{ $field->id }})">
                                            {{ __('setting.Delete') }}
                                        </button>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{ $formFields->links() }}
</div>
