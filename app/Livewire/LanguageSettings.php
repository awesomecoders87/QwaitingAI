<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\LanguageSetting;
use Illuminate\Support\Facades\Config;
use App\Models\Category;
use App\Models\Translation;
use App\Models\FormField;


#[Title('Language Setting')]
class LanguageSettings extends Component
{
    public $locationId;
    public $teamId;
    public $activeTab = 'language';
    public $categories;
    public $availableLanguages;

    public $enabled_language_settings = false;
    public $available_languages = []; // checkbox array
    public $default_language = 'en';  // selected option
    public $translations = [];
    public $formInputs;
    public $formSelect;

    public function mount()
    {
        $this->locationId = Session::get('selectedLocation');
        $this->teamId = tenant('id');

        $setting = LanguageSetting::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->first();

        $this->categories = Category::where('team_id', $this->teamId)->whereJsonContains('category_locations', $this->locationId)->pluck('name');

        $this->formInputs = FormField::where('team_id', $this->teamId)->where('location_id', $this->locationId)->pluck('label');

        $this->availableLanguages = LanguageSetting::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->pluck('available_languages');

        if (!empty($this->availableLanguages[0])) {
            // Load saved translations from DB for this team and these categories & languages
            $savedTranslations = Translation::where('team_id', $this->teamId)
                ->whereIn('language', $this->availableLanguages[0])
                ->get();

            foreach ($savedTranslations as $translation) {
                $this->translations[$translation->name][$translation->language] = $translation->value;
            }


            foreach ($this->categories as $category) {
                foreach ($this->availableLanguages[0] as $lang) {
                    if (!isset($this->translations[$category][$lang])) {
                        $this->translations[$category][$lang] = '';
                    }
                    if (!isset($this->translations[$category . '_other_name'][$lang])) {
                        $this->translations[$category . '_other_name'][$lang] = '';
                    }
                    if (!isset($this->translations[$category . '_description'][$lang])) {
                        $this->translations[$category . '_description'][$lang] = '';
                    }
                    if (!isset($this->translations[$category . '_note'][$lang])) {
                        $this->translations[$category . '_note'][$lang] = '';
                    }
                }
            }

            foreach ($this->formInputs as $formInput) {
                foreach ($this->availableLanguages[0] as $lang) {
                    if (!isset($this->translations[$formInput][$lang])) {
                        $this->translations[$formInput][$lang] = '';
                    }
                }
            }

            $formSelectOptions = FormField::where('team_id', $this->teamId)
                ->where('location_id', $this->locationId)
                ->where('type', 'Select')
                ->pluck('options')
                ->toArray(); // already array of arrays/strings

            foreach ($formSelectOptions as $options) {
                if (is_array($options)) {
                    foreach ($options as $option) {
                        $this->formSelect[] = $option;

                        foreach ($this->availableLanguages[0] as $lang) {
                            if (!isset($this->translations[$option][$lang])) {
                                $this->translations[$option][$lang] = '';
                            }
                        }
                    }
                }
            }
        }

        if ($setting) {
            $this->enabled_language_settings = $setting->enabled_language_settings;
            $this->available_languages = $setting->available_languages ?? [];
            $this->default_language = $setting->default_language;
        }

    }

    public function updatedAvailableLanguages()
    {
        if (!in_array($this->default_language, $this->available_languages ?? [])) {
            $this->default_language = '';
        }
    }

    public function save()
    {
        $this->validate([
            'default_language' => 'required|string',
        ]);

        LanguageSetting::updateOrCreate(
            ['team_id' => $this->teamId, 'location_id' => $this->locationId],
            [
                'enabled_language_settings' => $this->enabled_language_settings,
                'available_languages' => $this->available_languages,
                'default_language' => $this->default_language ?? 'en',
            ]
        );
        $this->dispatch('updated');
    }


    public function saveTranslation()
{
    foreach ($this->translations as $key => $langs) {
        foreach ($langs as $language => $value) {

            // Ensure $value is always string
            if (is_array($value)) {
                $value = json_encode($value); // or implode(',', $value);
            }

            $translation = Translation::where('team_id', $this->teamId)
                ->whereRaw('BINARY name = ?', [$key])
                ->where('language', $language)
                ->first();

            if ($translation) {
                $translation->update(['value' => $value]);
            } else {
                Translation::create([
                    'team_id'  => $this->teamId,
                    'name'     => $key,
                    'language' => $language,
                    'value'    => $value,
                ]);
            }
        }
    }
    $this->dispatch('updated');
}
    public function render()
    {
        return view('livewire.language-settings');
    }
}
