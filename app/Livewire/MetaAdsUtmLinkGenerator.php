<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MetaAdsAndCampaignsLink;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class MetaAdsUtmLinkGenerator extends Component
{
    use WithPagination;

    #[Title('Meta Ads and Campaigns')]

    public $teamId;
    public $locationId;
    public $link_type;
    public $base_url;
    public $source;
    public $medium;
    public $campaign;
    public $generated_link;
    public $startDate = null;
    public $endDate = null;

    // Remove this public property:
    // public $listing;

    public function mount()
    {
        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');
    }

    public function autoFillBaseUrl()
    {
        if ($this->link_type == 'Booking') {
            $this->base_url = url('book-appointment');
        } else {
            $this->base_url = url('queue');
        }
    }

    public function generateLink()
    {
        $this->validate([
            'link_type' => 'required',
            'base_url' => 'required|url',
            'source' => 'required|string|max:255',
            'medium' => 'required|string|max:255',
            'campaign' => 'required|string|max:255',
        ]);

        $this->generated_link = url($this->base_url . '?utm_source=' . $this->source . '&utm_medium=' . $this->medium . '&utm_campaign=' . $this->campaign);

        MetaAdsAndCampaignsLink::create([
            'team_id' => $this->teamId,
            'location_id' => $this->locationId,
            'type' => $this->link_type,
            'source' => $this->source,
            'medium' => $this->medium,
            'campaign' => $this->campaign,
            'generated_link' => $this->generated_link,
        ]);

        // Instead of assigning to $this->listing, just refresh data by emitting event or resetting pagination
        $this->resetPage(); // This resets pagination to page 1, Livewire will re-render and fetch fresh data
    }

    public function resetDateFilters()
{
    $this->startDate = null;
    $this->endDate = null;
}

    public function render()
    {
       $listing = MetaAdsAndCampaignsLink::select('*')
        ->selectRaw('
            (SELECT COUNT(*) FROM bookings WHERE campaign_id = meta_ads_and_campaigns_links.id) as total_bookings,
            (SELECT COUNT(*) FROM queues_storage WHERE campaign_id = meta_ads_and_campaigns_links.id) as total_walk_ins,
            (SELECT COUNT(*) FROM queues_storage WHERE campaign_id = meta_ads_and_campaigns_links.id AND status = "Close") as served,
            (SELECT COUNT(*) FROM bookings WHERE campaign_id = meta_ads_and_campaigns_links.id AND status = "Cancelled") + 
            (SELECT COUNT(*) FROM queues_storage WHERE campaign_id = meta_ads_and_campaigns_links.id AND status IN ("Cancelled", "Skip")) as no_show,
            (SELECT COUNT(*) FROM bookings WHERE campaign_id = meta_ads_and_campaigns_links.id) + 
            (SELECT COUNT(*) FROM queues_storage WHERE campaign_id = meta_ads_and_campaigns_links.id) as total_visits
        ')
        ->where('team_id', $this->teamId)
        ->where('location_id', $this->locationId)
        ->when($this->startDate, fn($query) =>
        $query->whereDate('created_at', '>=', $this->startDate)
        )
        ->when($this->endDate, fn($query) =>
            $query->whereDate('created_at', '<=', $this->endDate)
        )
        ->orderBy('id', 'desc')
        ->paginate(5);

        return view('livewire.meta-ads-utm-link-generator', compact('listing'));
    }


}

