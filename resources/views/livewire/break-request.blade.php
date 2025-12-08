<div class="p-4">

    <h2 class="text-xl font-semibold dark:text-white/90 mb-4">{{ __('text.Break Requests') }}</h2>

    <div class="mb-4">
        <div class="relative w-full lg:w-[300px]">
        <span class="pointer-events-none absolute top-1/3 left-4 -translate-y-1/4">
                    <svg class="fill-gray-500 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04199 9.37381C3.04199 5.87712 5.87735 3.04218 9.37533 3.04218C12.8733 3.04218 15.7087 5.87712 15.7087 9.37381C15.7087 12.8705 12.8733 15.7055 9.37533 15.7055C5.87735 15.7055 3.04199 12.8705 3.04199 9.37381ZM9.37533 1.54218C5.04926 1.54218 1.54199 5.04835 1.54199 9.37381C1.54199 13.6993 5.04926 17.2055 9.37533 17.2055C11.2676 17.2055 13.0032 16.5346 14.3572 15.4178L17.1773 18.2381C17.4702 18.531 17.945 18.5311 18.2379 18.2382C18.5308 17.9453 18.5309 17.4704 18.238 17.1775L15.4182 14.3575C16.5367 13.0035 17.2087 11.2671 17.2087 9.37381C17.2087 5.04835 13.7014 1.54218 9.37533 1.54218Z" fill=""></path>
                    </svg>
                </span>
        <input type="text" wire:model.live.debounce.500ms="search"
               placeholder="Search by reason..."
               class="dark:bg-dark-900 bg-white shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-[42px] w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-[42px] text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden xl:w-[300px] dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">

