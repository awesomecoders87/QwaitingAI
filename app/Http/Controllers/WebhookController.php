<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;


class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Extract data from the incoming webhook request
        $data = $request->all();
        $url = $request->url();

        
        // return response()->json(['message' => $data['name']], 200);
        // Create a new user with the extracted data
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Optionally, you can perform additional actions here

        // Return a response, if needed
        return response()->json(['message' => $url], 200);
    }

    public function register(Request $request)
    {
        $response = Http::post('https://hooks.zapier.com/hooks/catch/18934975/2yn3dh8/', $request->all());

        if ($response->successful()) {
            return response()->json(['message' => 'User registered successfully'], 201);
        } else {
            return response()->json(['message' => 'Failed to register user'], $response->status());
        }
    }
}