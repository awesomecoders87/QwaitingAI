<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DynamicReport extends Model
{
    protected $fillable = [
        'team_id',
        'location_id',
        'report_name',
        'report_fields',
        'status',
    ];

    protected $casts = [
        'report_fields' => 'array', // auto-cast JSON to array
    ];

    public static function availableFields($teamId,$locationId){

         $levels = Level::where('team_id', $teamId)
        ->where('location_id', $locationId)
        ->whereIn('level', [1, 2, 3])
        ->get()
        ->keyBy('level');

       $level1 = $levels[1]->name ?? 'Level 1';
       $level2 = $levels[2]->name ?? 'Level 2';
       $level3 = $levels[3]->name ?? 'Level 3';

        return [
        "srno" =>__('report.S.No'),
        "token" =>__('report.Token'),
        "status" =>__('report.status'),
        "level1" => $level1,
        "level2" =>$level2,
        "level3" =>$level3,
        "arrives_time" =>__('report.created at'),
        "counter" =>__('report.Counter'),
        "served_by" => __('report.Served By'),
        "name" => __('report.name'),
        "contact" =>__('report.contact'),
        "email" =>__('report.Email'),
        "closed_by" => __('report.Closed By'),
        "response_time" =>__('report.Response Time'),
        "serving_time" =>__('report.Serving Time'),
        "mode" =>__('text.Mode'),
        "start_acronym" =>__('text.Acronym'),
        "forward_counter_id" =>__('text.forward counter'),
        "transfer_id" =>__('text.Transfer Service'),
        "is_missed" =>__('report.Missed'),
        "is_hold" =>__('text.Hold'),
        "hold_by" =>__('text.Hold By'),
        "json" =>__('text.Custom Form Details'),
        "called_datetime"       => __('text.Called At'),
        "start_datetime"        => __('text.Start Time'),
       "closed_datetime"       => __('text.Closed At'),
       "hold_start_datetime"   => __('text.Hold Start'),
        "hold_end_datetime"     => __('text.Hold End'),
        "locations_id" =>__('report.Location'),
        "meeting_link" =>__('text.meeting link'),
        "esitmate_note" =>__('text.Estimate Notes'),
        ];
    }
}
