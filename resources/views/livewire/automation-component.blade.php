<div class="p-4">
       @if($mainpage)
        <h3 class="fi-section-header-heading text-xl font-semibold dark:text-white">{{ __('setting.Automation') }}</h3>
    <ul class="mt-3 border shadow rounded border-gray-300 dark:border-gray-700">
         <li class="bg-white flex gap-3 p-6 border-b border-gray-300 justify-items-center justify-between cursor-pointer hover:bg-gray-100  dark:border-gray-700 dark:bg-white/[0.03] dark:hover:bg-gray-700" wire:click="showPage('addBookingsToWaitlist')">
            <div class="">
               <h3 class="fi-section-header-heading text-base font-semibold dark:text-white">{{ __('setting.Add Bookings To Waitlist before their start time') }}</h3>
            </div>
            <div class="flex">
               <span><svg xmlns="http://www.w3.org/2000/svg" class="white-path" width="24" height="24" viewBox="0 0 24 24"><path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path></svg></span>
            </div>
            
         </li>
    </ul>
     @endif

   @if($addBookingsToWaitlist)
       <div>
       <div class="inside-page-heading flex items-center gap-4">
        <button type="button" wire:click="showPage('mainpage')"> <svg class="fi-icon-btn-icon h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"></path>
</svg> </button>
       <h3 class="fi-section-header-heading text-xl font-semibold dark:text-white">{{ __('setting.Add Bookings To Waitlist before their start time') }}</h3>
      </div>   
       @livewire('App\Livewire\AddBookingsToWaitlist',['teamId' => $teamId, 'locationId' => $locationId])
       </div>
       @endif
      
</div>
