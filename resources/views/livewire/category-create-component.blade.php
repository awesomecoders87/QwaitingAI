<div class="p-4">

<h2 class="text-xl font-semibold dark:text-white/90 mb-4">
            {{ $isEdit ? __('text.Edit Service') : __('text.Add Service') }}
</h2>
    <div class="p-4 md:p-5 rounded-lg shadow border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-gray-100 dark:border-gray-800">
        <div class="p-1">

    <form wire:submit.prevent="saveCategory">
    <div class="space-y-5">
    <div class="-mx-2.5 flex flex-wrap gap-y-5">
    @if($tab > 1 && !empty($parentCategory))
    <div class="w-full px-2.5 xl:w-1/2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        {{__('text.Parent Service')}}*
                        </label>
                        <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
                            <select
                                wire:model="parent_id"
                                class="dark:bg-dark-900 z-20 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                :class="isOptionSelected && 'text-gray-500 dark:text-gray-400'"
                                @change="isOptionSelected = true"
                            >
                            <option value="">{{__('text.select Service')}}</option>
                            @foreach($parentCategory as $category)
                            <option value="{{ $category->id }}" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                    {{ $category->name ?? 'None'}}
                                </option>
                            @endforeach

                            </select>
                            <span class="absolute z-30 text-gray-500 -translate-y-1/2 right-4 top-1/2 dark:text-gray-400 rtl:right-auto rtl:left-3">
                                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>
                        @error('parent_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    @endif

    <div class="w-full px-2.5 xl:w-1/2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">
                            {{__('text.name')}}*
                        </label>
                        <input
                            type="text"
                            wire:model="name"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                        />
                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="w-full px-2.5 xl:w-1/2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">
                        {{__('text.service Name (Other Language)')}}
                        </label>
                        <input
                            type="text"
                            wire:model="other_name"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                        />
                        @error('other_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="w-full px-2.5 xl:w-1/2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">
                        {{__('text.Acronym')}}
                        </label>
                        <input
                            type="text"
                            wire:model="acronym"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                        />
                        @error('acronym') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div class="w-full px-2.5 xl:w-1/2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        {{__('text.Sort')}}
                        </label>
                        <input
                            type="number"
                            wire:model="sort"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                        />
                        @error('sort') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>


        <div class="w-full px-2.5 xl:w-1/2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                       {{__('text.display on')}}
                        </label>
                        <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
                            <select
                                wire:model="display_on"
                                class="dark:bg-dark-900 z-20 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                :class="isOptionSelected && 'text-gray-500 dark:text-gray-400'"
                                @change="isOptionSelected = true"
                            >
                                <option value="Display on Transfer & Ticket Screen" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                    {{ __('text.Display on Transfer & Ticket Screen')}}
                                </option>
                                <option value="Ticket Screen" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                    {{ __('text.Ticket Screen')}}
                                </option>

                                <option value="Transfer Screen" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                    {{ __('text.Transfer Screen')}}
                                </option>



                            </select>
                            <span class="absolute z-30 text-gray-500 -translate-y-1/4 right-4 top-1/2 dark:text-gray-400 rtl:right-auto rtl:left-3">
                                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>
                        @error('display_on') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

        {{-- <div class="w-full px-2.5 xl:w-1/2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                         {{__('text.For Screen')}}
                        </label>
                        <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
                            <select
                                wire:model="for_screen"
                                class="dark:bg-dark-900 z-20 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                :class="isOptionSelected && 'text-gray-500 dark:text-gray-400'"
                                @change="isOptionSelected = true"
                            >
                                <option value="Display on Walk-In & Appointment Screen" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                    {{ __('text.Display on Walk-In & Appointment Screen')}}
                                </option>
                                <option value="Walk-In Screen" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                    {{ __('text.Walk-In Screen')}}
                                </option>

                                <option value="Appointment Screen" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                    {{ __('text.Appointment Screen')}}
                                </option>



                            </select>
                            <span class="absolute z-30 text-gray-500 -translate-y-1/4 right-4 top-1/2 dark:text-gray-400 rtl:right-auto rtl:left-3">
                                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>
                        @error('for_screen') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div> --}}


        <div class="w-full px-2.5 xl:w-1/2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        {{__('text.For Screen')}}
                        </label>
                        <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
                            <select
                                wire:model="booking_category_show_for"
                                class="dark:bg-dark-900 z-20 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                :class="isOptionSelected && 'text-gray-500 dark:text-gray-400'"
                                @change="isOptionSelected = true"
                            >
                                <option value="Backend & Online Appointment Screen" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                    {{ __('text.Display on Backend & Online Appointment Screen')}}
                                </option>
                                <option value="Backend" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                    {{ __('text.Backend')}}
                                </option>

                                <option value="Online" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                    {{ __('text.Online Appointment Screen')}}
                                </option>
                            </select>
                            <span class="absolute z-30 text-gray-500 -translate-y-1/4 right-4 top-1/2 dark:text-gray-400 rtl:right-auto rtl:left-3">
                                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>
                        @error('booking_category_show_for') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="w-full px-2.5 xl:w-1/2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                         {{ __('text.visitor in queue')}}
                        </label>
                        <input
                            type="number"
                            wire:model="visitor_in_queue"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                        />
                        @error('visitor_in_queue') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
      @if($this->paymentSet && $this->paymentSet->enable_payment == 1 && $this->paymentSet->category_level == $tab)

                    <div class="w-full px-2.5 xl:w-1/2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        {{ __('text.Select service is paid or free')}}
                        </label>
                         <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
                            <select
                                wire:model.live="is_paid"
                                class="dark:bg-dark-900 z-20 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                :class="isOptionSelected && 'text-gray-500 dark:text-gray-400'"
                                @change="isOptionSelected = true"
                            >
                                <option value="" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                   Select
                                </option>
                                <option value="0" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                   Free
                                </option>
                                <option value="1" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                 Paid
                                </option>
                            </select>
                            <span class="absolute z-30 text-gray-500 -translate-y-1/2 right-4 top-1/2 dark:text-gray-400 rtl:right-auto rtl:left-3">
                                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>

                        @error('is_paid') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    @if($is_paid == 1)
                    <div class="w-full px-2.5 xl:w-1/2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        {{__('text.Service Amount')}}
                        </label>
                        <input
                            type="text"
                            wire:model="amount"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                        />
                        @error('amount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                     @endif
@endif
                    {{-- <div class="w-full px-2.5 xl:w-1/2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Image</label>
            <input type="file" wire:model="img" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
            @if ($img)
                <img src="{{ $img->temporaryUrl() }}" class="mt-2 h-24">
            @elseif ($isEdit && $category->img)
                <img src="{{ url('storage/' . $category->img) }}" class="mt-2 h-24">
            @endif
        </div> --}}

        <div class="w-full px-2.5 xl:w-1/2">
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
        {{ __('text.Image')}}
    </label>
    <input type="file" wire:model="img" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
@error('img')
    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
@enderror
   @if ($img)
    @if (Str::startsWith($img->getMimeType(), 'image/'))
        <div class="mt-2 relative w-fit">
            <img src="{{ $img->temporaryUrl() }}" class="h-24">
            <button type="button" wire:click="removeTempImage"
                class="absolute top-0 right-0 bg-red-500 text-white text-xs px-1 py-0.5 rounded-full">✕</button>
        </div>
    @else
        <div class="mt-2">
            <p class="text-sm text-gray-600">Uploaded file: {{ $img->getClientOriginalName() }}</p>
            <button type="button" wire:click="removeTempImage"
                class="mt-1 bg-red-500 text-white text-xs px-2 py-1 rounded">Remove File</button>
        </div>
    @endif
@elseif ($isEdit && $category->img)
    <div class="mt-2 relative w-fit">
        <img src="{{ url('storage/' . $category->img) }}" class="h-24">
        <button type="button" wire:click="deleteOldImage"
            class="absolute top-0 right-0 bg-red-500 text-white text-xs px-1 py-0.5 rounded-full">✕</button>
    </div>
@endif
</div>

        <div class="w-full px-2.5 xl:w-1/2">
                        <div class="mb-1.5">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('text.Locations') }}*</label>
                            <div wire:ignore>
                            <select wire:model="locations" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" multiple id="locations-select">
                            @if(!empty($allLocations))
                            @foreach($allLocations as  $location)
                                    <option value="{{ $location->id }}">{{ $location->location_name }}</option>
                                @endforeach
                                @endif
                            </select>
                            </div>
                            @error('locations') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>
</div>

 <div class="w-full px-2.5 xl:w-1/2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        {{__('text.Redirect Url')}}
                        </label>
                          <input
                            type="url"
                            wire:model="redirectUrl"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                        />


                        @error('redirectUrl') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="w-full px-2.5">
                        <label class="switch">
                            <input type="checkbox" {{ $isService == true ? 'checked' :'' }}
                                wire:model.live="isService">
                            <span class="slider round"></span>
                        </label>
                         {{__('text.Enable/Disable Service Note.')}}
                    </div>

                     <div class="w-full px-2.5 xl:w-1/2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        {{__('text.Service Time (in mins)')}}
                        </label>
                        <input
                            type="number"
                            wire:model="serviceTime"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                        />
                        @error('serviceTime') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                     <div class="w-full px-2.5 xl:w-1/2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        {{__('text.Service Note')}}
                        </label>
                        <textarea wire:model="note" row="5"  class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"></textarea>

                        @error('note') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                <div class="w-full px-2.5 xl:w-1/2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                         {{__('text.description')}}
                        </label>
                      <div>
                <textarea id="editor" wire:model="description" class="bg-white dark:bg-dark-900 datepickerTwo shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-4 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"></textarea>
</div>

                        @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>



                      <div class="w-full px-2.5 xl:w-1/2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        {{__('text.Ticket Note')}}
                        </label>
                        <textarea wire:model="ticket_note" row="5"  class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"></textarea>

                        @error('ticket_note') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                     <div class="w-full px-2.5 xl:w-1/2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    {{ __('text.Label Image')}}
                </label>
                <input type="file" wire:model="label_image" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
            @error('label_image')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
            @if ($label_image)
                @if (Str::startsWith($label_image->getMimeType(), 'image/'))
                    <div class="mt-2 relative w-fit">
                        <img src="{{ $label_image->temporaryUrl() }}" class="h-24">
                        <button type="button" wire:click="removeTempImage"
                            class="absolute top-0 right-0 bg-red-500 text-white text-xs px-1 py-0.5 rounded-full">✕</button>
                    </div>
                @else
                    <div class="mt-2">
                        <p class="text-sm text-gray-600">Uploaded file: {{ $label_image->getClientOriginalName() }}</p>
                        <button type="button" wire:click="removeTempImage"
                            class="mt-1 bg-red-500 text-white text-xs px-2 py-1 rounded">Remove File</button>
                    </div>
                @endif
            @elseif ($isEdit && $category->label_image)
                <div class="mt-2 relative w-fit">
                    <img src="{{ url('storage/' . $category->label_image) }}" class="h-24">
                    <button type="button" wire:click="deleteOldLabelImage"
                        class="absolute top-0 right-0 bg-red-500 text-white text-xs px-1 py-0.5 rounded-full">✕</button>
                </div>
            @endif
            </div>

            <div class="w-full px-2.5 xl:w-1/2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    {{ __('text.Color') }}
                </label>
                <input type="color" wire:model="service_color"
                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-1 py-1 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                @error('service_color')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="w-full px-2.5 xl:w-1/2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">
                {{ __('text.Label Background Color') }}
            </label>
            <input
                type="color"
                wire:model="label_background_color"
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-1 py-1 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
            />
        </div>

        <div class="w-full px-2.5 xl:w-1/2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">
                 {{ __('text.Label Font Color') }}
            </label>
            <input
                type="color"
                wire:model="label_font_color"
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-1 py-1 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
            />
        </div>

        <div class="w-full px-2.5 xl:w-1/2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">
                {{ __('text.Label Text') }}
            </label>
            <input
                type="text"
                wire:model="label_text"
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
            />
              @error('label_text')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
        </div>

        <div class="w-full px-2.5 xl:w-1/2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">
                {{ __('text.Background Color') }}
            </label>
            <input
                type="color"
                wire:model="bg_color"
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-1 py-1 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
            />
        </div>


</div>
<div class="w-full xl:w-1/2 flex gap-3">
        <button type="submit" class="text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 primary-btn py-2 px-3 font-medium text-white transition-colors rounded-md bg-brand-500 hover:bg-brand-600 flex gap-x-2">
            {{ $isEdit ? __('text.Update') : __('text.Create') }}
        </button>
        <a href="{{ url('category-management') }}" class="px-4 py-3 text-sm font-medium text-white rounded-lg bg-error-500 shadow-theme-xs hover:bg-error-600">
            {{ __('text.Cancel') }}</a>
        </div>
</div>
    </form>
</div>
        </div>
    </div>
<script src="https://cdn.ckeditor.com/4.20.1-lts/standard/ckeditor.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    document.addEventListener('livewire:init', function () {
        initTinyMCE();

        Livewire.hook('message.processed', (message, component) => {
            initTinyMCE();
        });

        function initTinyMCE() {
            if (tinymce.get("tiny-editor")) {
                tinymce.get("tiny-editor").remove();
            }

            tinymce.init({
                selector: 'textarea#tiny-editor',
                setup: function (editor) {
                    editor.on('Change KeyUp', function () {
                        @this.set('description', editor.getContent());
                    });
                },
                height: 300,
                menubar: false,
                plugins: 'lists link image preview',
                toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | outdent indent | bullist numlist | link image'
            });
        }
    });
</script>
<script>
     document.addEventListener("DOMContentLoaded", function () {
                $(document).ready(function() {
                $('#locations-select').select2();
                $('#locations-select').on("change", function (e) {
                let data = $(this).val();
                @this.set('locations', data);
                });
                });
    });


        document.addEventListener("livewire:init", function () {
        // var editor = CKEDITOR.replace('editor');
        // // Sync CKEditor content with Livewire
        // editor.on('change', function () {
        //     @this.set('term_content', editor.getData());
        // });

        // Optional: Set the initial content if Livewire has data
        // Livewire.hook('message.processed', (message, component) => {
        //     editor.setData(@this.term_content);
        // });
    });
</script>
</div>
