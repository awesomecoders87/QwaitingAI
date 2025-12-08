<?php
namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Filament\Notifications\Notification;
use App\Models\User;
use App\Models\SiteDetail;
use App\Models\Domain;
use Livewire\Attributes\Title;

class ProfileForm extends Component
{
    #[Title('Profile')]

    public User $user;
    public $location;

    #[Validate('required|string|max:255')]
    public $name;

    #[Validate('required|email|max:255|regex:/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/')]
    public $email;

    #[Validate('required|string|min:8|max:15|regex:/^\+?[1-9]\d*$/')]
    public $phone;

    #[Validate('required|string|max:100')]
    public $address;

    #[Validate('required|string')]
    public $language = 'eng';

    #[Validate('required|string')]
    public $country;

    // #[Validate('required|string')]
    // public $timezone;

    #[Validate('required|integer|min:1|max:100')]
    public $sms_reminder_queue = 1;

    #[Validate('required|string')]
    public $date_format;

    #[Validate('required|array')]
    public $locations = [];

    public $listTimeZones = [];
    public $enable_location_page_option= [];
    public $enable_location_page;
    public $enable_active_users_list;

    public function mount()
    {
        $checkuser = Auth::user();
        if (!$checkuser->hasPermissionTo('Profile Setting')) {
            abort(403);
        }

        $this->location = Session::get('selectedLocation');
        $this->user = Auth::user();
        $domain = Domain::where('team_id',$this->user->team_id)->first();
        $sitedetail = SiteDetail::where('team_id',$this->user->team_id)->where('location_id',$this->location)->select('id','enable_active_users_list')->first();
        $this->enable_location_page_option = SiteDetail::getYesNo();
        // $this->listTimeZones = SiteDetail::getTimeZone();

        $this->fill([
            'name' => $this->user->name ?? '',
            'email' => $this->user->email ?? '',
            'phone' => $this->user->phone ?? '',
            'address' => $this->user->address ?? '',
            // 'timezone' => $this->user->timezone ?? '',
            'language' => $this->user->language ?? '',
            'country' => $this->user->country ?? '',
            'sms_reminder_queue' => $this->user->sms_reminder_queue ?? 1,
            'locations' => $this->user->locations ?? [],
            'date_format' => $this->user->date_format ?? '',
            'enable_location_page' => $domain->enable_location_page ?? 0,
            'enable_active_users_list' => $sitedetail->enable_active_users_list ?? 0,
        ]);
    }

    public function updateProfile()
    {

        $this->validate();

        $this->user->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'language' => $this->language,
            'country' => $this->country,
            // 'timezone' => $this->timezone ?? '',
            'sms_reminder_queue' => $this->sms_reminder_queue,
            'date_format' => $this->date_format,
            'locations' => $this->locations,
        ]);

        Domain::where('team_id', $this->user->team_id)->update([
            'enable_location_page'=>$this->enable_location_page ?? 1,
        ]);
        SiteDetail::where('team_id', $this->user->team_id)->where('location_id',$this->location)->update([
            'enable_active_users_list'=>$this->enable_active_users_list ?? 0,
        ]);

          // ✅ UPDATE ALL USERS OF SAME TEAM + SAME LOCATION
        User::where('team_id', $this->user->team_id)
            ->whereNotNull('locations')
            ->where('locations', '!=', '')
            ->whereRaw("JSON_VALID(locations)")
            ->where(function ($q) {
                $q->whereJsonContains('locations', (string) $this->location)
                ->orWhereJsonContains('locations', (int) $this->location);
            })
            ->update([
                'date_format' => $this->date_format
            ]);

        $this->dispatch('profileUpdated');

    }

    public function render()
    {
        return view('livewire.profile-form', [
            'languages' => User::getLanguages(),
            'countries' => User::getCountryCodes(),
            'dateFormats' => User::showDateFormat(),
            'locationsList' => User::getLocations(),
        ]);
    }
}
