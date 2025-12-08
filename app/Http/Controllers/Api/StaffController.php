<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class StaffController extends Controller
{public function index(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'teamId' => 'required|integer',
                'locationId' => 'required|integer',
                'offset' => 'nullable|integer|min:0',
                'limit' => 'nullable|integer|min:1|max:100',
            ]);
    
            $teamId = $validatedData['teamId'];
            $locationId = $validatedData['locationId'];
            $offset = $validatedData['offset'] ?? 0;
            $limit = $validatedData['limit'] ?? 10;
    
            $query = User::where('team_id', $teamId)
                ->whereJsonContains('locations', $locationId)
                ->where('is_admin', '!=', 1)
                ->where('id', '!=', auth()->id())
                ->whereDoesntHave('roles', function ($q) {
                    $q->where('name', 'superadmin');
                });
    
            $total = $query->count();
    
            $data = $query->offset($offset)
                          ->limit($limit)
                          ->get();
    
            return response()->json([
                'status' => 'success',
                'total' => $total,
                'offset' => $offset,
                'limit' => $limit,
                'data' => $data
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
}
