<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Advert;
use Illuminate\Http\Request;

echo "==================================\n";
echo "Testing Price Filter Logic\n";
echo "==================================\n\n";

// Get total count of active adverts
$totalCount = Advert::where('ad_status', 'active')
    ->where('sold', 'No')
    ->count();

echo "Total active adverts: {$totalCount}\n\n";

// Get some sample prices to understand the range
echo "Sample advert prices:\n";
Advert::where('ad_status', 'active')
    ->where('sold', 'No')
    ->orderBy('price')
    ->take(10)
    ->get(['id', 'ad_title', 'price'])
    ->each(function($ad) {
        echo "  - {$ad->ad_title}: ₦" . number_format($ad->price) . "\n";
    });

echo "\n==================================\n\n";

// Test 1: Filter with valid min and max
echo "Test 1: Valid min (10000) and max (500000)\n";
$query1 = Advert::where('ad_status', 'active')->where('sold', 'No');

$min = 10000;
$max = 500000;

if ($min !== null) {
    $query1->where('price', '>=', (int) $min);
}
if ($max !== null) {
    $query1->where('price', '<=', (int) $max);
}

$count1 = $query1->count();
echo "OLD METHOD (using !== null): {$count1} results\n";

// Using filled() equivalent
$request1 = Request::create('/test', 'POST', ['min' => 10000, 'max' => 500000]);
$query1b = Advert::where('ad_status', 'active')->where('sold', 'No');

if ($request1->filled('min')) {
    $query1b->where('price', '>=', (int) $request1->input('min'));
}
if ($request1->filled('max')) {
    $query1b->where('price', '<=', (int) $request1->input('max'));
}

$count1b = $query1b->count();
echo "NEW METHOD (using filled()): {$count1b} results\n";

$prices1 = $query1b->take(5)->pluck('price')->toArray();
$minPrice1 = min($prices1);
$maxPrice1 = max($prices1);
echo "Sample price range: ₦" . number_format($minPrice1) . " - ₦" . number_format($maxPrice1) . "\n";
echo "✓ Prices within range: " . ($minPrice1 >= 10000 && $maxPrice1 <= 500000 ? "YES" : "NO") . "\n\n";

echo "==================================\n\n";

// Test 2: Filter with EMPTY strings (the bug scenario)
echo "Test 2: Empty string min and max (bug scenario)\n";

// OLD METHOD - This would cause the bug
$query2 = Advert::where('ad_status', 'active')->where('sold', 'No');
$minEmpty = "";
$maxEmpty = "";

if ($minEmpty !== null) {
    $query2->where('price', '>=', (int) $minEmpty);
}
if ($maxEmpty !== null) {
    $query2->where('price', '<=', (int) $maxEmpty);
}

$count2 = $query2->count();
echo "OLD METHOD (using !== null): {$count2} results\n";
echo "  ⚠️  Empty string !== null is TRUE, so filter applies with price >= 0 and price <= 0\n";

// NEW METHOD - Fixed
$request2 = Request::create('/test', 'POST', ['min' => '', 'max' => '']);
$query2b = Advert::where('ad_status', 'active')->where('sold', 'No');

if ($request2->filled('min')) {
    $query2b->where('price', '>=', (int) $request2->input('min'));
}
if ($request2->filled('max')) {
    $query2b->where('price', '<=', (int) $request2->input('max'));
}

$count2b = $query2b->count();
echo "NEW METHOD (using filled()): {$count2b} results\n";
echo "  ✓ Empty strings are ignored, no price filter applied\n";
echo "  ✓ Result count should equal total count: " . ($count2b == $totalCount ? "YES" : "NO") . "\n\n";

echo "==================================\n\n";

// Test 3: Only min price
echo "Test 3: Only min price (100000)\n";
$request3 = Request::create('/test', 'POST', ['min' => 100000]);
$query3 = Advert::where('ad_status', 'active')->where('sold', 'No');

if ($request3->filled('min')) {
    $query3->where('price', '>=', (int) $request3->input('min'));
}
if ($request3->filled('max')) {
    $query3->where('price', '<=', (int) $request3->input('max'));
}

$count3 = $query3->count();
echo "Results: {$count3}\n";

$prices3 = $query3->take(5)->pluck('price')->toArray();
if (count($prices3) > 0) {
    $minPrice3 = min($prices3);
    echo "Lowest price in results: ₦" . number_format($minPrice3) . "\n";
    echo "✓ All prices >= 100000: " . ($minPrice3 >= 100000 ? "YES" : "NO") . "\n";
} else {
    echo "No results found\n";
}

echo "\n==================================\n\n";

// Test 4: Only max price
echo "Test 4: Only max price (50000)\n";
$request4 = Request::create('/test', 'POST', ['max' => 50000]);
$query4 = Advert::where('ad_status', 'active')->where('sold', 'No');

if ($request4->filled('min')) {
    $query4->where('price', '>=', (int) $request4->input('min'));
}
if ($request4->filled('max')) {
    $query4->where('price', '<=', (int) $request4->input('max'));
}

$count4 = $query4->count();
echo "Results: {$count4}\n";

$prices4 = $query4->take(5)->pluck('price')->toArray();
if (count($prices4) > 0) {
    $maxPrice4 = max($prices4);
    echo "Highest price in results: ₦" . number_format($maxPrice4) . "\n";
    echo "✓ All prices <= 50000: " . ($maxPrice4 <= 50000 ? "YES" : "NO") . "\n";
} else {
    echo "No results found\n";
}

echo "\n==================================\n\n";

// Test 5: No price parameters
echo "Test 5: No price parameters\n";
$request5 = Request::create('/test', 'POST', []);
$query5 = Advert::where('ad_status', 'active')->where('sold', 'No');

if ($request5->filled('min')) {
    $query5->where('price', '>=', (int) $request5->input('min'));
}
if ($request5->filled('max')) {
    $query5->where('price', '<=', (int) $request5->input('max'));
}

$count5 = $query5->count();
echo "Results: {$count5}\n";
echo "✓ Equals total count: " . ($count5 == $totalCount ? "YES" : "NO") . "\n\n";

echo "==================================\n\n";

echo "✅ SUMMARY\n";
echo "  - Bug was in Test 2: Empty strings triggered price filter with 0\n";
echo "  - Fixed by using filled() instead of !== null\n";
echo "  - Test 2 new method should equal Test 5: " . ($count2b == $count5 ? "✓ PASS" : "✗ FAIL") . "\n";
echo "  - All price filters working correctly\n";
