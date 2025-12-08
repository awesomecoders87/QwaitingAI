<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{
    Queue,
    ScreenTemplate,
    SiteDetail,
    PusherDetail
};
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class DisplayScreenController extends Controller
{
    public function show($encodedId)
    {
        try {
            $screenId = base64_decode($encodedId);
            $teamId = tenant('id');

            // Set language
            if (Session::has('locale')) {
                App::setLocale(Session::get('locale'));
            }

            // Fetch screen template
            $currentTemplate = ScreenTemplate::viewDetails($teamId, $screenId);
            if (!$currentTemplate) abort(404);

            $imageTemplates = $currentTemplate->json_data ? json_decode($currentTemplate->json_data, true) : [];
            $videoTemplates = $currentTemplate->json ? json_decode($currentTemplate->json, true) : [];

            // Location handling
            // $location = Session::get('selectedLocation', $currentTemplate->location_id);
            // Session::put('selectedLocation', $location);
            $location =  $currentTemplate->location_id;
             Session::put('selectedLocation', $location);
            if (!$location) abort(404);

            // Timezone
            Queue::timezoneSet();
            $timezone = Session::get('timezone_set');

            // Site details
            $siteData = SiteDetail::where('team_id', $teamId)
                ->where('location_id', $location)
                ->select('id','team_id','location_id','queue_heading_first','queue_heading_second')
                ->first();

            // Pusher details
            $pusherDetails = PusherDetail::viewPusherDetails($teamId, $location);
            $pusherKey = $pusherDetails->key ?? env('PUSHER_APP_KEY');
            $pusherCluster = $pusherDetails->options_cluster ?? env('PUSHER_APP_CLUSTER');

            // Counters & categories
            $counterIDs = $currentTemplate?->counters?->pluck('id')->toArray();
            $categoryIDs = $currentTemplate?->categories?->pluck('id')->toArray();

            // Queues
            $queues = $this->getQueues($teamId, $location, $currentTemplate, $counterIDs, $categoryIDs);

            return view('display-screen', [
                'teamId'          => $teamId,
                'location'        => $location,
                'currentTemplate' => $currentTemplate,
                'imageTemplates'  => $imageTemplates,
                'videoTemplates'  => $videoTemplates,
                'siteData'        => $siteData,
                'pusherKey'       => $pusherKey,
                'pusherCluster'   => $pusherCluster,
                'timezone'        => $timezone,
                'queues'          => $queues,
            ]);

        } catch (\Exception $e) {
            Log::error('Display screen load failed: ' . $e->getMessage());
            abort(500, 'Display screen could not be loaded.');
        }
    }

    private function getQueues($teamId, $location, $template, $counterIDs, $categoryIDs)
    {
        try {
            $queues = Queue::getAllQueues(
                $teamId,
                (int)$location,
                $template->show_queue_number,
                $template->type === "Counter" ? $counterIDs : null,
                $template->type !== "Counter" ? $categoryIDs : null,
                $template->is_skip_closed_call_from_display_screen,
                $template->is_waiting_call_show === ScreenTemplate::STATUS_ACTIVE,
                $template->is_skip_call_show === ScreenTemplate::STATUS_ACTIVE,
                $template->is_hold_queue === ScreenTemplate::STATUS_ACTIVE,
            );

            return [
                'display' => $queues['display'],
                'waiting' => $queues['waiting'],
                'missed'  => $queues['missed'],
                'hold'    => $queues['hold']
            ];
        } catch (\Exception $e) {
            Log::error('Queue fetch failed: ' . $e->getMessage(), compact('teamId', 'location'));
            return [
                'display' => collect(),
                'waiting' => collect(),
                'missed'  => collect(),
                'hold'    => collect()
            ];
        }
    }

public function refreshQueues(Request $request)
{
    $teamId = tenant('id') ?? 0; // fallback if tenant not set

    // Get location and template_id from either GET or POST
    $location = $request->input('location');
    $templateId = $request->input('template_id');

    $template = ScreenTemplate::find($templateId);

    if (!$template) {
        return response()->json(['error' => 'Template not found'], 404);
    }

    // Safely get counters and categories
    $counterIDs = $template->counters?->pluck('id')->toArray() ?? [];
    $categoryIDs = $template->categories?->pluck('id')->toArray() ?? [];

    try {
        $queues = $this->getQueues($teamId, $location, $template, $counterIDs, $categoryIDs);
        return response()->json($queues);
    } catch (\Exception $e) {
        \Log::error('Queue refresh failed: ' . $e->getMessage(), ['request' => $request->all()]);
        return response()->json(['error' => 'Queue refresh failed'], 500);
    }
}
}