</div>
    </div>

    <div class="p-4 md:p-4 rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow">
        <div class="max-w-full overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 table-auto">
            <thead class="dark:bg-gray-700">
                <tr>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 dark:text-white">{{ __('text.staff') }}</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 dark:text-white">{{ __('text.reason') }}</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 dark:text-white">{{ __('text.Comment') }}</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 dark:text-white">{{ __('text.status') }}</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 dark:text-white">{{ __('text.Requested Start At') }}</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 dark:text-white">{{ __('text.Requested End At') }}</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 dark:text-white">{{ __('text.created at') }}</th>
                    <th class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-white">{{ __('text.Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($staffBreaks as $break)
                    <tr class="hover:bg-gray-100 dark:hover:bg-gray-700">
                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">{{ $break->staff->name ?? '' }}</td>
                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">{{ $break->reason }}</td>
                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">{{ $break->comment }}</td>
                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">
                            @php
                                $statusLabels = ['Pending', 'Approved', 'Rejected','Auto Approved'];
                                $statusColors = ['bg-yellow-200 text-yellow-800', 'bg-green-200 text-green-800', 'bg-red-200 text-red-800','bg-green-200 text-green-800'];
                                $status = $break->status ?? 0;
                            @endphp
                            <span class="py-1 rounded text-xs font-semibold">
                                {{ $statusLabels[$status] }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">
                             <?php  $datetimeFormat = App\Models\AccountSetting::showDateTimeFormat(); // Fallback to default format
                          // Return the formatted date based on the format from AccountSetting table
                          if(!empty($break->time_start)){
                            
                              echo $created = \Carbon\Carbon::parse($break->time_start)->format($datetimeFormat) ?? '';
                          }

                          ?> 
                         
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">
                             <?php  $datetimeFormat = App\Models\AccountSetting::showDateTimeFormat(); // Fallback to default format
                          // Return the formatted date based on the format from AccountSetting table
                         if(!empty($break->time_end)){
                          echo $created = \Carbon\Carbon::parse($break->time_end)->format($datetimeFormat) ?? '';
                         }
                          ?> 
                         
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">
                             <?php  $datetimeFormat = App\Models\AccountSetting::showDateTimeFormat(); // Fallback to default format
                          // Return the formatted date based on the format from AccountSetting table
                     
                          echo $created = \Carbon\Carbon::parse($break->created_at)->format($datetimeFormat) ?? '';
                         
                          ?> 
                         
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">
                          
                             <div class="flex items-center justify-center">
                                    <div x-data="{openDropDown: false}" class="relative">
                                        <button @click="openDropDown = !openDropDown"
                                            class="text-gray-500 dark:text-gray-400 action-btn">
                                            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24"
                                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                    d="M5.99902 10.245C6.96552 10.245 7.74902 11.0285 7.74902 11.995V12.005C7.74902 12.9715 6.96552 13.755 5.99902 13.755C5.03253 13.755 4.24902 12.9715 4.24902 12.005V11.995C4.24902 11.0285 5.03253 10.245 5.99902 10.245ZM17.999 10.245C18.9655 10.245 19.749 11.0285 19.749 11.995V12.005C19.749 12.9715 18.9655 13.755 17.999 13.755C17.0325 13.755 16.249 12.9715 16.249 12.005V11.995C16.249 11.0285 17.0325 10.245 17.999 10.245ZM13.749 11.995C13.749 11.0285 12.9655 10.245 11.999 10.245C11.0325 10.245 10.249 11.0285 10.249 11.995V12.005C10.249 12.9715 11.0325 13.755 11.999 13.755C12.9655 13.755 13.749 12.9715 13.749 12.005V11.995Z"
                                                    fill="" />
                                            </svg>
                                        </button>
                                        <div x-show="openDropDown" @click.outside="openDropDown = false"
                                            class="dropdown-menu shadow-theme-lg dark:bg-gray-dark absolute top-full right-0 z-40 w-40 space-y-1 rounded-2xl border border-gray-200 bg-white p-2 dark:border-gray-800">
                                            @can('Booking Status Update')
                                                @if($break->status == 0 || $break->status != 3)
                                                <button wire:click="openStatusModal({{ $break->id }})"
                                                    class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                                {{ __('text.Update Status') }}
                                                </button>
                                                @endif
                                            @endcan

                                        </div>
                                    </div>
                                </div>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-4 text-center text-gray-500 dark:text-white">{{ __('text.No break requests found.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
                        </div>                
        <div class="p-4">
            {{ $staffBreaks->links() }}
        </div>
    </div>

    <!-- Status Modal -->
     
 @if ($showStatusModal)

<div class="fixed inset-0 flex items-center justify-center p-5 overflow-y-auto z-99999" style="">
    <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-400/50"></div>
    <div
        class="no-scrollbar relative w-full max-w-[507px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-5">
        <!-- close btn -->
        <button wire:click="$set('showStatusModal', false)" class="absolute right-3 top-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition-colors hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white sm:right-6 sm:top-6 sm:h-11 sm:w-11">
      <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" fill=""></path>
      </svg>
    </button>
        <div class="px-2 pr-14">
            <h4 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
               {{ __('text.Change Break Status') }}
            </h4>

        </div>
        <form class="flex flex-col">
            <div class="custom-scrollbar h-[200px] overflow-y-auto px-2">

                <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-1">

                 
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        {{ __('text.status') }}
                        </label>
                        <select wire:model="selectedStatus"
                                class="dark:bg-dark-900 z-20 h-11 w-full rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                :class="isOptionSelected && 'text-gray-500 dark:text-gray-400'"
                                @change="isOptionSelected = true">
                                <option value="" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                     {{ __('text.Select') }}
                                </option>
                             

                                <option value="1" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">Approved</option>
                    <option value="2" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">Rejected</option>
                            </select>
                        @error('selectedStatus') <span class="text-error-500">{{ $message }}</span> @enderror
                    </div>
                  
                </div>

                <div>
                    <div class="flex items-center gap-3 px-2 mt-6 lg:justify-end">
                        <button wire:click="$set('showStatusModal', false)" type="button"
                            class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] sm:w-auto">
                            {{ __('text.Close') }}
                        </button>
                        <button type="button"
                            class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto"
                            wire:click.prevent="updateStatus">
                            {{ __('text.Save') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
    </div>

</div>

