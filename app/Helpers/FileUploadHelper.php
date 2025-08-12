<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver; // Or Imagick driver if installed

class FileUploadHelper
{
    /**
     * Upload and compress an image to WebP (default quality 75).
     *
     * @param UploadedFile $file
     * @param string       $folder
     * @param string|null  $oldFile
     * @return string|null
     */
    public static function upload(UploadedFile $file, string $folder, string $oldFile = null): ?string
    {
        $uploadPath = self::getUploadPath($folder);
        $quality = 65; // WebP quality
        $maxFileSize = 100 * 1024; // 100 KB in bytes

        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        if ($oldFile) {
            self::delete($folder, $oldFile);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $filename  = uniqid() . '.webp'; // Always save as WebP
        $fullPath  = $uploadPath . '/' . $filename;

        // Only process if it's an image
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'webp'])) {

            // If already below size threshold, just move without compression
            if ($file->getSize() <= $maxFileSize) {
                $file->move($uploadPath, $filename);
                return $filename;
            }

            // Create ImageManager instance (GD driver)
            $manager = new ImageManager(new Driver());

            // Read image
            $image = $manager->read($file->getPathname());

            // Optional: Resize if too large
            if ($image->width() > 1920) {
                $image->resize(1920, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            // Convert to WebP with given quality and save
            $image->toWebp($quality)->save($fullPath);

        } else {
            // Non-image: just move without modification
            $file->move($uploadPath, $filename);
        }

        return $filename;
    }

    public static function delete(string $folder, string $filename): bool
    {
        $filePath = self::getUploadPath($folder) . '/' . $filename;
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }

    public static function getUploadPath(string $folder): string
    {
        $folder = trim($folder, '/');

        if (app()->environment('production') && isset($_SERVER['DOCUMENT_ROOT'])) {
            return rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/uploads/' . $folder;
        }

        return public_path('uploads/' . $folder);
    }
}
