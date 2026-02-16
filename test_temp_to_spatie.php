<?php
/**
 * Test Script: Verify Temp Images to Spatie Media Library Flow
 *
 * This script simulates the form submission process with validation errors
 * and verifies that temp images are properly processed through Spatie.
 *
 * Run: php test_temp_to_spatie.php
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Advert;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║  TEMP IMAGES → SPATIE MEDIA LIBRARY TEST                  ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

// Get a test user
$user = User::first();
if (!$user) {
    echo "❌ No users found. Please create a user first.\n";
    exit(1);
}

echo "✅ Using test user: {$user->name} (ID: {$user->user_id})\n\n";

// Step 1: Check temp folder
echo "═══════════════════════════════════════════════════════════\n";
echo "STEP 1: Check Temp Image Folder\n";
echo "═══════════════════════════════════════════════════════════\n";

$tempPath = 'temp/post-ad-images';
if (Storage::disk('public')->exists($tempPath)) {
    $tempFiles = Storage::disk('public')->allFiles($tempPath);
    echo "📁 Temp folder exists: storage/app/public/{$tempPath}\n";
    echo "📊 Temp files count: " . count($tempFiles) . "\n";

    if (count($tempFiles) > 0) {
        echo "\n🔍 Recent temp files:\n";
        foreach (array_slice($tempFiles, 0, 5) as $file) {
            $size = Storage::disk('public')->size($file);
            $time = Storage::disk('public')->lastModified($file);
            echo "   • {$file}\n";
            echo "     Size: " . round($size / 1024, 2) . " KB\n";
            echo "     Modified: " . date('Y-m-d H:i:s', $time) . "\n";
        }
    }
} else {
    echo "⚠️  Temp folder doesn't exist yet (will be created on first upload)\n";
}

echo "\n";

// Step 2: Check most recent advert
echo "═══════════════════════════════════════════════════════════\n";
echo "STEP 2: Check Most Recent Advert\n";
echo "═══════════════════════════════════════════════════════════\n";

$latestAdvert = Advert::latest('id')->first();

if ($latestAdvert) {
    echo "📋 Latest Advert:\n";
    echo "   ID: {$latestAdvert->id}\n";
    echo "   Title: {$latestAdvert->ad_title}\n";
    echo "   Ad ID: {$latestAdvert->ad_id}\n";
    echo "   Category: {$latestAdvert->category}\n";
    echo "   Created: {$latestAdvert->created_at}\n\n";

    // Check Spatie media
    echo "🖼️  Spatie Media Library:\n";
    $mediaCollection = $latestAdvert->getMedia('images');

    if ($mediaCollection->count() > 0) {
        echo "   ✅ Has {$mediaCollection->count()} images in Spatie\n\n";

        foreach ($mediaCollection as $index => $media) {
            echo "   Image " . ($index + 1) . ":\n";
            echo "     • ID: {$media->id}\n";
            echo "     • File: {$media->file_name}\n";
            echo "     • Size: " . round($media->size / 1024, 2) . " KB\n";
            echo "     • Collection: {$media->collection_name}\n";
            $position = $media->getCustomProperty('position');
            echo "     • Position: " . ($position ? $position : 'N/A') . "\n";

            // Check conversions
            $conversions = $media->getGeneratedConversions();
            echo "     • Conversions: " . implode(', ', array_keys($conversions->toArray())) . "\n";

            // Check URLs
            echo "     • Original URL: {$media->getUrl()}\n";
            if ($media->hasGeneratedConversion('optimized')) {
                echo "     • Optimized URL: {$media->getUrl('optimized')}\n";
            }
            echo "\n";
        }

        // Check main ad_image field
        echo "   Main Image (ad_image field):\n";
        $adImageValue = $latestAdvert->ad_image ? $latestAdvert->ad_image : 'NULL';
        echo "     • Value: " . $adImageValue . "\n\n";

    } else {
        echo "   ⚠️  No images in Spatie Media Library\n";
        echo "   💡 This advert might use the old AdvertImage system\n\n";

        // Check old AdvertImage table
        $advertImages = \App\Models\AdvertImage::where('advert_id', $latestAdvert->ad_id)->get();
        if ($advertImages->count() > 0) {
            echo "   📸 Found {$advertImages->count()} images in old AdvertImage table:\n";
            foreach ($advertImages as $img) {
                echo "     • {$img->image}\n";
            }
            echo "\n   ⚠️  WARNING: Using old image system, not Spatie!\n\n";
        }
    }
} else {
    echo "⚠️  No adverts found in database\n\n";
}

// Step 3: Check Spatie media table
echo "═══════════════════════════════════════════════════════════\n";
echo "STEP 3: Check Spatie Media Table\n";
echo "═══════════════════════════════════════════════════════════\n";

$totalMedia = \Spatie\MediaLibrary\MediaCollections\Models\Media::where('model_type', 'App\\Models\\Advert')
    ->where('collection_name', 'images')
    ->count();

echo "📊 Total Spatie media records for Adverts: {$totalMedia}\n";

$recentMedia = \Spatie\MediaLibrary\MediaCollections\Models\Media::where('model_type', 'App\\Models\\Advert')
    ->where('collection_name', 'images')
    ->latest('created_at')
    ->limit(5)
    ->get();

if ($recentMedia->count() > 0) {
    echo "\n🔍 Recent media uploads:\n";
    foreach ($recentMedia as $media) {
        $advert = Advert::find($media->model_id);
        $advertTitle = $advert ? $advert->ad_title : 'N/A';
        echo "   • Media ID {$media->id} → Advert '{$advertTitle}'\n";
        echo "     File: {$media->file_name}\n";
        echo "     Created: {$media->created_at}\n";
        $source = $media->getCustomProperty('source');
        echo "     Source: " . ($source ? $source : 'unknown') . "\n\n";
    }
}

echo "\n";

// Step 4: File system check
echo "═══════════════════════════════════════════════════════════\n";
echo "STEP 4: Check Spatie Storage Directory\n";
echo "═══════════════════════════════════════════════════════════\n";

$mediaPath = storage_path('app/public/media');
if (is_dir($mediaPath)) {
    echo "📁 Spatie media directory exists: {$mediaPath}\n";

    // Count directories (each advert gets a directory)
    $advertDirs = array_filter(glob($mediaPath . '/*'), 'is_dir');
    echo "📊 Advert directories: " . count($advertDirs) . "\n\n";

    // Show recent directories
    if (count($advertDirs) > 0) {
        echo "🔍 Recent advert directories:\n";
        $recentDirs = array_slice($advertDirs, -5);
        foreach ($recentDirs as $dir) {
            $dirName = basename($dir);
            $files = glob($dir . '/*');
            $conversionDir = $dir . '/conversions';
            $conversions = is_dir($conversionDir) ? count(glob($conversionDir . '/*')) : 0;

            echo "   • Directory: {$dirName}\n";
            echo "     Files: " . count($files) . "\n";
            echo "     Conversions: {$conversions}\n";

            // Show files
            foreach (glob($dir . '/*.webp') as $file) {
                $fileName = basename($file);
                $fileSize = round(filesize($file) / 1024, 2);
                echo "       - {$fileName} ({$fileSize} KB)\n";
            }
            echo "\n";
        }
    }
} else {
    echo "⚠️  Spatie media directory doesn't exist yet\n";
}

echo "\n";

// Step 5: Summary and Instructions
echo "═══════════════════════════════════════════════════════════\n";
echo "MANUAL TEST INSTRUCTIONS\n";
echo "═══════════════════════════════════════════════════════════\n\n";

echo "📝 To test the complete flow:\n\n";

echo "1️⃣  FIRST SUBMISSION (with validation error):\n";
echo "   • Go to: http://localhost:8030/user/post-ad\n";
echo "   • Select Category: Vehicles\n";
echo "   • Select Subcategory: Cars\n";
echo "   • Upload 2-3 images\n";
echo "   • Fill some fields but LEAVE DESCRIPTION EMPTY\n";
echo "   • Click Submit\n";
echo "   • Expected: Validation error\n\n";

echo "2️⃣  CHECK TEMP IMAGES:\n";
echo "   • Run: ls -la storage/app/public/temp/post-ad-images/\n";
echo "   • Expected: See temp image files\n";
echo "   • Check form: Should display temp images with green notice\n\n";

echo "3️⃣  SECOND SUBMISSION (fix and submit):\n";
echo "   • Fill in the description field\n";
echo "   • DO NOT upload new images\n";
echo "   • Click Submit\n";
echo "   • Expected: Success, redirect to my ads\n\n";

echo "4️⃣  VERIFY SPATIE PROCESSING:\n";
echo "   • Run this script again: php test_temp_to_spatie.php\n";
echo "   • Check latest advert has Spatie media\n";
echo "   • Verify temp files were deleted\n";
echo "   • Check conversions were generated\n\n";

echo "5️⃣  DATABASE CHECK:\n";
echo "   SELECT * FROM media WHERE model_type = 'App\\\\Models\\\\Advert'\n";
echo "   ORDER BY created_at DESC LIMIT 5;\n\n";

echo "═══════════════════════════════════════════════════════════\n";
echo "TEST SCRIPT COMPLETE\n";
echo "═══════════════════════════════════════════════════════════\n\n";
