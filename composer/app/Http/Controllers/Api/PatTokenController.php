<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PatTokenController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'abilities' => ['sometimes', 'array'],
            'abilities.*' => ['string', 'max:100'],
            'expires_at' => ['sometimes', 'nullable', 'date'],
        ]);

        $user = $request->user();
        $abilities = $data['abilities'] ?? ['*'];
        $expiresAt = array_key_exists('expires_at', $data) && $data['expires_at'] !== null
            ? Carbon::parse($data['expires_at'])
            : null;

        $tokenResult = $user->createToken($data['name'], $abilities, $expiresAt);
        $token = $tokenResult->accessToken;

        return response()->json([
            'access_token' => $tokenResult->plainTextToken,
            'token_type' => 'bearer',
            'token' => [
                'id' => $token->id,
                'name' => $token->name,
                'abilities' => $token->abilities,
                'expires_at' => $token->expires_at,
                'created_at' => $token->created_at,
            ],
        ], 201);
    }
}
