<?php

namespace App\Livewire;

use App\Models\ScreenTemplate;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Rule;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;

class EditScreenTemplateSettings extends Component
{
    use WithFileUploads;
    #[Title('Edit Screen Template Setting')]

    public ScreenTemplate $screenTemplate;

    // Form fields
    public $token_title;
    public $counter_title;
    public $missed_queue;
    public $hold_queue;
    public $font_size;
    public $show_queue_number;
    public $font_color;
    public $background_color;
    public $current_serving_fontcolor;
    public $hold_queue_bg;
    public $is_datetime_show;
    public $datetime_font_color;
    public $datetime_bg_color;
    public $datetime_position;
    public $is_powered_by;
    public $powered_image;
    public $is_header_show;
    public $is_logo;
    public $is_missed_queue;
    public $is_hold_queue;
    public $json = [];
    public $json_data = [];
    public $newPoweredImage;
    public $newImages = [];
    public $teamId;
    public $locationId;
    public $isdisclaimer;
    public $is_name_on_display_screen_show = 1; //Newq Changes
    public $is_skip_call_show;
    public $is_waiting_call_show;
    public $is_skip_closed_call_from_display_screen;
    public $display_screen_disclaimer;
    public $waiting_queue;
    public $waiting_queue_bg;
    public $missed_queue_bg;
    public $disclaimer_title;
    public $display_behavior;
	// ⭐ NEW FIELDS ⭐
	public $is_location = 0;
	public $location_fontcolor;
	public $location_bg;


    public function mount($record)
    {
        $this->screenTemplate = ScreenTemplate::findOrFail($record);
        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');
        if($this->screenTemplate->team_id != $this->teamId && $this->screenTemplate->location_id != $this->locationId){
            abort(404);
        }

        // Fill form with existing data
        $this->token_title = $this->screenTemplate->token_title;
        $this->counter_title = $this->screenTemplate->counter_title;
        $this->missed_queue = $this->screenTemplate->missed_queue;
        $this->hold_queue = $this->screenTemplate->hold_queue;
        $this->font_size = $this->screenTemplate->font_size;
        $this->show_queue_number = $this->screenTemplate->show_queue_number;
        $this->font_color = $this->screenTemplate->font_color;
        $this->background_color = $this->screenTemplate->background_color;
        $this->current_serving_fontcolor = $this->screenTemplate->current_serving_fontcolor;
        $this->waiting_queue_bg = $this->screenTemplate->waiting_queue_bg;
        $this->missed_queue_bg = $this->screenTemplate->missed_queue_bg;
        $this->hold_queue_bg = $this->screenTemplate->hold_queue_bg;
        $this->is_datetime_show = (bool)$this->screenTemplate->is_datetime_show;
        $this->datetime_font_color = $this->screenTemplate->datetime_font_color;
        $this->datetime_bg_color = $this->screenTemplate->datetime_bg_color;
        $this->datetime_position = $this->screenTemplate->datetime_position;
        $this->is_powered_by = (bool)$this->screenTemplate->is_powered_by ?? ScreenTemplate::STATUS_INACTIVE;
        $this->powered_image = $this->screenTemplate->powered_image;
        $this->is_header_show = (bool)$this->screenTemplate->is_header_show;
        $this->is_logo = (bool)$this->screenTemplate->is_logo;
        $this->is_missed_queue = (bool)$this->screenTemplate->is_missed_queue;
        $this->is_hold_queue = (bool)$this->screenTemplate->is_hold_queue;
        $this->isdisclaimer = (bool)$this->screenTemplate->is_disclaimer;
        //$this->is_name_on_display_screen_show = (bool)$this->screenTemplate->is_name_on_display_screen_show;
		 // Set the dropdown value from DB
		$this->is_name_on_display_screen_show = $this->screenTemplate->is_name_on_display_screen_show;
        $this->is_skip_call_show = (bool)$this->screenTemplate->is_skip_call_show;
        $this->is_waiting_call_show = (bool)$this->screenTemplate->is_waiting_call_show;
        $this->is_skip_closed_call_from_display_screen = (bool)$this->screenTemplate->is_skip_closed_call_from_display_screen;
        $this->display_screen_disclaimer = $this->screenTemplate->display_screen_disclaimer;
        $this->waiting_queue = $this->screenTemplate->waiting_queue;
        $this->disclaimer_title = $this->screenTemplate->disclaimer_title;
        $this->display_behavior = $this->screenTemplate->display_behavior ?? 1;
		 // ⭐ NEW FIELDS ⭐
		$this->is_location = (bool)$this->screenTemplate->is_location ?? 0;
		$this->location_fontcolor = $this->screenTemplate->location_fontcolor;
		$this->location_bg = $this->screenTemplate->location_bg;

        // Process JSON fields
        $this->json = $this->screenTemplate->json ? json_decode($this->screenTemplate->json, true) : [];
        $this->json_data = $this->screenTemplate->json_data ? json_decode($this->screenTemplate->json_data, true) : [];
		
    }

