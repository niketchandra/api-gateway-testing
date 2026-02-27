<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConfigurationFile;
use App\Models\RawData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    /**
     * Upload configuration file (text file with .config extension or no extension)
     * Saves to file system and stores raw data in database
     */
    public function uploadConfigFile(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'max:10240', // Max 10MB
                function ($attribute, $value, $fail) {
                    $extension = $value->getClientOriginalExtension();
                    $mimeType = $value->getMimeType();
                    
                    // Allow .config files, files without extension, and text files
                    $allowedExtensions = ['config', 'conf', 'cfg', 'txt', ''];
                    $allowedMimeTypes = [
                        'text/plain',
                        'application/octet-stream',
                        'application/x-config',
                        'text/x-config',
                    ];
                    
                    if (!in_array($extension, $allowedExtensions) && !in_array($mimeType, $allowedMimeTypes)) {
                        $fail('The file must be a text/configuration file.');
                    }
                },
            ],
        ]);

        $user = $request->user();
        $file = $request->file('file');
        
        // Get original filename
        $originalName = $file->getClientOriginalName();
        
        // Generate unique filename with UUID
        $extension = $file->getClientOriginalExtension();
        $fileName = Str::uuid() . ($extension ? '.' . $extension : '');
        
        // Define storage path: config_files/{user_id}/
        $storagePath = "config_files/{$user->id}";
        
        // Store file in Laravel storage (storage/app/config_files/{user_id}/)
        $filePath = $file->storeAs($storagePath, $fileName);
        
        // Read file content for raw_data table
        $fileContent = Storage::get($filePath);
        
        // Create configuration file record
        $configFile = ConfigurationFile::create([
            'user_id' => $user->id,
            'file_name' => $originalName,
            'file_location' => $filePath,
        ]);

        // Store raw data
        RawData::create([
            'file_id' => $configFile->id,
            'user_id' => $user->id,
            'file_name' => $originalName,
            'file_data' => $fileContent,
        ]);

        return response()->json([
            'message' => 'Configuration file uploaded successfully',
            'file' => [
                'id' => $configFile->id,
                'file_name' => $configFile->file_name,
                'original_name' => $originalName,
                'file_location' => $configFile->file_location,
                'file_size' => strlen($fileContent),
                'created_at' => $configFile->created_at,
            ],
        ], 201);
    }

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
            'file_name' => $data['file_name'],
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

    /**
     * List all configuration files for the authenticated user (active only)
     */
    public function listConfigFiles(Request $request)
    {
        $user = $request->user();

        $files = ConfigurationFile::where('user_id', $user->id)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($file) {
                return [
                    'id' => $file->id,
                    'file_name' => $file->file_name,
                    'file_location' => $file->file_location,
                    'status' => $file->status,
                    'created_at' => $file->created_at,
                    'updated_at' => $file->updated_at,
                ];
            });

        return response()->json([
            'total' => $files->count(),
            'files' => $files,
        ]);
    }

    /**
     * Download configuration file from file system (active only)
     */
    public function downloadConfigFile(Request $request, $fileId)
    {
        $user = $request->user();

        // Find the configuration file (active only)
        $configFile = ConfigurationFile::where('id', $fileId)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->firstOrFail();

        // Check if file exists in storage
        if (!Storage::exists($configFile->file_location)) {
            return response()->json([
                'message' => 'File not found in storage',
            ], 404);
        }

        // Get file content
        $fileContent = Storage::get($configFile->file_location);
        $mimeType = Storage::mimeType($configFile->file_location);

        // Return file as download
        return response($fileContent, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'attachment; filename="' . $configFile->file_name . '"');
    }

    /**
     * Get raw data for a specific configuration file (active only)
     */
    public function getRawData(Request $request, $fileId)
    {
        $user = $request->user();

        // Find the configuration file with raw data (active only)
        $configFile = ConfigurationFile::where('id', $fileId)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->with(['rawData' => function ($query) {
                $query->where('status', 'active');
            }])
            ->firstOrFail();

        if (!$configFile->rawData) {
            return response()->json([
                'message' => 'Raw data not found for this file',
            ], 404);
        }

        return response()->json([
            'file_id' => $configFile->id,
            'file_name' => $configFile->file_name,
            'raw_data' => [
                'id' => $configFile->rawData->id,
                'file_data' => $configFile->rawData->file_data,
                'status' => $configFile->rawData->status,
                'created_at' => $configFile->rawData->created_at,
                'updated_at' => $configFile->rawData->updated_at,
            ],
        ]);
    }

    /**
     * Soft delete configuration file (mark as inactive)
     * Also marks the related raw_data as inactive
     */
    public function deleteConfigFile(Request $request, $fileId)
    {
        $user = $request->user();

        // Find the configuration file (active only)
        $configFile = ConfigurationFile::where('id', $fileId)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->with('rawData')
            ->firstOrFail();

        // Mark configuration file as inactive
        $configFile->status = 'inactive';
        $configFile->touch(); // Updates updated_at timestamp
        $configFile->save();

        // Mark raw data as inactive
        if ($configFile->rawData) {
            $configFile->rawData->status = 'inactive';
            $configFile->rawData->touch(); // Updates updated_at timestamp
            $configFile->rawData->save();
        }

        return response()->json([
            'message' => 'Configuration file marked as inactive successfully',
            'file' => [
                'id' => $configFile->id,
                'file_name' => $configFile->file_name,
                'status' => $configFile->status,
                'updated_at' => $configFile->updated_at,
            ],
        ]);
    }
}
