<div class="p-4">


<h3 class="fi-section-header-heading text-xl font-semibold dark:text-white mb-4">Location Setting</h3>

<div class="flex -mx-2.5 flex-wrap grid-cols-[--cols-default] lg:grid-cols-[--cols-lg] fi-fo-component-ctn mb-4" style="--cols-default: repeat(1, minmax(0, 1fr)); --cols-lg: repeat(2, minmax(0, 1fr));">
{{-- <div class="w-full px-2.5 xl:w-1/2 mb-3">   
<div class="p-3 rounded-lg shadow border border-gray-200 bg-white h-full col-md-6 gap-3 px-6 py-4 col-[--col-span-default]">
     <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">Location info</h3>
      <ul class="mt-3">
         <li class="flex gap-3 mt-3 grid grid-cols-[--cols-default] fi-fo-component-ctn"  style="--cols-default: 70px 8fr; --cols-lg: 70px 8fr;"><span class="text-gray-400 dark:text-white ">Name</span><span>{{ $location->user->name ?? '' }}</span></li>
         <li class="flex gap-3 mt-3 grid grid-cols-[--cols-default] fi-fo-component-ctn"  style="--cols-default: 70px 8fr; --cols-lg: 70px 8fr;"><span class="text-gray-400 dark:text-white ">Email</span><span>{{ $location->user->email ?? '' }}</span></li>
         <li class="flex gap-3 mt-3 grid grid-cols-[--cols-default] fi-fo-component-ctn"  style="--cols-default: 70px 8fr; --cols-lg: 70px 8fr;"><span class="text-gray-400 dark:text-white ">Phone</span><span>{{ $location->user->phone ?? '' }}</span></li>

      </ul>
   </div>
</div> --}}
   <div class="w-full px-2.5  mb-3">
   <div class="p-3 rounded-lg shadow border border-gray-200 bg-white h-full col-md-6 gap-3 px-6 py-4 col-[--col-span-default]">
      <h3 class="fi-section-header-heading text-base font-semibold text-gray-950 dark:text-white">Location info</h3>
      <ul class="mt-3">
         <li class="flex gap-3 mt-3 grid grid-cols-[--cols-default] fi-fo-component-ctn"  style="--cols-default: 70px 8fr; --cols-lg: 70px 8fr;"><span class="text-gray-400 dark:text-white ">Location</span><span>{{ $location->location_name ?? '' }}</span></li>
         <li class="flex gap-3 mt-3 grid grid-cols-[--cols-default] fi-fo-component-ctn"  style="--cols-default: 70px 8fr; --cols-lg: 70px 8fr;"><span class="text-gray-400 dark:text-white ">Street</span><span>{{ $location->address ?? '' }}</span></li>
         <li class="flex gap-3 mt-3 grid grid-cols-[--cols-default] fi-fo-component-ctn"  style="--cols-default: 70px 8fr; --cols-lg: 70px 8fr;"><span class="text-gray-400 dark:text-white ">City</span><span>{{ $location->city ?? '' }}</span></li>
         <li class="flex gap-3 mt-3 grid grid-cols-[--cols-default] fi-fo-component-ctn"  style="--cols-default: 70px 8fr; --cols-lg: 70px 8fr;"><span class="text-gray-400 dark:text-white ">State</span><span>{{ $location->state ?? '' }}</span></li>
         <li class="flex gap-3 mt-3 grid grid-cols-[--cols-default] fi-fo-component-ctn"  style="--cols-default: 70px 8fr; --cols-lg: 70px 8fr;"><span class="text-gray-400 dark:text-white ">Country</span><span>{{ $location->country ?? '' }}</span></li>
         <li class="flex gap-3 mt-3 grid grid-cols-[--cols-default] fi-fo-component-ctn"  style="--cols-default: 70px 8fr; --cols-lg: 70px 8fr;"><span class="text-gray-400 dark:text-white ">Zip</span><span>{{ $location->zip ?? '' }}</span></li>
      </ul>
   </div>
