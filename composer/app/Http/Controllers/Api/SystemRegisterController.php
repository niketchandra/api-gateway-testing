<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PatToken;
use App\Models\SystemRegister;
use Illuminate\Http\Request;

class SystemRegisterController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'org_id' => ['nullable', 'integer', 'min:1'],
            'system_name' => ['required', 'string', 'max:255'],
            'os_type' => ['required', 'string', 'max:100'],
            'ip_address' => ['required', 'string', 'max:45'],
            'tags' => ['nullable', 'string', 'max:512'],
            'metadata' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        $patToken = $request->attributes->get('patToken');

        if (!$patToken) {
            $token = $request->bearerToken();
            $hashedToken = $token ? hash('sha256', $token) : null;
            $patToken = $hashedToken ? PatToken::where('token', $hashedToken)->first() : null;
        }

        if (!$patToken) {
            return response()->json(['message' => 'PAT token required'], 401);
        }

        $system = SystemRegister::create([
            'pat_token_id' => $patToken->id,
            'user_id' => $user->id,
            'org_id' => $data['org_id'] ?? null,
            'system_name' => $data['system_name'],
            'os_type' => $data['os_type'],
            'ip_address' => $data['ip_address'],
            'tags' => $data['tags'] ?? null,
            'metadata' => $data['metadata'] ?? null,
        ]);

        return response()->json([
            'message' => 'System registered successfully',
            'system' => [
                'id' => $system->id,
                'pat_token_id' => $system->pat_token_id,
                'user_id' => $system->user_id,
                'org_id' => $system->org_id,
                'system_name' => $system->system_name,
                'os_type' => $system->os_type,
                'ip_address' => $system->ip_address,
                'tags' => $system->tags,
                'metadata' => $system->metadata,
                'created_at' => $system->created_at,
            ],
        ], 201);
    }
}
