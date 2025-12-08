<?php

namespace App\Livewire;

use Livewire\Attributes\Validate;
use Livewire\Component;
use App\Models\Tenant;
use App\Models\Domain;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class AddTenant extends Component
{
   #[Validate('required|string|max:255')]
    public string $name = '';

 
  
    public function save()
    {
      $this->validate([
            'name' => 'required|string|max:255',
        ]);



        $slug = Str::slug($this->name);
        $domainName = $slug .'.'.env('PARENT_DOMAIN');
  
        $username = ucfirst($this->name);

        // Check if domain already exists
        if (Domain::where('domain', $domainName)->exists()) {
            $this->addError('name', 'Domain already exists.');
            return;
        }

       


  
      $tenant = Tenant::create([
    
        'name' => ucfirst($this->name),
        'brand' => ucfirst($this->name),
    
]);
         $tenant->domains()->create(['domain' => $domainName]);

     

           // Check if username already exists for is_admin = 1
        // if (User::where('username', $username)->exists()) {
        //     $this->addError('name', 'Admin user with this username already exists.');
        //     return;
        // }
//         // Step 3: Create admin user
        $user = User::create([
            'name' => strtolower($this->name),
            'username' => $username,
            'email' => strtolower($slug) . '@gmail.com',
            'phone' => '',
            'is_admin' => 1,
            'email_verified_at' => now(),
            'password' => Hash::make('Password@123'),
            'remember_token' => Str::random(60),
            'address' => 'Mohali',
            'timezone' => 'Asia/Kolkata',
            'language' => 'eng',
            'country' => '92',
            'locations' => [],
            'sms_reminder_queue' => 1,
            'team_id' => $tenant->id,
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i',
            'created_at' => now(),
            'role_id' => 1,
            'updated_at' => now()->addDays(3),
            'is_login' => 1,
            'is_active' => 1,
        ]);

        // Step 4: Assign "Admin" role to user
        if ($adminRole = Role::where('name', 'Admin')->first()) {
            $user->roles()->attach($adminRole->id);
        }

        // Reset form and show success message
        $this->reset('name');
        session()->flash('success', 'Tenant, domain, and admin user created successfully.');
    }

    public function render()
    {
        return view('livewire.add-tenant');
    }
}
