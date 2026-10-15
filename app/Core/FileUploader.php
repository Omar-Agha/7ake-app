<?php

namespace App\Core;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileUploader
{
    /**
     * Upload the given file to storage.
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param string|null $disk
     */
    public function uploadFile(UploadedFile $file, string $directory = 'uploads', ?string $disk = null): string
    {
        $disk = $disk ?? config('filesystems.default');

        // Store the file in the specified directory and disk
        return $file->store($directory, $disk);
    }

    /**
     * Get the full URL of a stored file.
     *
     * @param string $path
     * @param string|null $disk
     * @return string
     */
    public function getFileUrl(string $path, ?string $disk = null): string
    {
        $disk = $disk ?? config('filesystems.default');
        return Storage::disk($disk)->url($path);
    }
}