<div class="p-4">

<style>
    .tool-description{
        position:absolute;
        left:50%;
        bottom:100%;
        transform:translateX(-50%);
        margin-bottom:0.5rem;
        color:#fff !important;
        background-color:#374151;
        font-size:0.875rem;
        padding:0.25rem 0.5rem;
        border-radius:0.25rem;
        width:14rem;
        text-align:left;
        z-index:50;
    }
</style>

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

    <div class="max-w-2xl mx-auto bg-white rounded-xl shadow p-6 dark:bg-white/[0.03]">

    <h2 class="text-xl font-semibold mb-4">{{ __('setting.Service Level') }}</h2>

    <form wire:submit.prevent="storeLevel" class="space-y-6">
   <!-- Section 1 -->
           <div class="mb-4 relative">
                                <label class="flex text-sm font-medium text-gray mt-2 mb-2 items-center">
                                    {{ __('setting.Set Acronyms') }}
                                    <span class="ml-2 cursor-pointer relative inline-block">
                                        <button type="button" data-tooltip-id="tooltip-acronyms" class="ml-1 text-gray-400 hover:text-gray-600 focus:outline-none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9" />
                                                <line x1="12" y1="8" x2="12" y2="8" />
                                                <line x1="12" y1="12" x2="12" y2="16" />
                                        </svg>
                                        </button>
                                        <div id="tooltip-acronyms" class="hidden tool-description">
                                            {{ __('setting.Select the level acronym to be displayed on the ticket printout') }}
                                        </div>
                                    </span>

                                </label>

                                <select wire:model.live="acronyms_show_level"
                                    class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                    <option value="1">{{ __($level1) }}</option>
                                    <option value="2">{{ __($level2) }}</option>
                                    <option value="3">{{ __($level3) }}</option>
                                </select>
                            </div>
      <!-- Divider -->
    <hr class="my-4 border-t border-gray-300">

       <!-- Section 2 -->
    <div><h2 class="font-bold"> {{ __('setting.Define Level Label') }}</h2></div>
      <!-- Level 1 -->
      <div class="border-b pb-4">
        <label class="block text-gray-700 font-medium mb-1 dark:text-white">Level 1 (EN)</label>
        <input type="text" wire:model="level1" class="w-full border rounded border-gray-300 p-2 mb-2 dark:bg-gray-900 dark:text-white dark:border-gray-700"
               placeholder="{{ __('setting.Key') }}">
        @error('level1') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

        <button type="button" class="flex justify-between items-center w-full text-sm text-gray-600 font-medium py-2 dark:text-white"
                onclick="toggleAccordion('level1')">
          <span class="">Other Languages</span>
          <svg id="icon-level1" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>

       <div id="content-level1" class="hidden space-y-3 mt-2">
       @if(!empty($availableLanguages[0]))
        @foreach($availableLanguages[0] as $langIndex => $lang)

                <!-- Level Input -->
                <div>
                    <label class="block text-sm font-medium">
                        Level 1 ({{ $allLanguages[$lang] }})
                    </label>
                    <input type="text"
                           wire:model.defer="translations.level1.{{ $lang }}"
                           wire:key="input-level_1-{{ $lang }}"
                           class="w-full border rounded p-2 border-gray-300 dark:bg-gray-900 dark:text-white dark:border-gray-700"
                           placeholder="">
                </div>

        @endforeach
    @endif