</div>
</div>
<div class="flex justify-between align-center gap-6 mb-4" style="--cols-default: repeat(1, minmax(0, 1fr)); --cols-lg: repeat(2, minmax(0, 1fr));">
   <h3 class="fi-section-header-heading text-xl font-semibold text-gray-950 dark:text-white">Opening hours</h3>
   <div class="">
   <button class="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);" class="flex gap-x-3 fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-action fi-ac-btn-action" wire:click="showEditModal">
            
   <span class="flex gap-x-2 w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto">
          <i class="ri-edit-line"></i>Edit hours
            </span>
            </button>
</div>
</div>

<div class="flex -mx-2.5 flex-wrap grid-cols-[--cols-default] lg:grid-cols-[--cols-lg] fi-fo-component-ctn mb-4" style="--cols-default: repeat(1, minmax(0, 1fr)); --cols-lg: repeat(2, minmax(0, 1fr));">
<div class="w-full px-2.5 xl:w-1/2 mb-3">
<div class="p-3 rounded-lg shadow border border-gray-200 bg-white h-full col-md-6 gap-3 px-6 py-4 col-[--col-span-default]">
     <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">Weekly hours</h3>

     <ul class="mt-3">
            @foreach($businessHours as $day)
                <li class="flex gap-3 mt-3 grid grid-cols-[--cols-default] fi-fo-component-ctn"  style="--cols-default: 90px 8fr; --cols-lg: 90px 8fr;">
                <span class="text-gray-400 dark:text-white ">{{ $day['day'] }}</span>
                    
                        <span>
                            @if ($day['is_closed'] === "closed")
                                <span class="text-red-500">Closed</span>
                            @else
                            {{ \Carbon\Carbon::parse($day['start_time'])->format('h:i A') }} - 
                            {{ \Carbon\Carbon::parse($day['end_time'])->format('h:i A') }}
                            @endif
                        
                

                    <!-- Show day intervals if available -->
                    @if (!empty($day['day_interval']) &&  $day['is_closed'] != "closed")
                        <ul class="ml-2 pl-4 text-sm text-gray-600">
                            @foreach($day['day_interval'] as $interval)
                                @if(!empty($interval['start_time']) && !empty($interval['end_time']))
                                    <li>
                                    <span class="text-gray-400 dark:text-white text-gray-400 dark:text-white ">:</span>
                                        <span>{{ \Carbon\Carbon::parse($interval['start_time'])->format('h:i A') }} -
                                        {{ \Carbon\Carbon::parse($interval['end_time'])->format('h:i A') }}</span>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    @endif
                    </span>
                </li>
            @endforeach
        </ul>
   
   </div>
</div>
<div class="w-full px-2.5 xl:w-1/2 mb-3">
    <div class="p-3 rounded-lg shadow border border-gray-200 bg-white h-full col-md-6 gap-3 px-6 py-4 col-[--col-span-default]">
        <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">Hours by date</h3>
        

        <ul class="mt-3">
        <li class="flex gap-3 mt-3 grid grid-cols-[--cols-default] fi-fo-component-ctn"><span class="text-gray-400 dark:text-white ">Set hours for specific dates</span><span></span></li>
        @if($customSlots)
        @foreach($customSlots as $day)
                    <li class="flex gap-3 mt-3 grid grid-cols-[--cols-default] fi-fo-component-ctn"  style="--cols-default: 90px 8fr; --cols-lg: 90px 8fr;">
                    <span class="text-gray-400 dark:text-white ">{{ \Carbon\Carbon::parse($day['selected_date'])->format('M d') }}</span>
                        
                            <span>
                                @if ($day['is_closed'] === "closed")
                                    <span class="text-red-500">Closed</span>
                                @else
                                {{ \Carbon\Carbon::parse($day['start_time'])->format('h:i A') }} - 
                                {{ \Carbon\Carbon::parse($day['end_time'])->format('h:i A') }}
                                @endif
                            
                    

                        <!-- Show day intervals if available -->
                        @if (!empty($day['day_interval']) &&  $day['is_closed'] != "closed")
                            <ul class="ml-2 pl-4 text-sm text-gray-600">
                                @foreach($day['day_interval'] as $interval)
                                    @if(!empty($interval['start_time']) && !empty($interval['end_time']))
                                        <li>
                                        <span class="text-gray-400 dark:text-white text-gray-400 dark:text-white ">:</span>
                                            <span>{{ \Carbon\Carbon::parse($interval['start_time'])->format('h:i A') }} -
                                            {{ \Carbon\Carbon::parse($interval['end_time'])->format('h:i A') }}</span>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        @endif
                        </span>
                    </li>
                @endforeach
                @endif
            </ul>
    </div>
  </div>
