<div>
  @if($isEnabledlanguage)
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
  <select id="selectLocation" wire:model.live="chooseLanguage"
    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
    <option value="" disabled>{{ __('Select Language') }}</option>
    <option value="en" {{ $defaultSelected == 'en' ? 'selected' : ""}}>{{ __('English') }}</option>
    @if(!empty($languages))
    @foreach ($languages as $item)
    <option value="{{$item}}" {{ $defaultSelected == $item ? 'selected' : ""}}>{{ __($allLanguages[$item]) }}</option>
    @endforeach
    @endif
  </select>
  @endif
</div>