</div>

      </div>

      <!-- Level 2 -->
      <div class="border-b py-4">
        <label class="block text-gray-700 font-medium mb-1 dark:text-white">Level 2 (EN)</label>
        <input type="text" wire:model="level2" class="w-full border rounded border-gray-300 p-2 mb-2 dark:bg-gray-900 dark:text-white dark:border-gray-700"
               placeholder="{{ __('setting.Key') }}">
        @error('level2') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

        <button type="button" class="flex justify-between items-center w-full text-sm text-gray-600 font-medium py-2 dark:text-white"
                onclick="toggleAccordion('level2')">
          <span class="">Other Languages</span>
          <svg id="icon-level2" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>

        <div id="content-level2" class="hidden space-y-3 mt-2">
           @if(!empty($availableLanguages[0]))
        @foreach($availableLanguages[0] as $langIndex => $lang)
                <!-- Level Input -->
                <div>
                    <label class="block text-sm font-medium">
                        Level 2 ({{ $allLanguages[$lang] }})
                    </label>
                    <input type="text"
                           wire:model.defer="translations.level2.{{ $lang }}"
                           wire:key="input-level_2-{{ $lang }}"
                           class="w-full border rounded p-2  border-gray-300 dark:bg-gray-900 dark:text-white dark:border-gray-700"
                           placeholder="">
                </div>
        @endforeach
    @endif
      </div>
      </div>

      <!-- Level 3 -->
      <div class="border-b py-4">
        <label class="block text-gray-700 font-medium mb-1 dark:text-white">Level 3 (EN)</label>
        <input type="text" wire:model="level3" class="w-full border rounded border-gray-300 p-2 mb-2  dark:bg-gray-900 dark:text-white dark:border-gray-700"
               placeholder="{{ __('setting.Key') }}">
        @error('level3') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

        <button type="button" class="flex justify-between items-center w-full text-sm text-gray-600 font-medium py-2 dark:text-white"
                onclick="toggleAccordion('level3')">
          <span>Other Languages</span>
          <svg id="icon-level3" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>

        <div id="content-level3" class="hidden space-y-3 mt-2">
         @if(!empty($availableLanguages[0]))
        @foreach($availableLanguages[0] as $langIndex => $lang)

                <!-- Level Input -->
                <div>
                    <label class="block text-sm font-medium">
                        Level 3 ({{ $allLanguages[$lang] }})
                    </label>
                    <input type="text"
                           wire:model.defer="translations.level3.{{ $lang }}"
                           wire:key="input-level_3-{{ $lang }}"
                           class="w-full border rounded p-2  border-gray-300 dark:bg-gray-900 dark:text-white dark:border-gray-700"
                           placeholder="">
                </div>
        @endforeach
    @endif
      </div>
      </div>

      <div class="border-b py-4">
      <label class="block text-gray-700 font-medium mb-1 dark:text-white"> Tag Line 1 (English)</label>
      <input type="text" wire:model="tag_line1" class="w-full border  border-gray-300 rounded p-2 mb-2 dark:bg-gray-900 dark:text-white dark:border-gray-700"
               placeholder="Tag Line 1">

      <button type="button" class="flex justify-between items-center w-full text-sm text-gray-600 font-medium py-2 dark:text-white"
              onclick="toggleAccordion('tag1')">
        <span>Other Languages</span>
        <svg id="icon-tag" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </button>

      <div id="content-tag1" class="hidden space-y-3 mt-2">
         @if(!empty($availableLanguages[0]))
        @foreach($availableLanguages[0] as $langIndex => $lang)
             <label class="block text-sm font-medium">
                        Tag Line 1 ({{ $allLanguages[$lang] }})
                    </label>
                    <input type="text"
                           wire:model.defer="translations.level1_tagline.{{ $lang }}"
                           wire:key="input-level_1_tagline-{{ $lang }}"
                           class="w-full border rounded border-gray-300 p-2 dark:bg-gray-900 dark:text-white dark:border-gray-700"
                           placeholder="">
        @endforeach
        @endif
      </div>
    </div>

    <div class="border-b py-4">
      <label class="block text-gray-700 font-medium mb-1 dark:text-white"> Tag Line 2 (English)</label>
      <input type="text" wire:model="tag_line2" class="w-full border border-gray-300 rounded p-2 mb-2 dark:bg-gray-900 dark:text-white dark:border-gray-700"
               placeholder="Tag Line 2">

      <button type="button" class="flex justify-between items-center w-full text-sm text-gray-600 font-medium py-2 dark:text-white"
              onclick="toggleAccordion('tag2')">
        <span>Other Languages</span>
        <svg id="icon-tag" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </button>

      <div id="content-tag2" class="hidden space-y-3 mt-2">
         @if(!empty($availableLanguages[0]))
        @foreach($availableLanguages[0] as $langIndex => $lang)
             <label class="block text-sm font-medium">
                        Tag Line 2 ({{ $allLanguages[$lang] }})
                    </label>
                    <input type="text"
                           wire:model.defer="translations.level2_tagline.{{ $lang }}"
                           wire:key="input-level_2_tagline-{{ $lang }}"
                           class="w-full border rounded p-2 border-gray-300 dark:bg-gray-900 dark:text-white dark:border-gray-700"
                           placeholder="">
        @endforeach
        @endif
      </div>
    </div>

    <div class="border-b py-4">
      <label class="block text-gray-700 font-medium mb-1 dark:text-white"> Tag Line 3 (English)</label>
      <input type="text" wire:model="tag_line3" class="w-full border rounded border-gray-300 p-2 mb-2 dark:bg-gray-900 dark:text-white dark:border-gray-700"
               placeholder="Tag Line 3">

      <button type="button" class="flex justify-between items-center w-full text-sm text-gray-600 font-medium py-2 dark:text-white"
              onclick="toggleAccordion('tag3')">
        <span>Other Languages</span>
        <svg id="icon-tag" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </button>

      <div id="content-tag3" class="hidden space-y-3 mt-2">
         @if(!empty($availableLanguages[0]))
        @foreach($availableLanguages[0] as $langIndex => $lang)
             <label class="block text-sm font-medium">
                        Tag Line 3 ({{ $allLanguages[$lang] }})
                    </label>
                    <input type="text"
                           wire:model.defer="translations.level3_tagline.{{ $lang }}"
                           wire:key="input-level_3_tagline-{{ $lang }}"
                           class="w-full border rounded border-gray-300 p-2 dark:bg-gray-900 dark:text-white  dark:border-gray-700"
                           placeholder="">
        @endforeach
        @endif
      </div>
    </div>

      <div class="pt-6">
        <button type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
          {{ __('setting.Save') }}
        </button>
      </div>
    </form>
    </div>

  <script>
    function toggleAccordion(key) {
      const content = document.getElementById("content-" + key);
      const icon = document.getElementById("icon-" + key);
      content.classList.toggle("hidden");
      icon.classList.toggle("rotate-180");
    }
  </script>

  <script>
    // Tooltip behavior (same as logo-update)
    document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('[data-tooltip-id]').forEach(button => {
        const tooltipId = button.getAttribute('data-tooltip-id');
        const tooltip = document.getElementById(tooltipId);

        if (!tooltip) return;

        button.addEventListener('mouseenter', () => tooltip.classList.remove('hidden'));
        button.addEventListener('mouseleave', () => tooltip.classList.add('hidden'));
        tooltip.addEventListener('mouseenter', () => tooltip.classList.remove('hidden'));
        tooltip.addEventListener('mouseleave', () => tooltip.classList.add('hidden'));
      });
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    document.addEventListener('livewire:init', () => {
      Livewire.on('saved', () => {
        Swal.fire({
          title: "{{ __('setting.Settings Updated Successfully') }}",
          text: 'Success',
          icon: "success",
        }).then(() => {
          window.location.reload();
        });
      });
    });
  </script>

  @livewireScripts
