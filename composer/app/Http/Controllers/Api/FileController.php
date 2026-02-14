<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConfigurationFile;
use App\Models\RawData;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FileController extends Controller
{
    public function upload(Request $request)
    {
        $data = $request->validate([
            'file_name' => ['required', 'string', 'max:255'],
            'file_data' => ['required', 'string'], // base64 or raw text
        ]);

        $user = $request->user();

        // Generate unique file location
        $fileLocation = 'uploads/' . $user->id . '/' . Str::uuid() . '_' . $data['file_name'];

        // Create configuration file record
        $configFile = ConfigurationFile::create([
            'user_id' => $user->id,
            'file_name' => $data['file_name'],
            'file_location' => $fileLocation,
        ]);

        // Store raw data
        RawData::create([
            'file_id' => $configFile->id,
            'user_id' => $user->id,
            'file_data' => $data['file_data'],
        ]);

        return response()->json([
            'message' => 'File uploaded successfully',
            'file' => [
                'id' => $configFile->id,
                'file_name' => $configFile->file_name,
                'file_location' => $configFile->file_location,
                'created_at' => $configFile->created_at,
            ],
        ], 201);
    }

    public function download(Request $request, $fileId)
    {
        $user = $request->user();

        // Find the configuration file
        $configFile = ConfigurationFile::where('id', $fileId)
            ->where('user_id', $user->id)
            ->with('rawData')
            ->firstOrFail();

        return response()->json([
            'file' => [
                'id' => $configFile->id,
                'file_name' => $configFile->file_name,
                'file_location' => $configFile->file_location,
                'file_data' => $configFile->rawData->file_data,
                'created_at' => $configFile->created_at,
                'updated_at' => $configFile->updated_at,
            ],
        ]);
    }
}
