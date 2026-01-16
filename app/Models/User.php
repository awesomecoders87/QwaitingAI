<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens, SoftDeletes;

    const ROLE_ADMIN = 'Admin';
    const ROLE_SUPER_ADMIN = 'Super Admin';
    const ROLE_STAFF = 'Staff';
    const ROLE_TEAM_LEADER = 'Team Leader';
    const STATUS_YES = 1;


    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'unique_id',
        'show_next_button',
        'counter_id',
        'assign_counters',
        'enable_desktop_notification',
        'username',
        'timezone',
        'language',
        'sms_reminder_queue',
        'team_user_id',
        'team_id',
        'locations',
        'country',
        'is_login',
        'role_id',
        'login_token',
        'date_format',
        'time_format',
        'assigned_permissions',
        'is_active',
        'microsoft_email',
        'created_by',
        'enable_hold_queue',
        'parent_id',
        'level_id',
        'priority',
        'is_admin',
        'saleforce_user_id',
        'must_change_password',
        'login_datetime',
		'is_superadmin',
        'sms_credits_balance',
    ];



    protected $dates = ['deleted_at'];
    public static function getLanguages()
    {
        return [
            'eng' => 'English',
            'zho' => 'Chinese',
            'fra' => 'French',
            'ara' => 'Arabic'
        ];
    }
    public static function getSelectLocations()
    {
        $user = auth()->user();
        $locations = [];
        return  $locations = Location::where('team_id', tenant('id'))
            ->where('status',1)
            ->pluck('location_name', 'id')
            ->toArray();
        // Check if the authenticated user has the 'admin' role
        if ($user && $user->hasRole(self::ROLE_ADMIN)) {
            // If the user is an admin, fetch all locations for the team
            $locations = Location::where('team_id', tenant('id'))
                ->where('status',1)
                ->pluck('location_name', 'id')
                ->toArray();
        } else {
            // If the user is not an admin, fetch only the locations assigned to the user and active first assigned location

            $userLocations = $user['locations'];
            if ($userLocations) {
                $locations = Location::where('team_id', tenant('id'))
                    ->where('status',1)
                    ->whereIn('id', $userLocations)
                    ->orderByRaw('FIELD(id, ' . implode(',', $userLocations) . ')')
                    ->pluck('location_name', 'id')
                    ->toArray();
            }
        }

        return $locations;
    }

    public static function getLocations()
    {
        $user = auth()->user();
        $locations = [];

        // If the user is an admin, fetch all locations for the team
        $locations = Location::where('team_id', tenant('id'))
            ->where('status',1)
            ->pluck('location_name', 'id')
            ->toArray();


        return $locations;
    }
    protected $hidden = [
        'password',
        'remember_token'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
        'assigned_permissions' => 'array'
        
    ];

    public function setLocationsAttribute($value)
    {
        $this->attributes['locations'] = json_encode($value);
    }
    public function setAssignCountersAttribute($value)
    {
        $this->attributes['assign_counters'] = json_encode($value);
    }

    // Define an accessor to decode the JSON string when retrieving from the database
    public function getLocationsAttribute($value)
    {
        return json_decode($value, true);
    }

    public function getAssignCountersAttribute($value)
    {
        return json_decode($value, true);
    }

    public function checkrole()
    {
        return $this->belongsToMany(Role::class, 'model_has_roles', 'model_id', 'role_id');
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class);
    }



    public static function getDefaultLocation()
    {
        $user = Auth::user();
        if ($user && !empty($user->locations)) {
            $locations = $user->locations;

            // Decode the JSON string into an array only if it is a string
            if (is_string($locations)) {
                $locations = json_decode($locations, true);
            }
            foreach ($locations as $locationId) {
                $location = \App\Models\Location::find($locationId);
                if ($location && $location->status == 1) {
                    return $location->id;
                }
            }


            // Return the first location ID if it exists
            // return !empty($locations) ? $locations[0] : '';
        }

        // Default to an empty string if no locations are found
        return '';
    }


    public static function showDateFormat()
    {
        $dateFormats = [
            'Y-m-d H:i:s' => __('Y-m-d H:i:s'),
            'd-m-Y H:i:s' => __('d-m-Y H:i:s'),
            'm/d/Y H:i:s' => __('m/d/Y H:i:s'),
            'Y-m-d h:i A' => __('Y-m-d h:i A'),
            'd-m-Y h:i A' => __('d-m-Y h:i A'),
            'm/d/Y h:i A' => __('m/d/Y h:i A'),
            'Y-m-d'       => __('Y-m-d'),
            'd-m-Y'       => __('d-m-Y'),
            'm/d/Y'       => __('m/d/Y'),
            'F j, Y'      => __('F j, Y'),
            'd M, Y'      => __('d M, Y'),
            'l, F j, Y'   => __('l, F j, Y'),
        ];

        return $dateFormats;
    }

    public static function showTimeFormat()
    {
        $timeFormats = [
            'H:i:s' => __('HH:mm:ss'),
            'h:i:s A' => __('hh:mm:ss AM/PM'),
            'H:i' => __('HH:mm'),
            'h:i A' => __('hh:mm AM/PM'),
            'g:i A' => __('h:mm AM/PM'),
        ];

        return $timeFormats;
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    // public function locations() {
    //     return $this->belongsToMany(Location::class);
    // }

    public function categories(): BelongsToMany
    {
        // return $this->belongsToMany(Category::class);
      return $this->belongsToMany(Category::class, 'category_user');
    }
    // public function team(): BelongsTo
    // {
    //     return $this->belongsTo(Team::class);
    // }
    public function customOwner()
    {
        return $this->belongsTo(Tenant::class, 'team_id');
    }

    public function queues()
    {
        return $this->hasMany(QueueStorage::class, 'served_by');
    }
    public function closedBy()
    {
        return $this->hasMany(QueueStorage::class, 'closed_by');
    }

    public function counter(): BelongsTo
    {
        return $this->belongsTo(Counter::class);
    }
    public function canAccessTenant(Model $tenant): bool
    {
        // Log::debug('$this->teams'. $this->teams);

        return $this->teams->contains($tenant);
    }



    // public function isAdmin()
    // {
    //     return $this->email === 'admin@gmail.com';
    // }
    public function isAdmin(): bool
    {
         return $this->hasRole(self::ROLE_ADMIN);
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'id', 'country');
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(self::ROLE_SUPER_ADMIN);
    }

    public static function  getCountryCodes()
    {
        return array('Afghanistan (+93)', 'Albania (+355)', 'Algeria (+213)', 'American Samoa (+1-684)', 'Andorra (+376)', 'Angola (+244)', 'Anguilla (+1-264)', 'Antigua and Barbuda (+1-268)', 'Argentina (+54)', 'Armenia (+374)', 'Aruba (+297)', 'Australia (+61)', 'Austria (+43)', 'Azerbaijan (+994)', 'Bahamas (+1-242)', 'Bahrain (+973)', 'Bangladesh (+880)', 'Barbados (+1-246)', 'Belarus (+375)', 'Belgium (+32)', 'Belize (+501)', 'Benin (+229)', 'Bermuda (+1-441)', 'Bhutan (+975)', 'Bolivia (+591)', 'Bosnia and Herzegovina (+387)', 'Botswana (+267)', 'Brazil (+55)', 'British Indian Ocean Territory (+246)', 'British Virgin Islands (+1-284)', 'Brunei (+673)', 'Bulgaria (+359)', 'Burkina Faso (+226)', 'Burundi (+257)', 'Cambodia (+855)', 'Cameroon (+237)', 'Canada (+1)', 'Cape Verde (+238)', 'Cayman Islands (+1-345)', 'Central African Republic (+236)', 'Chad (+235)', 'Chile (+56)', 'China (+86)', 'Christmas Island (+61)', 'Cocos Islands (+61)', 'Colombia (+57)', 'Comoros (+269)', 'Cook Islands (+682)', 'Costa Rica (+506)', 'Croatia (+385)', 'Cuba (+53)', 'Cyprus (+357)', 'Czech Republic (+420)', 'Democratic Republic of the Congo (+243)', 'Denmark (+45)', 'Djibouti (+253)', 'Dominica (+1-767)', 'Dominican Republic (+1-809)', 'Dominican Republic (+1-829)', 'East Timor (+670)', 'Ecuador (+593)', 'Egypt (+20)', 'El Salvador (+503)', 'Equatorial Guinea (+240)', 'Eritrea (+291)', 'Estonia (+372)', 'Ethiopia (+251)', 'Falkland Islands (+500)', 'Faroe Islands (+298)', 'Fiji (+679)', 'Finland (+358)', 'France (+33)', 'French Polynesia (+689)', 'Gabon (+241)', 'Gambia (+220)', 'Georgia (+995)', 'Germany (+49)', 'Ghana (+233)', 'Gibraltar (+350)', 'Greece (+30)', 'Greenland (+299)', 'Grenada (+1-473)', 'Guam (+1-671)', 'Guatemala (+502)', 'Guinea (+224)', 'Guinea (+245)', 'Guyana (+592)', 'Haiti (+509)', 'Honduras (+504)', 'Hong Kong (+852)', 'Hungary (+36)', 'Iceland (+354)', 'India (+91)', 'Indonesia (+62)', 'Iran (+98)', 'Iraq (+964)', 'Ireland (+353)', 'Isle of Man (+44-1624)', 'Israel (+972)', 'Italy (+39)', 'Ivory Coast (+225)', 'Jamaica (+1-876)', 'Japan (+81)', 'Jersey (+44-1534)', 'Jordan (+962)', 'Kazakhstan (+7)', 'Kenya (+254)', 'Kiribati (+686)', 'Kuwait (+965)', 'Kyrgyzstan (+996)', 'Laos (+856)', 'Latvia (+371)', 'Lebanon (+961)', 'Lesotho (+266)', 'Liberia (+231)', 'Libya (+218)', 'Liechtenstein (+423)', 'Lithuania (+370)', 'Luxembourg (+352)', 'Macao (+853)', 'Macedonia (+389)', 'Madagascar (+261)', 'Malawi (+265)', 'Malaysia (+60)', 'Maldives (+960)', 'Mali (+223)', 'Malta (+356)', 'Marshall Islands (+692)', 'Martinique (+596)', 'Mauritania (+222)', 'Mauritius (+230)', 'Mayotte (+262)', 'Mexico (+52)', 'Micronesia (+691)', 'Moldova (+373)', 'Monaco (+377)', 'Mongolia (+976)', 'Montenegro (+382)', 'Montserrat (+1-664)', 'Morocco (+212)', 'Mozambique (+258)', 'Myanmar (+95)', 'Namibia (+264)', 'Nauru (+674)', 'Nepal (+977)', 'Netherlands (+31)', 'Netherlands Antilles (+599)', 'New Caledonia (+687)', 'New Zealand (+64)', 'Nicaragua (+505)', 'Niger (+227)', 'Nigeria (+234)', 'Niue (+683)', 'North Korea (+850)', 'Northern Mariana Islands (+1-670)', 'Norway (+47)', 'Oman (+968)', 'Pakistan (+92)', 'Palau (+680)', 'Panama (+507)', 'Papua New Guinea (+675)', 'Paraguay (+595)', 'Peru (+51)', 'Philippines (+63)', 'Pitcairn (+870)', 'Poland (+48)', 'Portugal (+351)', 'Puerto Rico (+1-787) (+1-787)', 'Puerto Rico (+1-939) (+1-939)', 'Qatar (+974)', 'Republic of the Congo (+242)', 'Romania (+40)', 'Russia (+7)', 'Rwanda (+250)', 'Saint Barthelemy (+590)', 'Saint Helena (+290)', 'Saint Kitts and Nevis (+1-869)', 'Saint Lucia (+1-758)', 'Saint Martin (+590)', 'Saint Pierre and Miquelon (+508)', 'Saint Vincent and the Grenadines (+1-784)', 'Samoa (+685)', 'San Marino (+378)', 'Sao Tome and Principe (+239)', 'Saudi Arabia (+966)', 'Senegal (+221)', 'Serbia (+381)', 'Seychelles (+248)', 'Sierra Leone (+232)', 'Singapore (+65)', 'Slovakia (+421)', 'Slovenia (+386)', 'Solomon Islands (+677)', 'Somalia (+252)', 'South Africa (+27)', 'South Korea (+82)', 'Spain (+34)', 'Sri Lanka (+94)', 'Sudan (+249)', 'Suriname (+597)', 'Svalbard and Jan Mayen (+47)', 'Swaziland (+268)', 'Sweden (+46)', 'Switzerland (+41)', 'Syria (+963)', 'Taiwan (+886)', 'Tajikistan (+992)', 'Tanzania (+255)', 'Thailand (+66)', 'Togo (+228)', 'Tokelau (+690)', 'Tonga (+676)', 'Trinidad and Tobago (+1-868)', 'Tunisia (+216)', 'Turkey (+90)', 'Turkmenistan (+993)', 'Turks and Caicos Islands (+1-649)', 'Tuvalu (+688)', 'US. Virgin Islands (+1-340)', 'Uganda (+256)', 'Ukraine (+380)', 'United Arab Emirates (+971)', 'United Kingdom (+44)', 'United States (+1)', 'Uruguay (+598)', 'Uzbekistan (+998)', 'Vanuatu (+678)', 'Vatican (+379)', 'Venezuela (+58)', 'Vietnam (+84)', 'Wallis and Futuna (+681)', 'Western Sahara (+212)', 'Yemen (+967)', 'Zambia (+260)', 'Zimbabwe (+263)');
    }


    public static function getCountryCode($key = null)
    {
        if (!empty($key))
            return self::getCountryCodes()[$key];

        return self::getCountryCodes()[92];
    }

    public static function viewName($userId)
    {
        return self::find($userId)?->name;
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // public static function adminUser($teamId)
    // {
    //     return self::whereHas('roles', function ($query) {
    //         $query->where('name', self::ROLE_ADMIN);
    //     })->whereHas('teams', function ($query) use ($teamId) {
    //         $query->where('team_id', $teamId);
    //     })->first()?->address;
    // }

    // public static function adminUserDetail($teamId)
    // {
    //     return self::whereHas('roles', function ($query) {
    //         $query->where('name', self::ROLE_ADMIN);
    //     })->whereHas('teams', function ($query) use ($teamId) {
    //         $query->where('team_id', $teamId);
    //     })->first();
    // }

    public static function filterSettingExcel($selectedLocation, $filters)
    {
        $data = [];
        $currentDate = Carbon::now()->format('d-m-Y');
        $data['Branch Name'] = Location::locationName($selectedLocation);
        $data['Created From']  = (!empty($filters['created_at']['created_from'])) ? Carbon::parse($filters['created_at']['created_from'])->format('d-m-Y') : $currentDate;
        $data['Created Until']  = (!empty($filters['created_at']['created_until'])) ? Carbon::parse($filters['created_at']['created_until'])->format('d-m-Y') : $currentDate;

        return $data;
    }

    public function stripeUser()
    {

        return $this->hasOne(StripeUser::class, 'user_id', 'id');
    }
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    //     protected static function booted() {
    //         static::creating(function ($model) {
    //         $model->uuid = Str::uuid()->toString();
    //         });
    //    }

    
    public function countries()
    {
        return $this->belongsTo(Country::class, 'id', 'country');
    }



public static function getRotationByDirector($teamId, $locationId)
{
    $directors = User::where('team_id', $teamId)
        ->whereJsonContains('locations', (string)$locationId)
        ->where('level_id', 1)
        ->orderBy('id')
        ->get();

    $managers = User::where('team_id', $teamId)
        ->whereJsonContains('locations', (string)$locationId)
        ->where('level_id', 2)
        ->get();

    $agents = User::where('team_id', $teamId)
        ->whereJsonContains('locations', (string)$locationId)
        ->where('level_id', 3)
        ->where('is_login', 1)
        ->get();

    $rotationByDirector = [];

    foreach ($directors as $director) {
        $directorManagers = $managers->where('parent_id', $director->id)->values();

        // build manager groups (only managers with at least one logged-in agent)
        $managerGroups = [];
        foreach ($directorManagers as $manager) {
            $managerAgents = $agents->where('parent_id', $manager->id)
                ->sortBy('id')
                ->pluck('id')
                ->values()
                ->all();

            if (!empty($managerAgents)) {
                $managerGroups[] = [
                    'manager_id' => $manager->id,
                    'agents' => $managerAgents
                ];
            }
        }

        // if no eligible manager, skip director
        if (empty($managerGroups)) {
            $rotationByDirector[$director->id] = collect();
            continue;
        }

        // interleave managers equally (circular per manager's agent list)
        $maxAgents = max(array_map(fn($g) => count($g['agents']), $managerGroups));
        $directorRotation = collect();

        for ($i = 0; $i < $maxAgents; $i++) {
            foreach ($managerGroups as $group) {
                $agentCount = count($group['agents']);
                if ($agentCount === 0) continue;
                // circular index
                $idx = $i % $agentCount;
                $agentId = $group['agents'][$idx];
                $directorRotation->push([
                    'agent_id'    => $agentId,
                    'manager_id'  => $group['manager_id'],
                    'director_id' => $director->id,
                ]);
            }
        }

        $rotationByDirector[$director->id] = $directorRotation->values();
    }

    return $rotationByDirector; // array: directorId => Collection([...])
}

// New getNextAgent that strictly alternates directors
public static function getNextAgent($teamId, $locationId)
{
    if (empty($teamId) || empty($locationId)) {
        return [
            'status' => false,
            'availableAgent' => false,
            'actualAgent' => false
        ];
    }

    $siteDetail = SiteDetail::where([
        'team_id' => $teamId,
        'location_id' => $locationId
    ])->select('select_timezone')->first();

    $userTimezone = $siteDetail->select_timezone ?? 'Asia/Kolkata';
    $today = Carbon::today($userTimezone);

    // Build per-director rotations
    $rotationByDirector = self::getRotationByDirector($teamId, $locationId);

    // Filter out directors with no entries
    $rotationByDirector = array_filter($rotationByDirector, fn($c) => $c->count() > 0);

    if (empty($rotationByDirector)) {
        return [
            'status' => false,
            'availableAgent' => false,
            'actualAgent' => false
        ];
    }

    // Deterministic director order (by id ascending)
    $directorIds = array_values(array_map(fn($k) => $k, array_keys($rotationByDirector)));
    sort($directorIds);

    // Find last assigned ticket for today
    $lastTicket = QueueStorage::where('team_id', $teamId)
        ->where('locations_id', $locationId)
        ->whereNotNull('locations_id')
        ->whereNull('cancelled_datetime')
        ->where('status', '!=', 'Cancelled')
        ->whereDate('arrives_time', $today->format('Y-m-d'))
        ->select('id', 'assign_staff_id', 'actual_staff_assign_id')
        ->orderBy('id', 'desc')
        ->first();

    // Determine last director id (of last assigned agent)
    $lastDirectorId = null;
    if ($lastTicket && $lastTicket->actual_staff_assign_id) {
        $lastAgent = User::select('id', 'parent_id')->where('id', $lastTicket->actual_staff_assign_id)->first();
        if ($lastAgent) {
            $manager = User::select('id', 'parent_id')->where('id', $lastAgent->parent_id)->first();
            if ($manager) {
                $lastDirectorId = $manager->parent_id;
            }
        }
    }

    // Choose next director in round-robin order
    if ($lastDirectorId === null) {
        // no previous assignment — start from first director
        $nextDirectorIndex = 0;
    } else {
        $idx = array_search($lastDirectorId, $directorIds);
        $nextDirectorIndex = ($idx === false) ? 0 : (($idx + 1) % count($directorIds));
    }
    // We'll iterate directors starting from this index, and pick the first director that still has rotation entries
    $selectedDirectorId = null;
    for ($i = 0; $i < count($directorIds); $i++) {
        $candidateIndex = ($nextDirectorIndex + $i) % count($directorIds);
        $candidateDirId = $directorIds[$candidateIndex];
        if (isset($rotationByDirector[$candidateDirId]) && $rotationByDirector[$candidateDirId]->count() > 0) {
            $selectedDirectorId = $candidateDirId;
            break;
        }
    }

    if ($selectedDirectorId === null) {
        // fallback - shouldn't happen because we filtered empty directors
        $firstDir = $directorIds[0];
        $selectedDirectorId = $firstDir;
    }

    // Determine how many tickets already assigned today to this director (to use as pointer)
    // We find all agent ids under this director and count today's assignments with those agent ids
    $managerIds = User::where('parent_id', $selectedDirectorId)->where('level_id', 2)->pluck('id')->toArray();

    $agentIdsUnderDirector = User::whereIn('parent_id', $managerIds)
        ->where('level_id', 3)
        ->where('team_id', $teamId)
        ->where(function ($q) use ($locationId) {
            $q->whereJsonContains('locations', (string)$locationId)
              ->orWhereNull('locations');
        })
        ->pluck('id')
        ->toArray();

    $assignedCountForDirectorToday = 0;
    if (!empty($agentIdsUnderDirector)) {
        $assignedCountForDirectorToday = QueueStorage::where('team_id', $teamId)
            ->where('locations_id', $locationId)
            ->whereNull('cancelled_datetime')
            ->where('status', '!=', 'Cancelled')
            ->whereDate('arrives_time', $today->format('Y-m-d'))
            ->whereIn('actual_staff_assign_id', $agentIdsUnderDirector)
            ->count();
    }

    // Get director rotation and pick pointer
    $dirRotation = $rotationByDirector[$selectedDirectorId]; // Collection
    $rotationLen = $dirRotation->count();

    if ($rotationLen === 0) {
        // fallback — choose any logged-in agent
        $fallback = User::where('team_id', $teamId)
            ->where('level_id', 3)
            ->where('is_login', 1)
            ->where(function ($q) use ($locationId) {
                $q->whereJsonContains('locations', (string)$locationId)
                  ->orWhereNull('locations');
            })
            ->orderBy('id')
            ->first();

        return [
            'status' => true,
            'availableAgent' => $fallback ? $fallback->id : false,
            'actualAgent' => $fallback ? $fallback->id : false
        ];
    }

    // pointer is assignedCountForDirectorToday % rotationLen (circular)
    $pointer = $rotationLen > 0 ? ($assignedCountForDirectorToday % $rotationLen) : 0;
    $entry = $dirRotation[$pointer];

    $nextAgentId = $entry['agent_id'];
    $managerId = $entry['manager_id'];
    $directorId = $entry['director_id'];

    // Final check - ensure agent still logged in
    $isLoggedIn = User::where('id', $nextAgentId)
        ->where('is_login', 1)
        ->exists();

    if ($isLoggedIn) {
        return [
            'status' => true,
            'availableAgent' => $nextAgentId,
            'actualAgent' => $nextAgentId
        ];
    }

    // Fallbacks if chosen agent not logged in (rare because we filtered earlier)
    $availableAgent = User::where([
            ['parent_id', '=', $managerId],
            ['is_login', '=', 1],
            ['team_id', '=', $teamId],
            ['level_id', '=', 3],
        ])
        ->where(function ($q) use ($locationId) {
            $q->whereJsonContains('locations', (string)$locationId)
              ->orWhereNull('locations');
        })
        ->orderBy('id')
        ->first();

    if (!$availableAgent) {
        $availableAgent = User::whereIn('parent_id', $managerIds)
            ->where('is_login', 1)
            ->where('team_id', $teamId)
            ->where('level_id', 3)
            ->where(function ($q) use ($locationId) {
                $q->whereJsonContains('locations', (string)$locationId)
                  ->orWhereNull('locations');
            })
            ->orderBy('id')
            ->first();
    }

    if (!$availableAgent) {
        $availableAgent = User::where('team_id', $teamId)
            ->where('level_id', 3)
            ->where('is_login', 1)
            ->where(function ($q) use ($locationId) {
                $q->whereJsonContains('locations', (string)$locationId)
                  ->orWhereNull('locations');
            })
            ->orderBy('id')
            ->first();
    }

    return [
        'status' => true,
        'availableAgent' => $availableAgent ? $availableAgent->id : false,
        'actualAgent' => $nextAgentId
    ];
}

public function addons()
{
    return $this->hasMany(Addon::class, 'team_id', 'team_id'); 
    
}

}
