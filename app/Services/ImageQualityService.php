<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Intervention\Image\Laravel\Facades\Image;

/**
 * Image Quality Validation Service - TIER 1
 *
 * Provides basic image quality validation including:
 * - Resolution validation (minimum dimensions)
 * - File size validation
 * - Quality scoring
 * - Blur/sharpness detection
 * - Brightness analysis
 */
class ImageQualityService
{
    /**
     * Minimum dimensions (industry standard)
     */
    const MIN_WIDTH = 800;
    const MIN_HEIGHT = 600;

    /**
     * Recommended dimensions for best quality
     */
    const RECOMMENDED_WIDTH = 1200;
    const RECOMMENDED_HEIGHT = 900;

    /**
     * File size limits
     */
    const MIN_FILE_SIZE = 50 * 1024; // 50KB
    const MAX_FILE_SIZE = 20 * 1024 * 1024; // 20MB

    /**
     * Quality thresholds
     */
    const MIN_QUALITY_SCORE = 40; // Below this = reject
    const MIN_SHARPNESS = 25; // Below this = warn about blur
    const MIN_BRIGHTNESS = 30; // Below this = too dark
    const MAX_BRIGHTNESS = 220; // Above this = too bright

    /**
     * Validate image quality
     *
     * @param UploadedFile $file
     * @return array ['valid' => bool, 'errors' => array, 'warnings' => array, 'score' => int, 'details' => array]
     */
    public function validateImage(UploadedFile $file): array
    {
        $result = [
            'valid' => true,
            'errors' => [],
            'warnings' => [],
            'score' => 100,
            'details' => []
        ];

        try {
            // Check file size first (before loading image)
            $fileSize = $file->getSize();

            if ($fileSize < self::MIN_FILE_SIZE) {
                $result['valid'] = false;
                $result['errors'][] = sprintf(
                    'File too small (%s). Minimum is %s. This may indicate a low-quality or corrupted image.',
                    $this->formatFileSize($fileSize),
                    $this->formatFileSize(self::MIN_FILE_SIZE)
                );
            }

            if ($fileSize > self::MAX_FILE_SIZE) {
                $result['valid'] = false;
                $result['errors'][] = sprintf(
                    'File too large (%s). Maximum is %s. Please compress or resize your image.',
                    $this->formatFileSize($fileSize),
                    $this->formatFileSize(self::MAX_FILE_SIZE)
                );
            }

            // Load image
            $image = Image::read($file->getPathname());

            $width = $image->width();
            $height = $image->height();

            $result['details'] = [
                'width' => $width,
                'height' => $height,
                'size' => $fileSize,
                'mime' => $file->getMimeType(),
                'size_formatted' => $this->formatFileSize($fileSize),
            ];

            // Check minimum dimensions
            if ($width < self::MIN_WIDTH || $height < self::MIN_HEIGHT) {
                $result['valid'] = false;
                $result['errors'][] = sprintf(
                    'Image too small (%dx%dpx). Minimum required is %dx%dpx. Please use a higher resolution image.',
                    $width,
                    $height,
                    self::MIN_WIDTH,
                    self::MIN_HEIGHT
                );
                $result['score'] -= 50;
            }

            // Warn about recommended dimensions
            if ($result['valid'] && ($width < self::RECOMMENDED_WIDTH || $height < self::RECOMMENDED_HEIGHT)) {
                $result['warnings'][] = sprintf(
                    'For best results, use images at least %dx%dpx. Your image is %dx%dpx.',
                    self::RECOMMENDED_WIDTH,
                    self::RECOMMENDED_HEIGHT,
                    $width,
                    $height
                );
                $result['score'] -= 15;
            }

            // Calculate quality score
            $qualityScore = $this->calculateQualityScore($image, $fileSize);
            $result['details']['quality_score'] = $qualityScore;
            $result['score'] = min($result['score'], $qualityScore);

            // Check blur/sharpness (basic)
            $sharpness = $this->detectSharpness($image);
            $result['details']['sharpness'] = $sharpness;

            if ($sharpness < self::MIN_SHARPNESS) {
                $result['warnings'][] = sprintf(
                    'Image appears blurry (sharpness: %.1f/100). Please use a clearer, focused photo.',
                    $sharpness
                );
                $result['score'] -= 20;
            }

            // Check brightness
            $brightness = $this->detectBrightness($image);
            $result['details']['brightness'] = $brightness;

            if ($brightness < self::MIN_BRIGHTNESS) {
                $result['warnings'][] = sprintf(
                    'Image is too dark (brightness: %.1f/255). Please use better lighting or a brighter photo.',
                    $brightness
                );
                $result['score'] -= 15;
            } elseif ($brightness > self::MAX_BRIGHTNESS) {
                $result['warnings'][] = sprintf(
                    'Image is overexposed/too bright (brightness: %.1f/255). Please use a less bright photo.',
                    $brightness
                );
                $result['score'] -= 10;
            }

            // Final quality check
            if ($result['score'] < self::MIN_QUALITY_SCORE) {
                $result['valid'] = false;
                $result['errors'][] = sprintf(
                    'Overall image quality is too low (score: %d/100). Please use a higher quality photo.',
                    $result['score']
                );
            }

            \Log::info('Image quality validation', [
                'filename' => $file->getClientOriginalName(),
                'valid' => $result['valid'],
                'score' => $result['score'],
                'dimensions' => $width . 'x' . $height,
                'sharpness' => $sharpness,
                'brightness' => $brightness
            ]);

        } catch (\Exception $e) {
            $result['valid'] = false;
            $result['errors'][] = 'Failed to process image. The file may be corrupted or in an unsupported format.';
            \Log::error('Image validation failed', [
                'filename' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $result;
    }

    /**
     * Calculate overall quality score (0-100)
     * Based on resolution, file size, and compression ratio
     */
    protected function calculateQualityScore($image, int $fileSize): int
    {
        $score = 100;

        $width = $image->width();
        $height = $image->height();
        $pixels = $width * $height;

        // Penalize low resolution
        $recommendedPixels = self::RECOMMENDED_WIDTH * self::RECOMMENDED_HEIGHT;
        if ($pixels < $recommendedPixels) {
            $ratio = $pixels / $recommendedPixels;
            $score *= $ratio;
        }

        // Check compression ratio (bytes per pixel)
        $bytesPerPixel = $fileSize / $pixels;

        // Over-compressed images (< 0.5 bytes/pixel for JPEG)
        if ($bytesPerPixel < 0.5) {
            $score *= 0.75; // Heavily compressed, likely poor quality
        } elseif ($bytesPerPixel < 1.0) {
            $score *= 0.9; // Moderately compressed
        }

        // Very large files might be unoptimized but not necessarily bad quality
        if ($fileSize > 10 * 1024 * 1024) {
            $score *= 0.95;
        }

        return (int) round(max(0, $score));
    }

    /**
     * Detect image sharpness using Laplacian variance method
     * Returns value 0-100 (higher = sharper)
     */
    protected function detectSharpness($image): float
    {
        try {
            // Clone and resize for faster processing
            $gray = clone $image;
            $gray->greyscale();
            $gray->scale(width: 200);

            $width = $gray->width();
            $height = $gray->height();

            // Calculate Laplacian variance (edge detection)
            $laplacian = 0;
            $count = 0;

            // Sample pixels (not every pixel for performance)
            for ($y = 1; $y < $height - 1; $y += 2) {
                for ($x = 1; $x < $width - 1; $x += 2) {
                    try {
                        $center = $gray->pickColor($x, $y)->toArray()[0];
                        $top = $gray->pickColor($x, $y - 1)->toArray()[0];
                        $bottom = $gray->pickColor($x, $y + 1)->toArray()[0];
                        $left = $gray->pickColor($x - 1, $y)->toArray()[0];
                        $right = $gray->pickColor($x + 1, $y)->toArray()[0];

                        // Laplacian operator
                        $lap = abs(4 * $center - $top - $bottom - $left - $right);
                        $laplacian += $lap;
                        $count++;
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }

            // Calculate variance
            $variance = $count > 0 ? $laplacian / $count : 0;

            // Normalize to 0-100 scale (empirical values)
            $sharpness = min(100, $variance * 1.5);

            return round($sharpness, 2);

        } catch (\Exception $e) {
            \Log::warning('Sharpness detection failed: ' . $e->getMessage());
            return 50; // Default neutral value
        }
    }

    /**
     * Detect average brightness (0-255)
     * 0 = black, 255 = white, ~128 = ideal
     */
    protected function detectBrightness($image): float
    {
        try {
            $gray = clone $image;
            $gray->greyscale();
            $gray->scale(width: 100);

            $width = $gray->width();
            $height = $gray->height();

            $totalBrightness = 0;
            $count = 0;

            // Sample pixels
            for ($y = 0; $y < $height; $y += 2) {
                for ($x = 0; $x < $width; $x += 2) {
                    try {
                        $color = $gray->pickColor($x, $y)->toArray();
                        $totalBrightness += $color[0]; // R value (same as G and B in grayscale)
                        $count++;
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }

            $averageBrightness = $count > 0 ? $totalBrightness / $count : 128;

            return round($averageBrightness, 2);

        } catch (\Exception $e) {
            \Log::warning('Brightness detection failed: ' . $e->getMessage());
            return 128; // Default neutral value
        }
    }

    /**
     * Format file size for human-readable display
     */
    protected function formatFileSize(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        if ($bytes < 1024 * 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }

        return round($bytes / (1024 * 1024), 2) . ' MB';
    }

    /**
     * Get user-friendly quality rating
     */
    public function getQualityRating(int $score): string
    {
        if ($score >= 80) return 'Excellent';
        if ($score >= 60) return 'Good';
        if ($score >= 40) return 'Acceptable';
        return 'Poor';
    }

    /**
     * Get recommended actions based on validation result
     */
    public function getRecommendations(array $validationResult): array
    {
        $recommendations = [];

        if (!$validationResult['valid']) {
            $recommendations[] = 'Use your phone camera to take a fresh photo instead of using screenshots or downloaded images.';
        }

        $details = $validationResult['details'] ?? [];

        // Resolution recommendations
        if (isset($details['width']) && $details['width'] < self::RECOMMENDED_WIDTH) {
            $recommendations[] = 'Take photos with your phone camera at the highest quality setting.';
        }

        // Sharpness recommendations
        if (isset($details['sharpness']) && $details['sharpness'] < 40) {
            $recommendations[] = 'Make sure your camera lens is clean and the subject is in focus before taking the photo.';
            $recommendations[] = 'Hold your phone steady or use a tripod to avoid motion blur.';
        }

        // Brightness recommendations
        if (isset($details['brightness']) && $details['brightness'] < 60) {
            $recommendations[] = 'Take photos in better lighting - natural daylight works best.';
            $recommendations[] = 'Avoid taking photos in dim or dark environments.';
        }

        return $recommendations;
    }
}
