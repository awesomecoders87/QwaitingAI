<?php

namespace App\Livewire\Superadmin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AiActivityLog;

class AiActivityLogComponent extends Component
{
    use WithPagination;

    public $search = '';
    public $activeTab = 'booking_chatbot';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function render()
    {
        $logs = AiActivityLog::with(['team'])
            ->when($this->search, function ($query) {
                $query->where('ai_activity_logs.chatbot_name', 'like', '%' . $this->search . '%');
            })
            ->when($this->activeTab, function ($query) {
                if ($this->activeTab == 'queue_analytics') {
                    $query->where('type', 'queue_analytics');
                } elseif ($this->activeTab == 'booking_chatbot') {
                    $query->where('type', 'booking_chatbot');
                } else {
                    $query->where('type', 'general');
                }
            })
            ->latest()
            ->paginate(15);

        return view('livewire.superadmin.ai-activity-log-component', [
            'logs' => $logs
        ])->extends('superadmin.components.layout')
          ->section('content');
    }
}
