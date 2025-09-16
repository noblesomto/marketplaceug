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
        $quality = 65;
        $maxFileSize = 100 * 1024;

        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        if ($oldFile) {
            self::delete($folder, $oldFile);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $filename  = uniqid() . '.webp';
        $fullPath  = $uploadPath . '/' . $filename;

        $manager = new ImageManager(new Driver());

        // --- HEIC/HEIF Handling ---
        if (in_array($extension, ['heic', 'heif'])) {
            // Check if heif-convert is installed
            $heifConvertExists = shell_exec("command -v heif-convert");
            if (!$heifConvertExists) {
                throw new \Exception("HEIC images are not supported on this server. Please upload JPG or PNG instead.");
            }

            $tempPath = sys_get_temp_dir() . '/' . uniqid() . '.jpg';

            // Convert HEIC → JPG
            exec("heif-convert " . escapeshellarg($file->getPathname()) . " " . escapeshellarg($tempPath) . " 2>&1", $output, $status);

            if ($status !== 0 || !file_exists($tempPath)) {
                throw new \Exception("HEIC conversion failed. Please try again with JPG or PNG.");
            }

            $image = $manager->read($tempPath)->orient();

            // Clean up temp file
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }
        // --- Normal Image Handling ---
        elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'webp'])) {
            $image = $manager->read($file->getPathname())->orient();
        } else {
            // Non-image fallback
            $file->move($uploadPath, $filename);
            return $filename;
        }

        // IMPROVED RESIZE LOGIC - Fix for stretched images
        $maxWidth = 1920;
        $maxHeight = 1920;

        // Method 1: Resize based on largest dimension (recommended)
        if ($image->width() > $maxWidth || $image->height() > $maxHeight) {
            $image->resize($maxWidth, $maxHeight, function ($constraint) {
                $constraint->aspectRatio();      // Maintain aspect ratio
                $constraint->upsize(false);      // Don't upsize smaller images
            });
        }

        // Alternative Method 2: Scale down proportionally if image is too large
        // Uncomment this and comment out Method 1 if you prefer this approach
        /*
        $currentWidth = $image->width();
        $currentHeight = $image->height();

        if ($currentWidth > $maxWidth || $currentHeight > $maxHeight) {
            // Calculate scale factor to fit within bounds
            $scaleX = $maxWidth / $currentWidth;
            $scaleY = $maxHeight / $currentHeight;
            $scale = min($scaleX, $scaleY); // Use smaller scale to fit within bounds

            $newWidth = (int)($currentWidth * $scale);
            $newHeight = (int)($currentHeight * $scale);

            $image->resize($newWidth, $newHeight);
        }
        */

        // Alternative Method 3: Fit within bounds (adds padding if needed)
        // Uncomment this and comment out Method 1 if you want exact dimensions with padding
        /*
        if ($image->width() > $maxWidth || $image->height() > $maxHeight) {
            $image->fit($maxWidth, $maxHeight, function ($constraint) {
                $constraint->upsize(false);
            }, 'center');
        }
        */

        // Save as WebP with error handling
        try {
            $image->toWebp($quality)->save($fullPath);

            // Optional: Check final file size and adjust quality if needed
            if (filesize($fullPath) > $maxFileSize && $quality > 30) {
                $newQuality = max(30, $quality - 15);
                $image->toWebp($newQuality)->save($fullPath);
            }

        } catch (\Exception $e) {
            throw new \Exception("Failed to save image: " . $e->getMessage());
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
