<div>
  <!-- Settings Card -->
  <div class="max-w-2xl mx-auto bg-white shadow-lg rounded-xl p-6 space-y-6 mt-4 dark:bg-gray-800 dark:border-gray-700">

    <!-- Notice -->
    <div class="text-sm text-purple-600 bg-purple-50 border border-purple-200 rounded p-3">
      {{ __('setting.You will soon be able to automate more of your operations. Keep an eye out for updates.') }}'
    </div>

    <!-- Options List -->
    <div class="space-y-4">
      <!-- Option 1 -->
      <div class="flex justify-between items-center border-b pb-3 cursor-pointer hover:bg-gray-100 p-3 dark:border-gray-700 dark:hover:bg-gray-700" wire:click="openAddBookingsWaitlistModal">
        <span class="text-gray-800 text-sm  dark:text-gray-200">{{ __('setting.Add Bookings To Waitlist before their start time') }}</span>
        <span class="text-xs font-semibold px-3 py-1 rounded-full {{ $is_add_bookings_to_waitlist ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-600' }}">{{ $is_add_bookings_to_waitlist ? 'On' : 'Off' }}</span>
      </div>

      <!-- Option 2 -->
      <div class="flex justify-between items-center border-b pb-3 cursor-pointer hover:bg-gray-100 p-3 dark:border-gray-700 dark:hover:bg-gray-700" wire:click="closeWaitlistEarly">
        <span class="text-gray-800 text-sm  dark:text-gray-200">{{ __('setting.Close the waitlist early') }}</span>
        <span class="text-xs font-semibold px-3 py-1 rounded-full {{ $is_close_waitlist_early ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-600' }}">{{ $is_close_waitlist_early ? 'On' : 'Off' }}</span>
      </div>

      <!-- Option 3 -->
      <div class="flex justify-between items-center border-b pb-3 cursor-pointer hover:bg-gray-100 p-3 dark:border-gray-700 dark:hover:bg-gray-700" wire:click="assignVisitOnAlert">
        <span class="text-gray-800 text-sm  dark:text-gray-200">{{ __('setting.Assign visit on alert') }}</span>
        <span class="text-xs font-semibold px-3 py-1 rounded-full {{ $is_assign_visit_on_alert ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-600' }}">{{ $is_assign_visit_on_alert ? 'On' : 'Off' }}</span>
      </div>

      <!-- Option 4 -->
      <div wire:click="openWaitlistPrioritizationModal" class="flex justify-between items-center cursor-pointer hover:bg-gray-100 p-3 dark:border-gray-700 dark:hover:bg-gray-700">
        <span class="text-gray-800 text-sm dark:text-gray-200">{{ __('setting.Waitlist Prioritization') }}</span>
        <span class="text-xs font-semibold px-3 py-1 rounded-full {{ $is_waitlist_prioritization ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-600' }}">{{ $is_waitlist_prioritization ? 'On' : 'Off' }}</span>
      </div>
    </div>
  </div>

  @if($showAddBookingsToWaitlistModal)
  <!-- 🔹 Modal: Add Booking -->
  <div id="booking-modal" class="fixed inset-0 bg-opacity-50 flex items-center justify-center bg-gray-800/50 z-99999">
    <div class="bg-white rounded-lg shadow-lg max-w-lg w-full p-6 relative" @click.away="showModal = false">
      <button wire:click="closeAddBookingsWaitlistModal" class="absolute top-3 right-3 text-gray-600 hover:text-black text-xl">&times;</button>

      <h2 class="text-xl font-semibold text-gray-800 mb-4">
        {{ __('setting.Add Bookings To Waitlist before their start time') }}
      </h2>

      <div class="space-y-4">
        <div class="flex items-center space-x-4">
          <label for="enabled" class="text-gray-800 text-sm font-medium">{{ __('setting.Enabled') }}</label>
          <input id="{{ $this->is_add_bookings_to_waitlist ? 'enabled' : ''  }}" type="checkbox" class="w-5 h-5 text-brand-600 border-gray-300 rounded" wire:model="is_add_bookings_to_waitlist">
        </div>

        <div>
          <label for="when-to-move" class="block text-sm font-medium text-gray-700 mb-1">When to move booking</label>
          <select id="when-to-move" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500" wire:model="when_to_move_booking">
            <option value="">{{ __('setting.Select time') }}</option>
            <option value="10 minutes">{{ __('setting.10 minutes before') }}</option>
            <option value="15 minutes">{{ __('setting.15 minutes before') }}</option>
            <option value="30 minutes">{{ __('setting.30 minutes before') }}</option>
          </select>
        </div>

        <div>
          <label for="position" class="block text-sm font-medium text-gray-700 mb-1">{{ __('setting.Move to position') }}</label>
          <select id="position" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500" wire:model="move_to_position">
            <option value="first">{{ __('setting.First') }}</option>
            <option value="last">{{ __('setting.Last') }}</option>
          </select>
        </div>

        <div class="text-right">
          <button class="bg-purple-600 text-white font-semibold px-6 py-2 rounded hover:bg-purple-700 transition" wire:click="saveAddBookingsToWaitlist">
            {{ __('setting.Save') }}
          </button>
        </div>
      </div>
    </div>
  </div>
  @endif


  @if($showWaitlistPrioritizationModal)
  <!-- 🔹 Modal: Waitlist Prioritization -->
  <div id="prioritization-modal" class="fixed inset-0 flex items-center justify-center bg-gray-800/50 z-99999">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-5xl p-8 relative dark:bg-gray-800  dark:text-gray-200">
      <button wire:click="closeWaitlistPrioritizationModal"  class="absolute right-3 top-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition-colors hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white sm:right-6 sm:top-6 sm:h-11 sm:w-11">
      <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" fill=""></path>
      </svg>
    </button>

      <!-- Header -->
      <h2 class="text-2xl font-semibold text-gray-800 mb-6  dark:text-white">
        {{ __('setting.Waitlist Prioritization') }}
      </h2>

      <div class="flex items-center space-x-4 mb-4">
        <label for="enabled" class="text-gray-800 text-sm font-medium dark:text-gray-200">{{ __('setting.Enabled') }}</label>
        <input id="{{ $this->is_waitlist_prioritization ? 'enabled' : ''  }}" type="checkbox" class="w-5 h-5 text-brand-600 border-gray-300 rounded" wire:model="is_waitlist_prioritization">
      </div>

      @foreach ($rules as $index => $rule)
