<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Http\Request;

echo "==================================\n";
echo "Debug: Testing filled() method\n";
echo "==================================\n\n";

// Test how filled() works with different values
$testCases = [
    ['min' => 10000, 'max' => 500000],
    ['min' => '', 'max' => ''],
    ['min' => null, 'max' => null],
    ['min' => 0, 'max' => 0],
    [],
];

foreach ($testCases as $index => $data) {
    echo "Test Case " . ($index + 1) . ": " . json_encode($data) . "\n";

    $request = Request::create('/test', 'POST', $data);

    echo "  filled('min'): " . ($request->filled('min') ? 'TRUE' : 'FALSE') . "\n";
    echo "  filled('max'): " . ($request->filled('max') ? 'TRUE' : 'FALSE') . "\n";
    echo "  input('min'): " . var_export($request->input('min'), true) . "\n";
    echo "  input('max'): " . var_export($request->input('max'), true) . "\n";

    if ($request->filled('min')) {
        $minValue = (int) $request->input('min');
        echo "  Cast min to int: {$minValue}\n";
    }

    if ($request->filled('max')) {
        $maxValue = (int) $request->input('max');
        echo "  Cast max to int: {$maxValue}\n";
    }

    echo "\n";
}

echo "==================================\n";
echo "Debug: Test actual filter query\n";
echo "==================================\n\n";

use App\Models\Advert;

// Test with real query
$request = Request::create('/test', 'POST', ['min' => 10000, 'max' => 500000]);

$query = Advert::where('ad_status', 'active')->where('sold', 'No');

echo "Before filters: " . $query->count() . " adverts\n";

$queryWithFilters = clone $query;

if ($request->filled('min')) {
    echo "Applying min filter: >= " . $request->input('min') . "\n";
    $queryWithFilters->where('price', '>=', (int) $request->input('min'));
}

if ($request->filled('max')) {
    echo "Applying max filter: <= " . $request->input('max') . "\n";
    $queryWithFilters->where('price', '<=', (int) $request->input('max'));
}

$resultCount = $queryWithFilters->count();
echo "After filters: {$resultCount} adverts\n\n";

$samplePrices = $queryWithFilters->take(10)->pluck('price')->toArray();
echo "Sample prices: " . implode(', ', array_map(function($p) {
    return '₦' . number_format($p);
}, $samplePrices)) . "\n\n";

if (count($samplePrices) > 0) {
    $minPrice = min($samplePrices);
    $maxPrice = max($samplePrices);
    echo "Min price found: ₦" . number_format($minPrice) . "\n";
    echo "Max price found: ₦" . number_format($maxPrice) . "\n";
    echo "All within range: " . ($minPrice >= 10000 && $maxPrice <= 500000 ? "YES" : "NO") . "\n";
}

echo "\n==================================\n";
echo "Debug: SQL Query\n";
echo "==================================\n\n";

$query2 = Advert::where('ad_status', 'active')->where('sold', 'No');
$request2 = Request::create('/test', 'POST', ['min' => 10000, 'max' => 500000]);

if ($request2->filled('min')) {
    $query2->where('price', '>=', (int) $request2->input('min'));
}

if ($request2->filled('max')) {
    $query2->where('price', '<=', (int) $request2->input('max'));
}

echo "SQL: " . $query2->toSql() . "\n";
echo "Bindings: " . json_encode($query2->getBindings()) . "\n";
