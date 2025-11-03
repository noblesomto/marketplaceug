<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaImageService
{
    public function handleImageUploads(
        Request $request,
        HasMedia $model,
        string $collection = 'images',
        bool $clearExisting = false
    ): array {
        if (!$request->hasFile('images')) {
            return [
                'accepted' => 0,
                'rejected' => 0,
                'total' => 0,
                'uploaded_media' => []
            ];
        }

        if ($clearExisting) {
            $model->clearMediaCollection($collection);
        }

        $images = array_values($request->file('images'));
        $order = explode(',', $request->input('image_order', ''));

        if (empty($order) || count($order) != count($images)) {
            $order = array_keys($images);
        }

        $acceptedCount = 0;
        $rejectedCount = 0;
        $uploadedMedia = [];
        $processedImages = [];

        foreach ($order as $position => $index) {
            if (!isset($images[$index]) || !$images[$index]->isValid()) {
                continue;
            }

            if ($this->hasWatermark($images[$index])) {
                $rejectedCount++;
                Log::info("Image rejected due to watermark: " . $images[$index]->getClientOriginalName());
                continue;
            }

            $processedImages[] = [
                'file' => $images[$index],
                'position' => $position + 1
            ];
            $acceptedCount++;
        }

        if (empty($processedImages)) {
            throw ValidationException::withMessages([
                'images' => ['All uploaded images contain watermarks. Please upload images without watermarks.']
            ]);
        }

        foreach ($processedImages as $imageData) {
            try {
                $image = $imageData['file'];
                $position = $imageData['position'];

                $media = $model
                    ->addMedia($image)
                    ->withCustomProperties([
                        'position' => $position,
                        'original_name' => $image->getClientOriginalName()
                    ])
                    ->usingFileName(uniqid() . '.webp')
                    ->toMediaCollection($collection);

                $media->order_column = $position;
                $media->save();

                $this->deleteOriginalAfterConversions($media);

                $uploadedMedia[] = $media;
            } catch (\Exception $e) {
                Log::error("Failed to upload image for {$model->getMorphClass()} ID {$model->id}: " . $e->getMessage());
                continue;
            }
        }

        return [
            'accepted' => $acceptedCount,
            'rejected' => $rejectedCount,
            'total' => $acceptedCount + $rejectedCount,
            'uploaded_media' => $uploadedMedia
        ];
    }

    protected function hasWatermark($imageFile): bool
{
    try {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($imageFile->getRealPath());

        $textScore = $this->detectTextWatermark($image);
        $colorScore = $this->detectColorWatermark($image);
        $transparentScore = $this->detectTransparentWatermark($image);
        $patternScore = $this->detectPatternWatermark($image);

        // More conservative total scoring - require stronger evidence
        $totalScore = ($textScore * 1.0) + ($colorScore * 2.0) + ($transparentScore * 0.8) + ($patternScore * 0.8);

        //Log::info("Watermark detection scores for {$imageFile->getClientOriginalName()}: text={$textScore}, color={$colorScore}, transparent={$transparentScore}, pattern={$patternScore}, total={$totalScore}");

        return $totalScore >= 2.0;
    } catch (\Exception $e) {
        Log::error("Watermark detection error: " . $e->getMessage());
        return false;
    }
}

protected function detectColorWatermark($image): float
{
    try {
        $testImage = clone $image;
        $testImage->resize(800, null, fn($c) => $c->aspectRatio()->upsize());

        $gd = $testImage->core()->native();
        $width = imagesx($gd);
        $height = imagesy($gd);

        // Define regions to check (corners and center)
        $regions = [
            ['x' => 0, 'y' => 0, 'w' => $width * 0.3, 'h' => $height * 0.3],
            ['x' => $width * 0.7, 'y' => 0, 'w' => $width * 0.3, 'h' => $height * 0.3],
            ['x' => 0, 'y' => $height * 0.7, 'w' => $width * 0.3, 'h' => $height * 0.3],
            ['x' => $width * 0.7, 'y' => $height * 0.7, 'w' => $width * 0.3, 'h' => $height * 0.3],
            ['x' => $width * 0.35, 'y' => $height * 0.35, 'w' => $width * 0.3, 'h' => $height * 0.3],
        ];

        $suspiciousRegions = 0;

        foreach ($regions as $region) {
            $textLikePixels = 0;
            $sampleCount = 0;
            $brightRedClusters = 0;
            $whiteTextPixels = 0;

            for ($y = $region['y']; $y < min($region['y'] + $region['h'], $height); $y += 3) {
                for ($x = $region['x']; $x < min($region['x'] + $region['w'], $width); $x += 3) {
                    $sampleCount++;
                    $rgb = imagecolorat($gd, $x, $y);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;

                    // Detect bright red/orange text (like phone numbers on cars)
                    // Must be VERY red and bright
                    if ($r > 200 && $g < 100 && $b < 100 && ($r - $g) > 120) {
                        $brightRedClusters++;
                        
                        // Check if it has sharp edges (text characteristic)
                        if ($x + 3 < $width) {
                            $rgb2 = imagecolorat($gd, $x + 3, $y);
                            $r2 = ($rgb2 >> 16) & 0xFF;
                            if (abs($r - $r2) > 100) {
                                $textLikePixels++;
                            }
                        }
                    }

                    // Detect white text with sharp edges
                    if ($r > 220 && $g > 220 && $b > 220) {
                        if ($x + 3 < $width && $y + 3 < $height) {
                            $rgb2 = imagecolorat($gd, $x + 3, $y);
                            $rgb3 = imagecolorat($gd, $x, $y + 3);
                            
                            $r2 = ($rgb2 >> 16) & 0xFF;
                            $r3 = ($rgb3 >> 16) & 0xFF;
                            
                            // Check for sharp contrast indicating text edges
                            if (abs($r - $r2) > 120 || abs($r - $r3) > 120) {
                                $whiteTextPixels++;
                            }
                        }
                    }
                }
            }

            $brightRedRatio = $sampleCount > 0 ? $brightRedClusters / $sampleCount : 0;
            $textLikeRatio = $sampleCount > 0 ? $textLikePixels / $sampleCount : 0;
            $whiteTextRatio = $sampleCount > 0 ? $whiteTextPixels / $sampleCount : 0;

            // Must have BOTH bright color AND text-like edges to be suspicious
            if (($brightRedRatio > 0.015 && $textLikeRatio > 0.01) || $whiteTextRatio > 0.02) {
                $suspiciousRegions++;
            }
        }

        // Need at least 2 regions with suspicious patterns
        return $suspiciousRegions >= 2 ? 1.0 : ($suspiciousRegions == 1 ? 0.5 : 0.0);
    } catch (\Exception $e) {
        Log::error("Color watermark detection error: " . $e->getMessage());
        return 0.0;
    }
}

protected function detectTextWatermark($image): float
{
    try {
        $testImage = clone $image;
        $testImage->resize(800, null, fn($c) => $c->aspectRatio()->upsize());
        
        // Try multiple preprocessing approaches
        $scores = [];
        
        // Approach 1: Standard greyscale with enhancement
        $grey1 = clone $testImage;
        $grey1->greyscale()->brightness(15)->contrast(25);
        $scores[] = $this->analyzeEdgeDensity($grey1);
        
        // Approach 2: Higher contrast for faint text
        $grey2 = clone $testImage;
        $grey2->greyscale()->contrast(40)->brightness(10);
        $scores[] = $this->analyzeEdgeDensity($grey2);

        return max($scores);
    } catch (\Exception $e) {
        Log::error("Text watermark detection error: " . $e->getMessage());
        return 0.0;
    }
}

protected function analyzeEdgeDensity($processedImage): float
{
    $gd = $processedImage->core()->native();
    $width = imagesx($gd);
    $height = imagesy($gd);

    $regions = [
        ['x' => 0, 'y' => 0, 'w' => $width * 0.25, 'h' => $height * 0.25],
        ['x' => $width * 0.75, 'y' => 0, 'w' => $width * 0.25, 'h' => $height * 0.25],
        ['x' => 0, 'y' => $height * 0.75, 'w' => $width * 0.25, 'h' => $height * 0.25],
        ['x' => $width * 0.75, 'y' => $height * 0.75, 'w' => $width * 0.25, 'h' => $height * 0.25],
        ['x' => $width * 0.4, 'y' => $height * 0.4, 'w' => $width * 0.2, 'h' => $height * 0.2],
    ];

    $highEdgeRegions = 0;

    foreach ($regions as $region) {
        $edgeCount = 0;
        $sampleCount = 0;

        // Sample more densely for better detection
        for ($y = $region['y']; $y < min($region['y'] + $region['h'], $height); $y += 2) {
            for ($x = $region['x']; $x < min($region['x'] + $region['w'], $width); $x += 2) {
                $sampleCount++;
                if ($x + 1 < $width && $y + 1 < $height) {
                    $color1 = imagecolorat($gd, $x, $y);
                    $color2 = imagecolorat($gd, $x + 1, $y);
                    $r1 = ($color1 >> 16) & 0xFF;
                    $r2 = ($color2 >> 16) & 0xFF;
                    
                    // Lower threshold to catch faint text
                    if (abs($r1 - $r2) > 30) {
                        $edgeCount++;
                    }
                }
            }
        }

        $edgeDensity = $sampleCount > 0 ? $edgeCount / $sampleCount : 0;

        if ($edgeDensity > 0.08) {
            $highEdgeRegions++;
        }
    }

    return $highEdgeRegions >= 2 ? 1.0 : ($highEdgeRegions == 1 ? 0.6 : 0.0);
}

protected function detectTransparentWatermark($image): float
{
    try {
        $mime = $image->origin()?->mimeType() ?? null;

        if ($mime !== 'image/png') {
            return 0.0;
        }

        $gd = $image->core()->native();
        $width = imagesx($gd);
        $height = imagesy($gd);

        $semiTransparent = 0;
        $total = 0;

        for ($y = 0; $y < $height; $y += 8) {
            for ($x = 0; $x < $width; $x += 8) {
                $total++;
                $color = imagecolorat($gd, $x, $y);
                $alpha = ($color & 0x7F000000) >> 24;

                if ($alpha > 10 && $alpha < 110) {
                    $semiTransparent++;
                }
            }
        }

        $ratio = $total > 0 ? $semiTransparent / $total : 0;

        return $ratio > 0.05 ? 1.0 : ($ratio > 0.03 ? 0.5 : 0.0);
    } catch (\Exception $e) {
        Log::error("Transparent watermark detection error: " . $e->getMessage());
        return 0.0;
    }
}

protected function detectPatternWatermark($image): float
{
    try {
        $testImage = clone $image;
        $testImage->resize(400, null, fn($c) => $c->aspectRatio()->upsize())->greyscale();

        $gd = $testImage->core()->native();
        $width = imagesx($gd);
        $height = imagesy($gd);

        $sectionSize = 35;
        $sections = [];
        $repeatedPatterns = 0;

        for ($y = 0; $y < $height - $sectionSize; $y += $sectionSize * 2) {
            for ($x = 0; $x < $width - $sectionSize; $x += $sectionSize * 2) {
                $hash = $this->getSectionHash($gd, $x, $y, $sectionSize);

                if (isset($sections[$hash])) {
                    $repeatedPatterns++;
                    if ($repeatedPatterns >= 2) {
                        return 1.0;
                    }
                }
                $sections[$hash] = true;
            }
        }

        return 0.0;
    } catch (\Exception $e) {
        Log::error("Pattern watermark detection error: " . $e->getMessage());
        return 0.0;
    }
}

    protected function getSectionHash($gd, $startX, $startY, $size): string
    {
        $hash = '';
        $step = 10;

        for ($y = $startY; $y < $startY + $size && $y < imagesy($gd); $y += $step) {
            for ($x = $startX; $x < $startX + $size && $x < imagesx($gd); $x += $step) {
                $color = imagecolorat($gd, $x, $y);
                $r = ($color >> 16) & 0xFF;
                $g = ($color >> 8) & 0xFF;
                $b = $color & 0xFF;
                $brightness = ($r + $g + $b) / 3;
                $hash .= $brightness > 128 ? '1' : '0';
            }
        }

        return md5($hash);
    }

    protected function deleteOriginalAfterConversions(Media $media): void
    {
        try {
            sleep(1);
            $path = $media->getPath();
            if (file_exists($path)) {
                unlink($path);
                //Log::info("Deleted original file to save space: {$path}");
            }
        } catch (\Exception $e) {
            Log::warning("Failed to delete original file for media ID {$media->id}: " . $e->getMessage());
        }
    }

    /**
     * Reorder images for any model
     */
    public function reorderImages(HasMedia $model, array $imageIds, string $collection = 'images'): bool
    {
        try {
            foreach ($imageIds as $position => $mediaId) {
                $media = $model->getMedia($collection)->where('id', $mediaId)->first();
                if ($media) {
                    $media->order_column = $position + 1;
                    $media->setCustomProperty('position', $position + 1);
                    $media->save();
                }
            }
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to reorder images for {$model->getMorphClass()} ID {$model->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete specific image
     */
    public function deleteImage(
        HasMedia $model,
        int $mediaId,
        string $collection = 'images',
        bool $reorderRemaining = true
    ): bool {
        try {
            $media = $model->getMedia($collection)->where('id', $mediaId)->first();

            if (!$media) {
                $media = Media::find($mediaId);

                if (!$media || $media->model_id != $model->id || $media->model_type != get_class($model) || $media->collection_name != $collection) {
                    Log::warning("Media ID {$mediaId} not found or doesn't belong to model {$model->getMorphClass()} ID {$model->id} in collection '{$collection}'");
                    return false;
                }
            }

            Log::info("Deleting media ID {$mediaId}: {$media->name} from collection '{$collection}'");

            $deleted = $media->delete();

            if (!$deleted) {
                Log::error("Failed to delete media record ID {$mediaId}");
                return false;
            }

            Log::info("Successfully deleted media ID {$mediaId}");

            if ($reorderRemaining) {
                $this->reorderRemainingImages($model, $collection);
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to delete image for {$model->getMorphClass()} ID {$model->id}: " . $e->getMessage(), [
                'media_id' => $mediaId,
                'collection' => $collection,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Delete multiple images by IDs
     */
    public function deleteMultipleImages(
        HasMedia $model,
        array $mediaIds,
        string $collection = 'images',
        bool $reorderRemaining = true
    ): array {
        $deleted = 0;
        $errors = [];

        foreach ($mediaIds as $mediaId) {
            if ($this->deleteImage($model, $mediaId, $collection, false)) {
                $deleted++;
            } else {
                $errors[] = "Failed to delete image ID: {$mediaId}";
            }
        }

        if ($reorderRemaining && $deleted > 0) {
            $this->reorderRemainingImages($model, $collection);
        }

        return [
            'deleted' => $deleted,
            'errors' => $errors
        ];
    }

    /**
     * Delete all images from collection
     */
    public function clearImages(HasMedia $model, string $collection = 'images'): bool
    {
        try {
            $model->clearMediaCollection($collection);
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to clear images for {$model->getMorphClass()} ID {$model->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all image URLs with different conversions
     */
    public function getImageUrls(
        HasMedia $model,
        string $collection = 'images',
        array $conversions = ['optimized', 'thumbnail']
    ): array {
        return $model->getMedia($collection)->map(function ($media) use ($conversions) {
            $urls = [
                'id' => $media->id,
                'original' => $this->getOriginalOrFallback($media),
                'position' => $media->getCustomProperty('position', $media->order_column),
                'name' => $media->getCustomProperty('original_name', $media->name),
            ];

            foreach ($conversions as $conversion) {
                $urls[$conversion] = $media->getUrl($conversion);
            }

            return $urls;
        })->sortBy('position')->values()->toArray();
    }

    /**
     * Get original URL or fallback to large conversion if original was deleted
     */
    protected function getOriginalOrFallback(Media $media): string
    {
        try {
            $originalPath = $media->getPath();

            if (!file_exists($originalPath)) {
                return $media->getUrl('large');
            }

            return $media->getUrl();
        } catch (\Exception $e) {
            return $media->getUrl('large');
        }
    }

    /**
     * Get first image URL
     */
    public function getFirstImageUrl(
        HasMedia $model,
        string $collection = 'images',
        string $conversion = 'optimized'
    ): ?string {
        $media = $model->getFirstMedia($collection);
        return $media ? $media->getUrl($conversion) : null;
    }

    /**
     * Add default image
     */
    public function addDefaultImage(
        HasMedia $model,
        string $imagePath,
        string $collection = 'images',
        array $customProperties = []
    ): ?Media {
        if (!file_exists($imagePath)) {
            Log::warning("Default image not found: {$imagePath}");
            return null;
        }

        try {
            $defaultProperties = array_merge(['position' => 1], $customProperties);

            $media = $model
                ->addMedia($imagePath)
                ->withCustomProperties($defaultProperties)
                ->usingName(basename($imagePath))
                ->usingFileName(basename($imagePath))
                ->toMediaCollection($collection);

            $media->order_column = $defaultProperties['position'];
            $media->save();

            return $media;
        } catch (\Exception $e) {
            Log::error("Failed to add default image for {$model->getMorphClass()} ID {$model->id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Reorder remaining images after deletion
     */
    protected function reorderRemainingImages(HasMedia $model, string $collection = 'images'): void
    {
        $images = $model->getMedia($collection)->sortBy('order_column');

        foreach ($images as $index => $media) {
            $media->order_column = $index + 1;
            $media->setCustomProperty('position', $index + 1);
            $media->save();
        }
    }

    /**
     * Replace all images (useful for updates)
     */
    public function replaceAllImages(
        Request $request,
        HasMedia $model,
        string $collection = 'images'
    ): array {
        return $this->handleImageUploads($request, $model, $collection, true);
    }

    /**
     * Force delete media record and files
     */
    public function forceDeleteMedia(int $mediaId): bool
    {
        try {
            $media = Media::find($mediaId);

            if (!$media) {
                Log::warning("Media ID {$mediaId} not found in database");
                return false;
            }

            Log::info("Force deleting media ID {$mediaId}: {$media->name}");
            $media->forceDelete();

            return true;
        } catch (\Exception $e) {
            Log::error("Exception during force delete of media ID {$mediaId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clean up orphaned media records
     */
    public function cleanupOrphanedMedia(HasMedia $model, string $collection = 'images'): array
    {
        $cleaned = [
            'deleted_records' => 0,
            'errors' => []
        ];

        try {
            $mediaItems = $model->getMedia($collection);

            foreach ($mediaItems as $media) {
                if (!file_exists($media->getPath()) &&
                    !file_exists($media->getPath('thumbnail')) &&
                    !file_exists($media->getPath('optimized')) &&
                    !file_exists($media->getPath('large'))) {

                    try {
                        $media->forceDelete();
                        $cleaned['deleted_records']++;
                        Log::info("Cleaned orphaned media ID {$media->id}");
                    } catch (\Exception $e) {
                        $cleaned['errors'][] = "Failed to delete orphaned record ID {$media->id}";
                    }
                }
            }

        } catch (\Exception $e) {
            $cleaned['errors'][] = "Exception during cleanup: " . $e->getMessage();
            Log::error("Cleanup exception: " . $e->getMessage());
        }

        return $cleaned;
    }

    /**
     * Check if model has images
     */
    public function hasImages(HasMedia $model, string $collection = 'images'): bool
    {
        return $model->hasMedia($collection);
    }

    /**
     * Clean up existing original files to save space
     */
    public function cleanupOriginalFiles(HasMedia $model, string $collection = 'images'): int
    {
        $deleted = 0;
        $media = $model->getMedia($collection);

        foreach ($media as $mediaItem) {
            try {
                $originalPath = $mediaItem->getPath();
                if (file_exists($originalPath)) {
                    $largeExists = file_exists($mediaItem->getPath('large'));
                    $optimizedExists = file_exists($mediaItem->getPath('optimized'));

                    if ($largeExists && $optimizedExists) {
                        unlink($originalPath);
                        $deleted++;
                        Log::info("Cleaned up original file: {$originalPath}");
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Failed to cleanup original file for media ID {$mediaItem->id}: " . $e->getMessage());
            }
        }

        return $deleted;
    }
}