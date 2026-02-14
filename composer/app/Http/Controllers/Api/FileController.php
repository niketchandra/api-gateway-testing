<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    public function upload(Request $request)
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $file = $data['file'];
        $fileId = Str::uuid()->toString();
        $safeName = $file->getClientOriginalName() ?: 'upload.bin';
        $path = 'uploads/' . $fileId . '_' . $safeName;

        Storage::disk('local')->put($path, file_get_contents($file->getRealPath()));

        return response()->json([
            'file_id' => $fileId,
            'filename' => $safeName,
        ]);
    }

    public function download(string $fileId)
    {
        $files = Storage::disk('local')->files('uploads');
        $match = null;

        foreach ($files as $file) {
            if (str_starts_with(basename($file), $fileId . '_')) {
                $match = $file;
                break;
            }
        }

        if (!$match) {
            return response()->json(['detail' => 'File not found'], 404);
        }

        return Storage::disk('local')->download($match);
    }
}
