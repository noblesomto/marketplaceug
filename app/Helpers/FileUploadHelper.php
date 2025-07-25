<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;

class FileUploadHelper
{
    /**
     * Upload a file to a given folder.
     *
     * @param UploadedFile $file
     * @param string       $folder   e.g., 'verification', 'avatars'
     * @param string|null  $oldFile  Existing file to delete
     * @return string|null Filename of uploaded file or null
     */
    public static function upload(UploadedFile $file, string $folder, string $oldFile = null): ?string
    {
        $uploadPath = self::getUploadPath($folder);

        // Create directory if it doesn't exist
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Delete old file if exists
        if ($oldFile) {
            $oldPath = $uploadPath . '/' . $oldFile;
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        // Generate safe filename
        $extension = $file->getClientOriginalExtension();
        $filename  = uniqid() . '.' . $extension;

        // Move file
        $file->move($uploadPath, $filename);

        return $filename;
    }

    /**
     * Get the full upload path depending on environment.
     *
     * @param string $folder
     * @return string
     */
    public static function getUploadPath(string $folder): string
    {
        if (file_exists(base_path('../public'))) {
            // Shared hosting structure
            return base_path('../public/uploads/' . $folder);
        }

        // Local development
        return public_path('uploads/' . $folder);
    }
}
