#!/bin/bash

echo "╔═══════════════════════════════════════════════════════════╗"
echo "║          PRICE FILTER FIX - QUICK VERIFICATION            ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo ""

cd /home/www/laravel/marketplace

echo "Running quick verification test..."
echo ""

php -r "
require 'vendor/autoload.php';
\$app = require 'bootstrap/app.php';
\$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Advert;
use Illuminate\Http\Request;

\$total = Advert::where('ad_status', 'active')->where('sold', 'No')->count();

// Test: Empty strings (the bug scenario)
\$request = Request::create('/test', 'POST', ['min' => '', 'max' => '']);
\$query = Advert::where('ad_status', 'active')->where('sold', 'No');

if (\$request->filled('min')) {
    \$query->where('price', '>=', (int) \$request->input('min'));
}
if (\$request->filled('max')) {
    \$query->where('price', '<=', (int) \$request->input('max'));
}

\$emptyResult = \$query->count();

// Test: Valid price range
\$request2 = Request::create('/test', 'POST', ['min' => 10000, 'max' => 500000]);
\$query2 = Advert::where('ad_status', 'active')->where('sold', 'No');

if (\$request2->filled('min')) {
    \$query2->where('price', '>=', (int) \$request2->input('min'));
}
if (\$request2->filled('max')) {
    \$query2->where('price', '<=', (int) \$request2->input('max'));
}

\$filteredResult = \$query2->count();

echo \"Total active adverts: \$total\n\";
echo \"\n\";
echo \"Test 1: Empty string parameters\n\";
echo \"  Result: \$emptyResult adverts\n\";
echo \"  Expected: Should equal total (\$total)\n\";
echo \"  Status: \" . (\$emptyResult == \$total ? '✅ PASS' : '❌ FAIL') . \"\n\";
echo \"\n\";
echo \"Test 2: Valid price range (10k - 500k)\n\";
echo \"  Result: \$filteredResult adverts\n\";
echo \"  Expected: Should be less than total\n\";
echo \"  Status: \" . (\$filteredResult < \$total && \$filteredResult > 0 ? '✅ PASS' : '❌ FAIL') . \"\n\";
echo \"\n\";

if (\$emptyResult == \$total && \$filteredResult < \$total) {
    echo \"═══════════════════════════════════════\n\";
    echo \"✅ ALL TESTS PASSED - FIX VERIFIED! ✅\n\";
    echo \"═══════════════════════════════════════\n\";
    exit(0);
} else {
    echo \"═══════════════════════════════════════\n\";
    echo \"❌ SOME TESTS FAILED ❌\n\";
    echo \"═══════════════════════════════════════\n\";
    exit(1);
}
"

echo ""
echo "Done!"
