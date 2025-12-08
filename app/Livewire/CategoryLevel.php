<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Level;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;
use App\Models\LanguageSetting;
use App\Models\Translation;
use Auth;

class CategoryLevel extends Component
{
    #[Title('Service Level')]

    public $teamId, $location, $userAuth, $level1, $level2, $level3, $tag_line1, $tag_line2, $tag_line3, $availableLanguages, $translations = [],$acronyms_show_level = 1;

    public function mount()
    {
        $user = Auth::user();

        if (!$user || !$user->hasAnyPermission(['Service Add', 'Service Edit'])) {
            abort(403);
        }

        $this->teamId = tenant('id');
        $this->location = Session::get('selectedLocation');


        $levels = Level::where('team_id', $this->teamId)
        ->where('location_id', $this->location)
        ->whereIn('level', [1, 2, 3])
        ->get()
        ->keyBy('level');

        $this->acronyms_show_level = $levels[1]->acronyms_show_level ?? 1;
        $this->level1 = $levels[1]->name ?? null;
        $this->level2 = $levels[2]->name ?? null;
        $this->level3 = $levels[3]->name ?? null;
        $this->tag_line1= $levels[1]->tag_line ?? null;
        $this->tag_line2 = $levels[2]->tag_line ?? null;
        $this->tag_line3 = $levels[3]->tag_line ?? null;

        $this->availableLanguages = LanguageSetting::where('team_id', $this->teamId)
            ->where('location_id', $this->location)
            ->pluck('available_languages');

             if(!empty($this->availableLanguages[0])){
        // Load saved translations from DB for this team and these categories & languages
        $savedTranslations = Translation::where('team_id', $this->teamId)
            ->whereIn('language', $this->availableLanguages[0])
            ->get();

        foreach ($savedTranslations as $translation) {
            $this->translations[$translation->name][$translation->language] = $translation->value;
        }
    }
    }

    public function storeLevel()
    {

        Level::updateOrCreate(
            ['team_id' => $this->teamId, 'location_id' => $this->location, 'level' => 1],
            ['name' => $this->level1 ?? 'Level 1', 'tag_line'=>$this->tag_line1,'acronyms_show_level'=>$this->acronyms_show_level]
        );

        Level::updateOrCreate(
            ['team_id' => $this->teamId, 'location_id' => $this->location, 'level' => 2],
            ['name' => $this->level2 ?? 'Level 2','tag_line'=>$this->tag_line2,'acronyms_show_level'=>$this->acronyms_show_level]
        );

        Level::updateOrCreate(
            ['team_id' => $this->teamId, 'location_id' => $this->location, 'level' => 3],
            ['name' => $this->level3 ?? 'Level 3','tag_line'=>$this->tag_line3,'acronyms_show_level'=>$this->acronyms_show_level]
        );

         ActivityLog::storeLog($this->teamId, Auth::id(), null, null, 'Break Delete', $this->location, ActivityLog::SETTINGS, null, $this->userAuth);

         foreach ($this->translations as $key => $langs) {
        foreach ($langs as $language => $value) {
            $translation = Translation::where('team_id', $this->teamId)
                ->whereRaw('BINARY name = ?', [$key])
                ->where('language', $language)
                ->first();

            if ($translation) {
                $translation->update(['value' => $value]);
            } else {
                Translation::create([
                    'team_id' => $this->teamId,
                    'name' => $key,
                    'language' => $language,
                    'value' => $value,
                ]);
            }
        }
    }

        $this->dispatch('saved');

    }
    public function render()
    {
        return view('livewire.category-level');
    }
}
