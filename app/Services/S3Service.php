<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class S3Service
{
    public function uploadFile($localPath, $s3Path)
    {
        $stream = fopen($localPath, 'r');

        Storage::disk('s3')->put($s3Path, $stream);

        fclose($stream);

        return $s3Path;
    }
    public function getFileContent($key)
    {
        try {
            return Storage::disk('s3')->get($key);
        } catch (\Exception $e) {
            // \Log::error("S3 Get Error", [
            //     'key' => $key,
            //     'error' => $e->getMessage()
            // ]);
            return null;
        }
    }

    public function uploadUploadedFile($file, $s3Path)
    {
        Storage::disk('s3')->put($s3Path, file_get_contents($file));

        return $s3Path;
    }

    public function getPublicUrl($s3Path)
    {
        return Storage::disk('s3')->url($s3Path);
    }
}