</div>
@if($showModal)
    <!-- Edit Modal -->
    <div class="fixed inset-0 flex items-center justify-center p-5 overflow-y-auto modal z-99999 p-4" style="background: rgba(0,0,0,0.25);">
        <div class="p-5 modal-close-btn inset-0  m-auto w-full max-w-2xl bg-white-400/50 bg-white rounded-xl relative">
         <button wire:click="showCloseModal" class="absolute right-3 top-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition-colors hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white sm:right-6 sm:top-6 sm:h-11 sm:w-11">
      <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" fill=""></path>
      </svg>
    </button>   
         <div class="mb-2">
            <h3 class="text-2xl mb-2 font-semibold">Edit Opening Hours</h3>
        </div>
            <div class="overflow-y-auto" style="height: calc(100% - 110px);">
            <div class="grid grid-cols-[--cols-default] lg:grid-cols-[--cols-lg] fi-fo-component-ctn gap-6 mb-4" style="--cols-default: repeat(1, minmax(0, 1fr)); --cols-lg: repeat(2, minmax(0, 1fr));">   
            <div class="mt-4 space-y-4 ">
                @foreach($businessHours as $index => $day)
               
                    <div class="mb-4">
                        <label class="font-medium block">{{ $day['day'] }}</label>
                        <select wire:model="businessHours.{{ $index }}.is_closed"
                                class="w-full border p-2 rounded-md border-gray-400">
                            <option value="open">Open</option>
                            <option value="closed">Closed</option>
                        </select>
                       
                            <div class="mt-3 flex gap-4">
                        
                                <input type="time" onclick="this.showPicker()" wire:model="businessHours.{{ $index }}.start_time"
                                       class="w-full border p-2 rounded rounded-md border-gray-400">
                                <input type="time" onclick="this.showPicker()" wire:model="businessHours.{{ $index }}.end_time"
                                       class="w-full border p-2 rounded rounded-md border-gray-400">
                            </div>
                             <!-- Day Intervals -->
                             <div class="mt-3 space-y-2" wire:key="day-{{ $index }}">
                                @foreach($businessHours[$index]['day_interval'] as $slotIndex => $slot)
                                    <div class="flex gap-4 items-center"  wire:key="interval-{{ $index }}-{{ $slotIndex }}">
                                        <input type="time" onclick="this.showPicker()" wire:model="businessHours.{{ $index }}.day_interval.{{ $slotIndex }}.start_time"
                                               class="w-full border p-2 rounded rounded-md border-gray-400">
                                        <input type="time" onclick="this.showPicker()" wire:model="businessHours.{{ $index }}.day_interval.{{ $slotIndex }}.end_time"
                                               class="w-full border p-2 rounded rounded-md border-gray-400">
                                        <button wire:click="removeSlot({{ $index }}, {{ $slotIndex }})"
                                                class="px-2 py-1 bg-red-500 text-black rounded hover:bg-red-600">
                                            ✖
                                        </button>
                                    </div>
                                @endforeach
                                <button wire:click="addSlot({{ $index }})"
                                class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-sm fi-btn-size-sm gap-1 px-2.5 py-1.5 text-sm inline-grid shadow-sm bg-custom-600 text-black hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50">
                                    + Add Time Slot
                                </button>
                            </div>
                    </div>
                @endforeach
            </div>
            <div>
    <h3 class="text-lg font-semibold">Set Opening Hours</h3>

    <!-- Dynamically Display Common Time Slots -->
    <div class="mt-4 space-y-4">
       
            @foreach($customSlots as $slotIndex => $slot)
                <div  wire:key="days-{{ $slotIndex }}">
                   <div class="mt-3 flex gap-4">
                    <input type="date" onclick="this.showPicker()" wire:model="customSlots.{{ $slotIndex }}.selected_date" class="w-full border p-2 rounded border-gray-400">
                        <select wire:model="customSlots.{{ $slotIndex }}.is_closed"
                                class="w-full border p-2 rounded">
                            <option value="open">Open</option>
                            <option value="closed">Closed</option>
                    </select>
                    </div>
                    <div class="mt-3 flex gap-4">
                    <input type="time" onclick="this.showPicker()" wire:model="customSlots.{{ $slotIndex }}.start_time" class="w-full border p-2 rounded" placeholder="Start Time">
                    <input type="time" onclick="this.showPicker()" wire:model="customSlots.{{ $slotIndex }}.end_time" class="w-full border p-2 rounded" placeholder="End Time">
                
                    </div>    
                    @foreach($slot['day_interval'] as $Index => $interval)
                    <div class="mt-3 flex gap-4">
                    <input type="time" onclick="this.showPicker()" wire:model="customSlots.{{ $slotIndex }}.day_interval.{{$Index}}.start_time" class="w-full border p-2 rounded" placeholder="Start Time">
                    <input type="time" onclick="this.showPicker()" wire:model="customSlots.{{ $slotIndex }}.day_interval.{{$Index}}.end_time" class="w-full border p-2 rounded" placeholder="End Time">
                    <button type="button" wire:click="removeCustomSlot({{ $slotIndex }},{{ $Index }})" class="px-2 py-1 bg-red-500 text-black rounded hover:bg-red-600">  ✖</button>
                    </div>   
                    @endforeach
                    <div class="flex mt-3 gap-2">
                        <button type="button" wire:click="addCustomSlot({{ $slotIndex }})"  class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-sm fi-btn-size-sm gap-1 px-2.5 py-1.5 text-sm inline-grid shadow-sm bg-custom-600 text-black hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50">+ Add Time Slot</button>
                        <button type="button" wire:click="deleteCustomSlot({{ $slotIndex }})" class="px-2 py-1 bg-red-500 text-black rounded hover:bg-red-600">x Remove</button>
                    </div>
                </div>
                    @endforeach
                    <div>
                    <button type="button" wire:click="addNextCustomSlot()" class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-sm fi-btn-size-sm gap-1 px-2.5 py-1.5 text-sm inline-grid shadow-sm bg-custom-600 text-black hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50">+ Add Next Date Slot</button>
                    </div>
                </div>

            <!-- Button to Add Time Slot -->
        </div>
  
        </div>
</div>

           
            <div class="mt-6 flex justify-end space-x-3 gap-3">
                <button wire:click="save" class="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600); flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto">
                    Save
                </button>
                <button wire:click="showCloseModal"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg">
                    Cancel
                </button>
            </div>
        </div>
    </div>
@endif

<script src="{{ config('app.asset_url') }}/js/livewire.js?v={{ time()}}" type="module"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   <script>
document.addEventListener('livewire:init', () => {
    Livewire.on('saved', (response) => {
        Swal.fire({
            title: "Saved Successfully",
            text: response.message,
            icon: "success",
            allowOutsideClick: false,
        }).then(() => {
            window.location.reload();
        });
    });
});
   </script>
</div>
