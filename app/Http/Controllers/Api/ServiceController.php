<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class ServiceController extends Controller
{
    public function checkService(Request $request)
    {
        // Validate request
        $validator = \Validator::make($request->all(), [
            'service_name' => 'required|string',
            'team_id'      => 'nullable|integer',
            'location_id'  => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 400);
        }

        // Defaults
        $teamId     = $request->team_id ?? 3;      // your default team
        $locationId = $request->location_id ?? null;

        // Fetch services based on your existing model logic
        $services = Category::getFirstCategorybooking($teamId, $locationId);

        // Check if service exists (matching name OR other_name)
        $queryName = strtolower($request->service_name);

        $exists = $services->first(function ($service) use ($queryName) {
            return strtolower($service->name) === $queryName ||
                   strtolower($service->other_name ?? '') === $queryName;
        });

        // Service found
        if ($exists) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Service found',
                'service' => [
                    'id'           => $exists->id,
                    'name'         => $exists->name,
                    'other_name'   => $exists->other_name,
                    'description'  => $exists->description,
                    'img'          => $exists->img,
                    'redirect_url' => $exists->redirect_url,
                ]
            ]);
        }

        // Service NOT found — return full list
        return response()->json([
            'status'  => 'error',
            'message' => 'Service not found',
            'services' => $services->map(function ($s) {
                return [
                    'id'   => $s->id,
                    'name' => $s->name,
                ];
            })
        ]);
    }
}
