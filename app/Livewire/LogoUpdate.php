<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use App\Models\SiteDetail;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;

class LogoUpdate extends Component
{
    use WithFileUploads;

    #[Title('Logo Update')]

    // Existing paths for display
    public $existingBusinessLogo;
    public $existingMobileLogo;
    public $existingLogoPrintTicket;
    public $existingLogoFooterTicketScreen;

    // Temp uploads (Livewire)
    public $business_logo;
    public $mobile_logo;
    public $logo_print_ticket;
    public $logo_footer_ticket_screen;

    public $teamId;
    public $locationId;
    public $siteDetail;
    public $selectedId;

    /** Allowed DB columns / Livewire props */
    protected array $allowedKeys = [
        'business_logo',
        'mobile_logo',
        'logo_print_ticket',
        'logo_footer_ticket_screen',
    ];

    /** Central rules (nullable = allows page load without file) */
    protected function rules(): array
    {
        return [
            'business_logo'             => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'mobile_logo'               => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'logo_print_ticket'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'logo_footer_ticket_screen' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }

    public function mount()
    {
        $user = Auth::user();
        if (!$user || !$user->hasPermissionTo('Logo Update')) {
            abort(403);
        }

        $this->locationId = Session::get('selectedLocation');
        $this->teamId = tenant('id'); // adjust to your tenancy helper
        $this->getExistingImage();
    }

    /**
     * Live-validate when user picks a file (better UX)
     */
    public function updated($property)
    {
        if (in_array($property, $this->allowedKeys, true)) {
            $this->validateOnly($property);
        }
    }

    /**
     * Upload a specific logo type
     */
    public function uploadLogo(string $type)
    {
        // whitelist guard
        abort_unless(in_array($type, $this->allowedKeys, true), 422);

        // force required for the specific type at upload time
        $this->validate([
            $type => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            "{$type}.mimes" => 'Only JPG, JPEG, and PNG files are allowed.',
            "{$type}.image" => 'The file must be an image.',
            "{$type}.max"   => 'Maximum file size is 2MB.',
        ]);

        /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file */
        $file = $this->$type;

        // Delete old file for this type (avoid orphans)
        $existing = SiteDetail::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->value($type);


        if ($existing && Storage::disk('public')->exists($existing) && $existing != 'logo/qwaiting.png') {
            Storage::disk('public')->delete($existing);
        }

        // Organized path: logo/{team}/{location}
        $folder = "logo/{$this->teamId}/{$this->locationId}";

        $path = $file->store($folder, 'public');

        SiteDetail::updateOrCreate(
            ['team_id' => $this->teamId, 'location_id' => $this->locationId],
            [$type => $path]
        );

        // Reset temp file and refresh existing
        $this->reset($type);
        $this->getExistingImage();

        // Trigger your SweetAlert success
        $this->dispatch('updated');
    }

    public function deleteconfirmation($id)
    {
        $this->selectedId = $id;
        $this->dispatch('confirmation-delete');
    }

    #[On('confirmed-delete')]
    public function deleteLogo()
    {
        // whitelist guard
        abort_unless(in_array($this->selectedId, $this->allowedKeys, true), 422);

        $type = $this->selectedId;

        $siteDetail = SiteDetail::where([
            'team_id' => $this->teamId,
            'location_id' => $this->locationId,
        ])->first();

        if (!$siteDetail) {
            session()->flash('error', 'Logo does not exist.');
            return;
        }

        $currentImage = $siteDetail->$type;

        if ($currentImage && Storage::disk('public')->exists($currentImage)) {
            Storage::disk('public')->delete($currentImage);
            $siteDetail->update([$type => null]);
            $this->dispatch('deleted');
        } else {
            session()->flash('error', 'Logo does not exist.');
        }

        $this->getExistingImage();
    }

    public function getExistingImage()
    {
        $this->siteDetail = SiteDetail::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->first();

        $this->existingBusinessLogo           = $this->siteDetail->business_logo ?? null;
        $this->existingMobileLogo             = $this->siteDetail->mobile_logo ?? null;
        $this->existingLogoPrintTicket        = $this->siteDetail->logo_print_ticket ?? null;
        $this->existingLogoFooterTicketScreen = $this->siteDetail->logo_footer_ticket_screen ?? null;
    }

    public function render()
    {
        return view('livewire.logo-update');
    }
}
