<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Session;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'max:128', 'confirmed'],
            'dob' => ['nullable', 'date'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => Hash::make($data['password']),
            'dob' => $data['dob'] ?? null,
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'dob' => $user->dob,
            ],
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'max:128'],
        ]);

        $user = User::where('email', $data['email'])->first();
        if (!$user || !Hash::check($data['password'], $user->password_hash)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Generate session token (temporary bearer token)
        $plainToken = Session::generateToken();
        $hashedToken = hash('sha256', $plainToken);

        // Create session record (expires in 24 hours)
        $session = Session::create([
            'user_id' => $user->id,
            'token' => $hashedToken,
            'expires_at' => Carbon::now()->addHours(24),
        ]);

        return response()->json([
            'access_token' => $plainToken,
            'token_type' => 'bearer',
            'expires_at' => $session->expires_at,
            'message' => 'Session token is temporary. Use it to create permanent PAT tokens.',
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->header('Authorization');
        
        if ($token && str_starts_with($token, 'Bearer ')) {
            $plainToken = substr($token, 7);
            $hashedToken = hash('sha256', $plainToken);
            
            Session::where('token', $hashedToken)->delete();
        }

        return response()->noContent();
    }
}
