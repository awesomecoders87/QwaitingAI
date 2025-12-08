<?php

namespace App\Observers;

use App\Models\Tenant;
use App\Models\FormField;

class TenantObserver
{
    /**
     * Handle the Team "created" event.
     */
    public function created(Tenant $team)
    {
        // Add default name and phone fields in form field table when created new team

        $data=[
            [
            'team_id' => $team->id,
            'type' => 'Text',
            'title' => 'name',
            'ticket_screen' => 1,
            'options' => '{}',
            'after_scan_screen' => 0,
            'mandatory' => 1,
            'placeholder' => 'Name',
            'custom_class' => '',
            'before_appointment_form' => 0,
            'after_appointment_form' => 0,
            'minimum_number_allowed' => 1,
            'maximum_number_allowed' => 20,
            'policy_content' => '',
            'policy_url' => '',
            'label' => 'Name',
            'validation' => '/^[a-zA-Z\s]+$/',
            'sort' => 1,
            'policy' => '',
            'is_edit_remove' => 0,
            ],
            [
            'team_id' => $team->id,
            'type' => 'Text',
            'title' => 'phone',
            'ticket_screen' => 1,
            'options' => '{}',
            'after_scan_screen' => 0,
            'mandatory' => 1,
            'placeholder' => 'Phone',
            'custom_class' => '',
            'before_appointment_form' => 0,
            'after_appointment_form' => 0,
            'minimum_number_allowed' => 1,
            'maximum_number_allowed' => 15,
            'policy_content' => '',
            'policy_url' => '',
            'label' => 'Phone',
            'validation' => '/^\d+$/',
            'sort' => 2,
            'policy' => '',
            'is_edit_remove' => 0,
            ],
        ];

        FormField::insert($data);
    }

    /**
     * Handle the Team "updated" event.
     */
    public function updated(Tenant $team): void
    {
        //
    }

    /**
     * Handle the Team "deleted" event.
     */
    public function deleted(Tenant $team): void
    {
        //
    }

    /**
     * Handle the Team "restored" event.
     */
    public function restored(Tenant $team): void
    {
        //
    }

    /**
     * Handle the Team "force deleted" event.
     */
    public function forceDeleted(Tenant $team): void
    {
        //
    }
}
