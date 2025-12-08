<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\VirtualQueueSetting;
use App\Models\VirtualQueue;
use App\Models\Category;
use App\Models\Level;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.custom-layout')]
class PublicVirtualQueueTypeSelection extends Component
{
    public $locationId;
    public $teamId;
    public $settings;
    public $selectedType = null;
    public $selectedLanguage = 'en';
    public $customerName;
    public $customerEmail;
    public $customerPhone;
    public $selectedCategory;
    public $categories;
    public $showForm = false;

    protected $rules = [
        'customerName' => 'required|string|max:255',
        'customerEmail' => 'nullable|email',
        'customerPhone' => 'required|string|max:20',
        'selectedCategory' => 'required|exists:categories,id',
        'selectedLanguage' => 'required|string',
    ];

    public function mount()
    {
        $this->locationId = Session::get('selectedLocation');

        if(!$this->locationId){
            return redirect()->route('public.choose-locations');
        }
        $this->teamId = tenant('id');
        
        // Load virtual queue settings
        $this->settings = VirtualQueueSetting::getSettings($this->teamId, $this->locationId);
        // Load categories - get first level categories for the location
        $firstLevel = Level::getFirstRecord();
     
        
        if ($firstLevel) {
            $this->categories = Category::where('team_id', $this->teamId)
                ->where('level_id', $firstLevel->id)
                ->whereJsonContains('category_locations', (string)$this->locationId)
                ->get();
        } else {
            // Fallback: get categories without level filter
            $this->categories = Category::where('team_id', $this->teamId)
                ->whereJsonContains('category_locations', (string)$this->locationId)
                ->whereNull('parent_id')
                ->get();
        }

        if (!$this->settings->isVirtualQueueEnabled()) {
            session()->flash('error', 'Virtual queue is not enabled.');
            return redirect()->route('public.virtual-queue-type-selection');
        }
        
        // Auto-select AI Agent and show form directly (skip type selection)
        if ($this->settings->isAIAgentEnabled()) {
            $this->selectedType = 'ai_agent';
            $this->showForm = true;
        }
    }

    public function selectType($type)
    {
        if ($type === 'ai_agent' && !$this->settings->isAIAgentEnabled()) {
            session()->flash('error', 'AI Agent is not enabled for this location.');
            return;
        }

        if ($type === 'human_agent' && !$this->settings->isHumanAgentEnabled()) {
            session()->flash('error', 'Human Agent is not enabled for this location.');
            return;
        }

        $this->selectedType = $type;
        $this->showForm = true;
    }

    public function createVirtualQueue()
    {
        $this->validate();

        // Generate ticket number
        $ticketNumber = 'VQ' . now()->format('ymd') . strtoupper(substr(md5(uniqid()), 0, 4));

        // Create virtual queue entry
        $virtualQueue = VirtualQueue::create([
            'team_id' => $this->teamId,
            'location_id' => $this->locationId,
            'ticket_number' => $ticketNumber,
            'queue_type' => $this->selectedType,
            'selected_language' => $this->selectedLanguage,
            'customer_name' => $this->customerName,
            'customer_email' => $this->customerEmail,
            'customer_phone' => $this->customerPhone,
            'status' => 'pending',
        ]);

        // Redirect to appropriate page
        if ($this->selectedType === 'ai_agent') {
            return redirect()->route('public.ai-agent-call', ['virtualQueueId' => base64_encode($virtualQueue->id)]);
        } else {
            return redirect()->route('public.human-agent-waiting', ['virtualQueueId' => base64_encode($virtualQueue->id)]);
        }
    }

    public function render()
    {
        return view('livewire.public-virtual-queue-type-selection');
    }
}