    public function save()
    {
        // Validate form data
        $this->validate([
            'token_title' => 'required|max:50',
            'counter_title' => 'required|max:50',
            'missed_queue' => 'required|max:50',
            'hold_queue' => 'required|max:50',
            'waiting_queue' => 'required|max:50',
            'disclaimer_title' => 'nullable|max:50',
            'font_size' => 'required',
            'show_queue_number' => 'required',
            'font_color' => 'required',
            'background_color' => 'required',
            'current_serving_fontcolor' => 'required',
            'hold_queue_bg' => 'required',
            'newPoweredImage' => $this->is_powered_by == ScreenTemplate::STATUS_ACTIVE && !$this->powered_image ? 'required|image|max:3072' : 'nullable|image|max:3072',
            'newImages.*' => 'nullable|image|max:3072',
			'is_location' => 'nullable|boolean',
			'location_fontcolor' => 'nullable|string|max:20',
			'location_bg' => 'nullable|string|max:20',
        ]);

        // Handle image uploads
        if ($this->newPoweredImage) {
            $poweredImagePath = $this->newPoweredImage->store('powered_image', 'public');
            $this->powered_image = $poweredImagePath;
        }

        if (!empty($this->newImages)) {
            $uploadedImages = [];
            foreach ($this->newImages as $image) {
                $path = $image->store('template', 'public');
                $uploadedImages[] = $path;
            }

            // Merge with existing images
            $this->json_data = array_merge($this->json_data, $uploadedImages);
        }

        // Prepare data for update
        $data = [
            'json_data' => !empty($this->json_data) ? json_encode($this->json_data) : null,
            'json' => !empty($this->json) ? json_encode($this->json) : null,
            'background_color' => $this->background_color,
            'font_color' => $this->font_color,
            'is_logo' => (bool)$this->is_logo,
            'is_image' => !empty($this->json_data) ? 1 : 0,
            'is_video' => !empty($this->json) ? 1 : 0,
            'show_queue_number' => $this->show_queue_number,
            'font_size' => $this->font_size,
            'missed_queue' => $this->missed_queue,
            'hold_queue' => $this->hold_queue,
            'counter_title' => $this->counter_title,
            'token_title' => $this->token_title,
            'is_header_show' => (bool)$this->is_header_show,
            'is_hold_queue' => (bool)$this->is_hold_queue,
            'is_missed_queue' => (bool)$this->is_missed_queue,
            'waiting_queue_bg' => $this->waiting_queue_bg,
            'missed_queue_bg' => $this->missed_queue_bg,
            'hold_queue_bg' => $this->hold_queue_bg,
            'current_serving_fontcolor' => $this->current_serving_fontcolor,
            'is_datetime_show' => (bool)$this->is_datetime_show ?? null,
            'datetime_font_color' => $this->datetime_font_color ?? ScreenTemplate::FONT_DEFAULT_COLOR,
            'datetime_bg_color' => $this->datetime_bg_color ?? ScreenTemplate::DATETIME_BG_COLOR,
            'datetime_position' => $this->datetime_position ?? ScreenTemplate::POSITION_LEFT,
            'is_powered_by' => (bool)$this->is_powered_by ?? ScreenTemplate::STATUS_INACTIVE,
            'is_disclaimer' => (bool)$this->isdisclaimer ?? ScreenTemplate::STATUS_INACTIVE,
            'powered_image' => $this->powered_image ?? null,
            'is_name_on_display_screen_show' => (int)$this->is_name_on_display_screen_show,//New Cahnges
            'is_skip_call_show' => (bool)$this->is_skip_call_show,
            'is_waiting_call_show' => (bool)$this->is_waiting_call_show,
            'is_skip_closed_call_from_display_screen' => (bool)$this->is_skip_closed_call_from_display_screen,
            'display_screen_disclaimer' => $this->display_screen_disclaimer,
            'waiting_queue' => $this->waiting_queue,
            'disclaimer_title' => $this->disclaimer_title,
            'display_behavior' => $this->display_behavior,
			'is_location' => (bool)$this->is_location ?? 0,
			'location_fontcolor' => $this->location_fontcolor,
			'location_bg' => $this->location_bg,
        ];

        // Update the record
        $this->screenTemplate->update($data);

        // Show notification
        session()->flash('success', __('text.updated successfully'));

        // Redirect to listing page or refresh
        return redirect()->route('tenant.screen-templates');
    }

    // Add video to the JSON array
    public function addVideo()
    {
        $this->json[] = ['yt_vid' => ''];
    }

    // Remove video at specific index
    public function removeVideo($index)
    {
        unset($this->json[$index]);
        $this->json = array_values($this->json);
    }

    // Remove image at specific index
    public function removeImage($index)
    {
        unset($this->json_data[$index]);
        $this->json_data = array_values($this->json_data);
    }

    // Reorder images
    public function reorderImages($orderedList)
    {
        $reordered = [];
        foreach ($orderedList as $item) {
            $reordered[] = $this->json_data[$item['value']];
        }
        $this->json_data = $reordered;
    }

    public function render()
    {
        return view('livewire.edit-screen-template-settings', [
            'fontSizes' => ScreenTemplate::getFontSize(),
            'queueNumbers' => ScreenTemplate::showQueueNumber(),
            'positions' => ScreenTemplate::getPosition(),
            'showVideoSection' => in_array($this->screenTemplate->template, [
                ScreenTemplate::TEMPLATE_KEY_VIDEO,
                ScreenTemplate::TEMPLATE_KEY_IMAGESVIDEO
            ]),
            'showImageSection' => in_array($this->screenTemplate->template, [
                ScreenTemplate::TEMPLATE_KEY_IMAGES,
                ScreenTemplate::TEMPLATE_KEY_IMAGESVIDEO
            ]),
        ]);
    }
}