<div class="flex flex-wrap gap-4 mb-4 items-center">

    <!-- Type -->
    <select wire:model="rules.{{ $index }}.type"
        class="border rounded px-4 py-2 text-sm text-gray-800 focus:ring-2 focus:ring-brand-500 w-48 dark:bg-gray-800 dark:text-white">
        <option value="">Select</option>
        <option value="Service">Service</option>
    </select>

    <!-- Category -->
    <select wire:model="rules.{{ $index }}.category"
        wire:change="onCategoryChanged($event.target.value, {{ $index }})"
        class="border rounded px-4 py-2 text-sm text-gray-800 focus:ring-2 focus:ring-brand-500 w-48 dark:bg-gray-800 dark:text-white">
        <option value="">Select</option>
        <option value="all">All</option>
        @foreach ($firstCategories as $keyCat => $nameCate)
            @php
                $category = $language != 'en' && isset($translations[$nameCate->name][$language])
                    ? $translations[$nameCate->name][$language]
                    : $nameCate->name;
            @endphp
            <option value="{{ $nameCate->id }}">{{ $category }}</option>
        @endforeach
    </select>

    <!-- Subcategory (only if category is selected and subcategories exist) -->
    @if (!empty($rule['category']) && !empty($secondCategories[$index]) && $rule['category'] !== 'all' && count($secondCategories[$index]) > 0)
        <select wire:model="rules.{{ $index }}.subcategory"
            wire:change="onSubcategoryChanged($event.target.value, {{ $index }})"
            class="border rounded px-4 py-2 text-sm text-gray-800 w-48 dark:bg-gray-800 dark:text-white">
            <option value="">Select</option>
            @foreach ($secondCategories[$index] ?? [] as $keySCat => $nameSCate)
                @php
                    $seccategory = $language != 'en' && isset($translations[$nameSCate][$language])
                        ? $translations[$nameSCate][$language]
                        : $nameSCate;
                @endphp
                <option value="{{ $keySCat }}">{{ $seccategory }}</option>
            @endforeach
        </select>
    @endif

    <!-- Child Category (only if subcategory is selected and child categories exist) -->
    @if (!empty($rule['subcategory']) && !empty($thirdCategories[$index]) && $rule['category'] !== 'all' && count($thirdCategories[$index]) > 0)
        <select wire:model="rules.{{ $index }}.childcategory"
            class="border rounded px-4 py-2 text-sm text-gray-800 w-48 dark:bg-gray-800 dark:text-white">
            <option value="">Select</option>
            @foreach ($thirdCategories[$index] ?? [] as $keySCat => $nameSCate)
                @php
                    $thirdcategory = $language != 'en' && isset($translations[$nameSCate][$language])
                        ? $translations[$nameSCate][$language]
                        : $nameSCate;
                @endphp
                <option value="{{ $keySCat }}">{{ $thirdcategory }}</option>
            @endforeach
        </select>
    @endif

    <!-- Remove Button -->
    <button type="button" wire:click="removeRule({{ $index }})"
        class="text-xl text-gray-600 hover:text-red-500">&times;</button>

</div>
@endforeach



      <!-- Add rule link -->
      <div class="mb-6">
        <button class="text-sm text-gray-800 font-medium flex items-center hover:underline" wire:click="addRule">
          <span class="text-xl mr-1">＋</span> {{ __('setting.Add rule') }}
        </button>
      </div>

      <!-- Divider -->
      <hr class="border-t mb-6 dark:border-gray-700">

      <!-- Save Button -->
      <div>
        <button class="bg-brand-500 hover:bg-brand-600 text-white font-semibold px-6 py-2 rounded shadow" wire:click="saveWaitlistPrioritization">
          {{ __('setting.Save') }}
        </button>
      </div>
    </div>
  </div>
  @endif

</div>