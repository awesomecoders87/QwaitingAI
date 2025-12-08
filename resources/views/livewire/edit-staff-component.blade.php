<div class="p-4">

 <h3 class="text-xl mb-4 font-medium dark:text-white/90">
                {{ __('text.Edit Staff') }}
            </h3>

    <div class="rounded-lg shadow border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

        <div class="p-5 space-y-6 border-t border-gray-100 dark:border-gray-800 sm:p-6">
            <form wire:submit.prevent="updateStaff">
                <div class="-mx-2.5 flex flex-wrap gap-y-5">
                    @if($enablePriority)
                      <div class="w-full px-2.5 xl:w-1/2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                             {{ __('text.Level') }}*
                        </label>
                        <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
                            <select wire:model.live="level"
                                class="dark:bg-dark-900 z-20 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                               >
                                <option value="" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                    {{ __('text.select level') }}
                                </option>

                                <option value="1" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                    {{ __('text.Level') }} 1
                                </option>
                                <option value="2" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                    {{ __('text.Level') }} 2
                                </option>
                                <option value="3" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                    {{ __('text.Level') }} 3
                                </option>

                            </select>
                        </div>
                        @error('level') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    @if($level > 1)

                      <div class="w-full px-2.5 xl:w-1/2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                             {{ __('text.Parent Staff') }}*
                        </label>
                        <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
                            <select wire:model.live="parent_id"
                                class="dark:bg-dark-900 z-20 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                               >
                               <option value="" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                   {{ __('text.select Staff') }}
                               </option>
                               @foreach($staffList as $value)
                               <option value="{{ $value->id }}"
                                   class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                   {{ $value->name }}
                               </option>
                               @endforeach
                            </select>
                        </div>
                        @error('level') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    @endif
                    @if($level == 1 || $level == 2)
                 <div class="w-full px-2.5 xl:w-1/2">
                     <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                         {{ __('text.Sort') }}
                     </label>
                     <input type="number" wire:model="priority" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                     @error('priority') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                 </div>
                 @endif
                @endif

                    <div class="w-full px-2.5 xl:w-1/2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            {{ __('text.name') }}*
                        </label>
                        <input type="text" wire:model="name" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="w-full px-2.5 xl:w-1/2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                             {{ __('text.Email') }}
                        </label>
                        <input type="text" wire:model="email" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="w-full px-2.5 xl:w-1/2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            {{ __('text.Phone') }}
                        </label>
                        <input type="text" wire:model="phone" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        @error('phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="w-full px-2.5 xl:w-1/2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            {{ __('text.Username') }}*
                        </label>
                        <input type="text" wire:model="username" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        @error('username') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>


                    <div class="w-full px-2.5 xl:w-1/2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            {{ __('text.role') }}*
                        </label>
                        <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
                            <select wire:model="role"
                                class="dark:bg-dark-900 z-20 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                :class="isOptionSelected && 'text-gray-500 dark:text-gray-400'"
                                @change="isOptionSelected = true">
                                <option value="" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                     {{ __('text.Select Role') }}
                                </option>
                                   <option value="1" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                     {{ __('text.Admin') }}
                                </option>
                                @foreach($allRoles as $id => $role)
                                <option value="{{ $role->id }}"
                                    class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                    {{ $role->name }}
                                </option>
                                @endforeach
                            </select>
                            <span
                                class="absolute z-30 text-gray-500 -translate-y-1/2 right-4 top-1/2 dark:text-gray-400">
                                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>
                        @error('role') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="w-full px-2.5 xl:w-1/2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                             {{ __('text.Counter') }}*
                        </label>
                        <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
                            <select wire:model="counter_id"
                                class="dark:bg-dark-900 z-20 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                :class="isOptionSelected && 'text-gray-500 dark:text-gray-400'"
                                @change="isOptionSelected = true">
                                <option value="" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                    {{ __('text.Select Counter') }}
                                </option>
                                @foreach($allCounters as $counter)
                                <option value="{{ $counter->id }}"
                                    class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                    {{ $counter->name }}
                                </option>
                                @endforeach
                            </select>
                            <span
                                class="absolute z-30 text-gray-500 -translate-y-1/2 right-4 top-1/2 dark:text-gray-400">
                                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>
                        @error('counter_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                   <div class="w-full px-2.5 xl:w-1/2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        {{ __('text.Assign Counters') }}*
                    </label>

                    <div class="grid grid-cols-1 gap-2">
                        {{-- Select All Checkbox --}}
                        <label class="inline-flex items-center space-x-2 text-sm font-semibold text-gray-800 dark:text-gray-300">
                            <input type="checkbox"
                                wire:model="selectAllCounters"
                                wire:click="toggleSelectAllCounters"
                                class="rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500 dark:bg-gray-800 dark:border-gray-600">
                            <span>Select All Counters</span>
                        </label>

                        {{-- Individual Counters --}}
                        @foreach($allCounters as $counter)
                            <label class="inline-flex items-center space-x-2 text-sm text-gray-700 dark:text-gray-200">
                                <input type="checkbox"
                                    wire:model.live="selectedAssignCounters"
                                    value="{{ $counter->id }}"
                                    class="rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500 dark:bg-gray-800 dark:border-gray-600">
                                <span>{{ $counter->name }}</span>
                            </label>
                        @endforeach
                    </div>

                    @error('selectedAssignCounters')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                    <div class="w-full px-2.5">
                        <div class="mb-1.5">
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('text.Locations') }}*</label>
                            <div wire:ignore>
                                <select wire:model="locations" class="w-full p-2 border rounded" multiple="multiple"
                                    id="locations-select">
                                    @if(!empty($allLocations))
                                    @foreach($allLocations as $location)
                                    <option value="{{ $location->id }}"
                                        {{ in_array($location->id, $locations ?? []) ? 'selected' : '' }}>
                                        {{ $location->location_name }}</option>
                                    @endforeach
                                    @endif
                                </select>
                            </div>
                            @error('locations') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>


                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            {{ __('text.address') }}
                        </label>
                        <textarea wire:model="address" rows="3" class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"></textarea>
                        @error('address') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="w-full px-2.5 ">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            {{ __('text.Unique ID') }}
                        </label>
                        <input type="text" wire:model="unique_id" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        @error('unique_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>



                    <div class="w-full px-2.5">
                        <label class="switch">
                            <input type="checkbox" {{ $enable_desktop_notification == true ? 'checked' :'' }}
                                wire:model.live="enable_desktop_notification">
                            <span class="slider round"></span>
                        </label>
                         {{ __('text.enable/disable desktop notifications') }}
                    </div>
                    @if($hold_queue_feature)
                    <div class="w-full px-2.5">
                        <label class="switch">
                            <input type="checkbox" {{ $enable_hold_queue == true ? 'checked' :'' }}
                                wire:model.live="enable_hold_queue">
                            <span class="slider round"></span>
                        </label>
                       {{ __('text.Enable/Disable Hold Queue') }}
                    </div>
                    @endif
                    <div class="w-full px-2.5">
                        <label class="switch">
                            <input type="checkbox" {{ $show_next_button == true ? 'checked' :'' }}
                                wire:model.live="show_next_button">
                            <span class="slider round"></span>
                        </label>
                         {{ __('text.show next button - call screen') }}
                    </div>

                    <div class="w-full px-2.5">
                        <h3 class="text-base py-4 font-medium text-gray-800 dark:text-white/90">
                            {{ __('text.Selected Services') }}
                        </h3>
                         <!-- Select All Checkbox -->
                        <div class="mb-3">
                            <input type="checkbox" wire:model="selectAll" wire:click="toggleSelectAll">
                            <label class="select-all">{{ __('text.Select All') }}</label>
                        </div>


                        <ul class="px-5 flex gap-5 flex-col">

                            @foreach($getcategories as $category)
                            <li>
                                <input type="checkbox" wire:model.live="selectedCategories" value="{{ $category['id'] }}">
                                {{ $category['name'] }}

                                @if (!empty($category['children']))
                                <ul class="ml-4  flex gap-3 mt-2 flex-col" style="margin-left: 25px;">
                                    @foreach ($category['children'] as $subCategory)
                                    @include('livewire.partials.category-item', ['category' =>
                                    $subCategory,'selectedcategories'=>$selectedCategories ?? []])
                                    @endforeach
                                </ul>
                                @endif
                            </li>
                            @endforeach
                        </ul>

                    </div>



                    <div class="px-2.5">
                        <button type="submit"
                            class="px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                             {{ __('text.Save') }}

                        </button>

                        <a href="{{ url('staff') }}"
                            class="px-4 py-3 text-sm font-medium text-white rounded-lg bg-error-500 shadow-theme-xs hover:bg-error-600">
                            {{ __('text.Cancel') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {

        $(document).ready(function() {
            $('#locations-select').select2();
            $('#locations-select').on("change", function(e) {
                let data = $(this).val();
                console.log(data);
                // $wire.locations = data;
                @this.set('locations', data);
            });
        });
    });
    </script>
</div>
