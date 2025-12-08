<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ColorSetting;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;
use App\Models\ActivityLog;
use Auth;
use App\Services\ThemeService;

class ColorSettings extends Component
{
    #[Title('Color Setting')]

    public $teamId, $locationId;
    public $page_layout, $categories_background_layout, $text_layout, $hover_background_layout,
        $hover_text_layout, $buttons_layout, $hover_button_layout, $mobile_page_layout,
        $mobile_header_background_color, $mobile_heading_text_color, $mobile_category_button_color,
        $mobile_button_text_color, $mobile_button_color, $mobile_font_color,
        $theme_color, $font_color, $button_color, $total_served_queue_color, $total_served_queue_text_color, $transfer_queue_color, $transfer_queue_text_color, $hold_queue_color, $hold_queue_text_color, $missed_queue_color, $missed_queue_text_color;

    public $successMessage = null; // For alert handling
    public $userAuth;

    public function mount()
    {
        $checkuser = Auth::user();
        if (!$checkuser->hasPermissionTo('Color Settings')) {
            abort(403);
        }
        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');

        $this->loadSettings();
    }

    public function loadSettings()
{
    $defaults = [
        'page_layout'                    => '#ffffff',
        'categories_background_layout'   => '#f5f5f5',
        'text_layout'                     => '#000000',
        'hover_background_layout'         => '#cccccc',
        'hover_text_layout'               => '#333333',
        'buttons_layout'                  => '#007bff',
        'hover_button_layout'             => '#0056b3',
        'mobile_page_layout'              => '#ffffff',
        'mobile_header_background_color'  => '#007bff',
        'mobile_heading_text_color'       => '#ffffff',
        'mobile_category_button_color'    => '#007bff',
        'mobile_button_text_color'        => '#ffffff',
        'mobile_button_color'             => '#007bff',
        'mobile_font_color'               => '#000000',
        'theme_color'                     => '#007bff',
        'font_color'                      => '#000000',
        'button_color'                    => '#007bff',
       'total_served_queue_color'       => '#28a745',
        'total_served_queue_text_color'  => '#ffffff',
        'transfer_queue_color'           => '#17a2b8',
        'transfer_queue_text_color'      => '#ffffff',
        'hold_queue_color'               => '#ffc107',
        'hold_queue_text_color'          => '#000000',
        'missed_queue_color'             => '#dc3545',
        'missed_queue_text_color'        => '#ffffff',
    ];

    $settings = ColorSetting::firstOrCreate(
        [
            'team_id'     => $this->teamId,
            'location_id' => $this->locationId
        ],
        $defaults
    );

    // Merge defaults with actual values from DB, so missing fields get defaults
    $this->fill(array_merge($defaults, $settings->toArray()));
}


    public function save()
    {
        $this->validate([
            'page_layout' => 'nullable|string',
            'categories_background_layout' => 'nullable|string',
            'text_layout' => 'nullable|string',
            'hover_background_layout' => 'nullable|string',
            'hover_text_layout' => 'nullable|string',
            'buttons_layout' => 'nullable|string',
            'hover_button_layout' => 'nullable|string',
            'mobile_page_layout' => 'nullable|string',
            'mobile_header_background_color' => 'nullable|string',
            'mobile_heading_text_color' => 'nullable|string',
            'mobile_category_button_color' => 'nullable|string',
            'mobile_button_text_color' => 'nullable|string',
            'mobile_button_color' => 'nullable|string',
            'mobile_font_color' => 'nullable|string',
             'theme_color' => 'nullable|string',
            'font_color' => 'nullable|string',
            'button_color' => 'nullable|string',
             'total_served_queue_color' => 'nullable|string',
            'total_served_queue_text_color' => 'nullable|string',
            'transfer_queue_color' => 'nullable|string',
            'transfer_queue_text_color' => 'nullable|string',
            'hold_queue_color' => 'nullable|string',
            'hold_queue_text_color' => 'nullable|string',
            'missed_queue_color' => 'nullable|string',
            'missed_queue_text_color' => 'nullable|string',
        ]);

        ColorSetting::updateOrCreate(
            ['team_id' => $this->teamId,'location_id'=>$this->locationId],
            $this->only([
                'page_layout',
                'categories_background_layout',
                'text_layout',
                'hover_background_layout',
                'hover_text_layout',
                'buttons_layout',
                'hover_button_layout',
                'mobile_page_layout',
                'mobile_header_background_color',
                'mobile_heading_text_color',
                'mobile_category_button_color',
                'mobile_button_text_color',
                'mobile_button_color',
                'mobile_font_color',
                 'theme_color',
            'font_color',
            'button_color',
            'total_served_queue_color',
                'total_served_queue_text_color',
                'transfer_queue_color',
                'transfer_queue_text_color',
                'hold_queue_color',
                'hold_queue_text_color',
                'missed_queue_color',
                'missed_queue_text_color',
            ])
        );

        // Save settings logic...
        session()->flash('success', 'Settings Updated Successfully.');
       ThemeService::clearCache($this->teamId, $this->locationId);
        // Set success message property
        $this->successMessage = 'Settings Updated Successfully.';

        // Dispatch event to hide the alert after timeout
        $this->dispatch('hide-alert');
    }


    private function colorFields()
    {
        return [
            'page_layout',
            'categories_background_layout',
            'text_layout',
            'hover_background_layout',
            'hover_text_layout',
            'buttons_layout',
            'hover_button_layout',
            'mobile_page_layout',
            'mobile_header_background_color',
            'mobile_heading_text_color',
            'mobile_category_button_color',
            'mobile_button_text_color',
            'mobile_button_color',
            'mobile_font_color',
            'main_theme_color',
            'main_font_color',
            'main_button_color',
            'main_button_text_color',
            'total_served_queue_color',
            'total_served_queue_text_color',
            'transfer_queue_color',
            'transfer_queue_text_color',
            'hold_queue_color',
            'hold_queue_text_color',
            'missed_queue_color',
            'missed_queue_text_color',
        ];
    }
    public function resetSuccessMessage()
    {
        $this->successMessage = null;
    }

    public function render()
    {
        return view('livewire.color-settings');
    }
}
