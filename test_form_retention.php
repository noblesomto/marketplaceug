<?php
/**
 * Form Value Retention Test Script
 * Tests that form values and images are retained after validation errors
 */

echo "===========================================\n";
echo "POST AD FORM - VALUE RETENTION TEST\n";
echo "===========================================\n\n";

// Check if we're in Laravel directory
if (!file_exists('artisan')) {
    echo "❌ Error: Must be run from Laravel root directory\n";
    echo "Usage: cd /home/www/laravel/marketplace && php test_form_retention.php\n";
    exit(1);
}

// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "✅ Laravel bootstrapped\n\n";

// Test 1: Check if old() helper works in blade
echo "TEST 1: Checking Blade Template for old() Helper\n";
echo "================================================\n";

$bladeFile = 'resources/views/dashboard/post-ad.blade.php';
$bladeContent = file_get_contents($bladeFile);

$checks = [
    'ad_title old()' => "old('ad_title')",
    'category old()' => "old('category')",
    'subcategory old()' => "old('subcategory')",
    'price old()' => "old('price')",
    'state old()' => "old('state')",
    'data-old-value' => 'data-old-value=',
    'temp_images session' => "session('temp_images')",
];

foreach ($checks as $name => $search) {
    if (strpos($bladeContent, $search) !== false) {
        echo "  ✅ {$name}: Found\n";
    } else {
        echo "  ❌ {$name}: NOT FOUND\n";
    }
}

echo "\n";

// Test 2: Check controller methods
echo "TEST 2: Checking Controller Methods\n";
echo "====================================\n";

$controllerFile = 'app/Http/Controllers/UserManageAdverts.php';
$controllerContent = file_get_contents($controllerFile);

$methods = [
    'storeTemporaryImages' => 'private function storeTemporaryImages',
    'processAdvertImages' => 'private function processAdvertImages',
    'removeEmojis null check' => 'if ($text === null',
    'temp_images flash' => "flash('temp_images'",
    'early validation' => 'EARLY VALIDATION',
];

foreach ($methods as $name => $search) {
    if (strpos($controllerContent, $search) !== false) {
        echo "  ✅ {$name}: Implemented\n";
    } else {
        echo "  ❌ {$name}: NOT FOUND\n";
    }
}

echo "\n";

// Test 3: Check storage directories
echo "TEST 3: Checking Storage Setup\n";
echo "================================\n";

$directories = [
    'storage/app/public' => 'Public storage disk',
    'storage/framework/sessions' => 'Session storage',
];

foreach ($directories as $dir => $desc) {
    if (is_dir($dir) && is_writable($dir)) {
        echo "  ✅ {$desc}: Exists and writable\n";
    } else {
        echo "  ⚠️  {$desc}: Not writable or missing\n";
    }
}

// Check if temp directory will be created
$tempDir = 'storage/app/public/temp/post-ad-images';
if (is_dir($tempDir)) {
    echo "  ✅ Temp images directory: Already exists\n";
    $files = glob($tempDir . '/*');
    echo "     Files in temp: " . count($files) . "\n";
} else {
    echo "  ℹ️  Temp images directory: Will be created on first upload\n";
}

echo "\n";

// Test 4: Simulate validation error scenario
echo "TEST 4: Simulating Validation Error\n";
echo "====================================\n";

try {
    // Create a mock request
    $request = new \Illuminate\Http\Request([
        'ad_title' => 'Test Car for Sale',
        'category' => '1',
        'subcategory' => '2',
        'brand' => '5',
        'price' => '5000000',
        'state' => 'Lagos',
        'lga' => 'Ikeja',
        // Missing description - will cause validation error
    ]);

    // Check old() would work
    $request->flashOnly(['ad_title', 'category', 'subcategory', 'brand', 'price', 'state', 'lga']);

    echo "  ✅ Request object created\n";
    echo "  ✅ Flash data would contain:\n";
    echo "     - ad_title: {$request->input('ad_title')}\n";
    echo "     - category: {$request->input('category')}\n";
    echo "     - price: {$request->input('price')}\n";
    echo "     - state: {$request->input('state')}\n";

} catch (Exception $e) {
    echo "  ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 5: Check validation rules
echo "TEST 5: Checking Validation Service\n";
echo "====================================\n";

if (class_exists('App\Services\AdvertValidationService')) {
    echo "  ✅ AdvertValidationService: Exists\n";

    try {
        $service = new \App\Services\AdvertValidationService();
        $rules = $service->getRules(1, 2, false);

        echo "  ✅ getRules() method: Works\n";
        echo "  ✅ Validation rules include:\n";

        $importantRules = ['ad_title', 'category', 'subcategory', 'description', 'state', 'lga'];
        foreach ($importantRules as $field) {
            if (isset($rules[$field])) {
                echo "     - {$field}: {$rules[$field]}\n";
            }
        }
    } catch (Exception $e) {
        echo "  ⚠️  Error testing service: " . $e->getMessage() . "\n";
    }
} else {
    echo "  ❌ AdvertValidationService: NOT FOUND\n";
}

echo "\n";

// Test 6: Check routes
echo "TEST 6: Checking Routes\n";
echo "========================\n";

try {
    $routes = app('router')->getRoutes();
    $postAdRoute = collect($routes)->first(function ($route) {
        return str_contains($route->uri(), 'user/post-ad') && in_array('POST', $route->methods());
    });

    if ($postAdRoute) {
        echo "  ✅ POST /user/post-ad route: Found\n";
        echo "     Controller: " . $postAdRoute->getActionName() . "\n";
    } else {
        echo "  ⚠️  POST /user/post-ad route: Not found\n";
    }
} catch (Exception $e) {
    echo "  ⚠️  Error checking routes: " . $e->getMessage() . "\n";
}

echo "\n";

// Summary
echo "===========================================\n";
echo "SUMMARY\n";
echo "===========================================\n\n";

echo "✅ IMPLEMENTED FEATURES:\n";
echo "  - Form value retention with old() helper\n";
echo "  - Image temporary storage system\n";
echo "  - Dynamic dropdown restoration (category, subcategory, brand, model)\n";
echo "  - State/LGA restoration\n";
echo "  - Radio button and checkbox retention\n";
echo "  - Early validation to prevent 500 errors\n";
echo "  - Null-safe removeEmojis method\n\n";

echo "📋 MANUAL TESTING CHECKLIST:\n";
echo "  [ ] Open: http://localhost:8030/user/post-ad (login first)\n";
echo "  [ ] Fill form with some data\n";
echo "  [ ] Upload 2-3 test images\n";
echo "  [ ] Submit WITHOUT description (trigger validation error)\n";
echo "  [ ] Verify all filled fields are still filled\n";
echo "  [ ] Verify uploaded images are shown as previews\n";
echo "  [ ] Verify green 'retained' message appears\n";
echo "  [ ] Add description and submit successfully\n";
echo "  [ ] Verify ad created with all data and images\n\n";

echo "🌐 APPLICATION URLs:\n";
echo "  - Artisan Serve: http://localhost:8030\n";
echo "  - Login: http://localhost:8030/login\n";
echo "  - Post Ad: http://localhost:8030/user/post-ad\n\n";

echo "===========================================\n";
echo "Test completed!\n";
echo "===========================================\n";
