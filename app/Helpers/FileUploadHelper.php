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

        // Delete old file if specified
        if ($oldFile) {
            self::delete($folder, $oldFile);
        }

        // Generate safe filename
        $extension = $file->getClientOriginalExtension();
        $filename  = uniqid() . '.' . $extension;

        // Move file
        $file->move($uploadPath, $filename);

        return $filename;
    }

    /**
     * Delete a file from a given folder.
     *
     * @param string $folder
     * @param string $filename
     * @return bool
     */
    public static function delete(string $folder, string $filename): bool
    {
        $filePath = self::getUploadPath($folder) . '/' . $filename;
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }

    /**
     * Get the full upload path depending on environment.
     *
     * @param string $folder
     * @return string
     */
    public static function getUploadPath(string $folder): string
    {
        $folder = trim($folder, '/');

        // For production (shared hosting)
        if (app()->environment('production') && isset($_SERVER['DOCUMENT_ROOT'])) {
            return rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/uploads/' . $folder;
        }

        // Default (local)
        return public_path('uploads/' . $folder);
    }
}
