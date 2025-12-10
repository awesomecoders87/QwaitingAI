<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    public function checkService(Request $request)
    {
        $request->validate([
            'service_name' => 'required|string'
        ]);

        // You can adjust these based on your system
        $teamId = $request->team_id ?? 3;
        $locationId = null;

        // Fetch all services from your existing model function
        $services = Category::getFirstCategorybooking($teamId, $locationId);

        // Normalize names for matching
        $exists = $services->first(function ($item) use ($request) {
            return strtolower($item->name) == strtolower($request->service_name)
                || strtolower($item->other_name ?? '') == strtolower($request->service_name);
        });

        if ($exists) {
            return response()->json([
                'status' => 'success',
                'message' => 'Service found',
                'service' => [
                    'id' => $exists->id,
                    'name' => $exists->name,
                    'other_name' => $exists->other_name,
                    'description' => $exists->description,
                    'img' => $exists->img,
                    'redirect_url' => $exists->redirect_url,
                ]
            ]);
        }

        // Return full list if not found
        return response()->json([
            'status' => 'error',
            'message' => 'Service not found',
            'services' => $services->map(function ($s) {
                return [
                    'id' => $s->id,
                    'name' => $s->name
                ];
            })
        ]);
    }
}
