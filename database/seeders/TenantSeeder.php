<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\Domain;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $t1 = Tenant::create(['id'=>'tenant','name'=>'Tenant']);
        // $t1->domains()->create(['domain' => 'tenant.localhost']);

        // $t2 = Tenant::create(['id'=>'tenant2','name'=>'Tenant 2']);
        // $t2->domains()->create(['domain' => 'tenant2.localhost']);

        // $t3 = Tenant::create(['id'=>'tenant3','name'=>'Tenant 3']);
        // $t3->domains()->create(['domain' => 'tenant3.localhost']);

        $t1 = Tenant::create(['name' => 'Tenant']);
        Domain::create([
            'domain' => 'tenant.localhost',
            'team_id' => $t1->id, // ✅ Fix: Assign the correct ID
        ]);
        $t2 = Tenant::create(['name'=>'Tenant 2']);
        $t2->domains()->create(['domain' => 'tenant2.localhost']);

        $t3 = Tenant::create(['name'=>'Tenant 3']);
        $t3->domains()->create(['domain' => 'tenant3.localhost']);
    }
}
