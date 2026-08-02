# Uganda Localization + Flutterwave Migration Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Relocalize MarketplaceUG from Nigeria (Naira, Nigerian states/LGAs, Paystack, Agility Logistics) to Uganda (UGX, Uganda regions/districts, Flutterwave, flat per-district shipping fees).

**Architecture:** No SDK dependency for either payment gateway — both are hand-rolled `Http::withToken(...)` REST calls, so the gateway swap is controller-for-controller with no client-side JS to touch. Locations reuse the existing `states`/`lgas` tables and `State`/`Lga` models (repointed with Uganda data, not renamed) to minimize churn in a single-country app. Currency introduces one `money()` helper that all ~130 hardcoded `₦`/`number_format()` call sites route through. This is pre-launch — no production data — so seeders truncate-and-replace rather than migrate.

**Tech Stack:** Laravel (PHP), Blade views, PHPUnit (`tests/Unit`, `tests/Feature`), MySQL, Flutterwave REST API v3 (`https://api.flutterwave.com/v3`), Laravel `Http` facade (Guzzle-backed, already a dependency — no new package needed).

## Global Constraints

- Currency displays as **"UGX"** (code, not "USh" or "₦"), zero decimal places by default.
- Uganda locations are **two-level: Region → District** (no city sub-level) — 4 regions, ~133 districts.
- Flutterwave **fully replaces** Paystack — no dual-gateway support, remove Paystack code rather than leaving it dormant.
- Seller payouts support **both bank transfer and mobile money** (MTN/Airtel).
- Shipping cost is a **flat, admin-configurable fee per district** — no third-party courier API.
- Site branding name is **"Marketplace Uganda"**.
- This is pre-launch: DB seeders may truncate and replace Nigerian data outright; no migration-of-existing-rows logic needed.
- Every task that touches a `*.php` file with existing PHPUnit coverage must run `php artisan test --filter=<relevant>` (or the full suite if scoped narrowly) before committing.

---

## Phase 1 — Currency

### Task 1: Currency config + `money()` helper

**Files:**
- Create: `config/currency.php`
- Modify: `app/Helpers/helpers.php` (already a Composer `files`-autoloaded helper file — no `composer.json` change needed)
- Test: `tests/Unit/Helpers/MoneyHelperTest.php`

**Interfaces:**
- Produces: `money($amount, int $decimals = 0): string` — global helper, e.g. `money(150000)` → `"UGX 150,000"`, `money(150000.5, 2)` → `"UGX 150,000.50"`.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Unit\Helpers;

use Tests\TestCase;

class MoneyHelperTest extends TestCase
{
    public function test_money_formats_whole_number_with_ugx_prefix(): void
    {
        $this->assertSame('UGX 150,000', money(150000));
    }

    public function test_money_formats_with_decimals(): void
    {
        $this->assertSame('UGX 150,000.50', money(150000.5, 2));
    }

    public function test_money_handles_zero(): void
    {
        $this->assertSame('UGX 0', money(0));
    }

    public function test_money_handles_string_numeric_input(): void
    {
        $this->assertSame('UGX 1,200', money('1200'));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Unit/Helpers/MoneyHelperTest.php`
Expected: FAIL with "Call to undefined function money()"

- [ ] **Step 3: Add config file and helper**

`config/currency.php`:
```php
<?php

return [
    'code' => env('CURRENCY_CODE', 'UGX'),
    'symbol' => env('CURRENCY_SYMBOL', 'UGX'),
];
```

Append to `app/Helpers/helpers.php`:
```php
if (! function_exists('money')) {
    function money($amount, int $decimals = 0): string
    {
        return config('currency.symbol') . ' ' . number_format((float) $amount, $decimals, '.', ',');
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Unit/Helpers/MoneyHelperTest.php`
Expected: PASS (4 tests)

- [ ] **Step 5: Commit**

```bash
git add config/currency.php app/Helpers/helpers.php tests/Unit/Helpers/MoneyHelperTest.php
git commit -m "feat: add UGX currency config and money() helper"
```

---

### Task 2: Sweep mechanical ₦+number_format price displays to money()

**Files:**
- Modify: all `resources/views/**/*.blade.php` files containing `₦{{ number_format(...) }}` or `₦ {{ number_format(...) }}`, plus `app/Jobs/SendFollowerPushNotification.php`
- Create (temporary, delete after use): `scripts/sweep-currency.php`

**Interfaces:**
- Consumes: `money($amount, int $decimals = 0)` from Task 1.

This task is mechanical and uniform — every occurrence of the pattern `₦` immediately followed (allowing an optional space) by `{{ number_format($EXPR, $DECIMALS, '.', ',') }}` becomes `{{ money($EXPR, $DECIMALS) }}`. Rather than hand-editing ~100+ known occurrences (list gathered via grep, but blade files churn — re-deriving the exact current set at execution time is safer than trusting a possibly-stale list), run a script that finds and transforms every live occurrence.

- [ ] **Step 1: Write the sweep script**

`scripts/sweep-currency.php`:
```php
<?php

$root = __DIR__ . '/..';
$dirs = [$root . '/resources/views'];
$pattern = '/₦\s*\{\{\s*number_format\(([^,]+),\s*(\d+)(?:,\s*\'\.\',\s*\',\')?\)\s*\}\}/';

$changed = [];

foreach ($dirs as $dir) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        $path = $file->getPathname();
        $contents = file_get_contents($path);
        $updated = preg_replace_callback($pattern, function ($m) {
            $expr = trim($m[1]);
            $decimals = trim($m[2]);
            return "{{ money({$expr}, {$decimals}) }}";
        }, $contents, -1, $count);

        if ($count > 0) {
            file_put_contents($path, $updated);
            $changed[$path] = $count;
        }
    }
}

foreach ($changed as $path => $count) {
    echo "{$path}: {$count} replacement(s)\n";
}

echo count($changed) . " file(s) changed.\n";
```

- [ ] **Step 2: Run the script**

Run: `php scripts/sweep-currency.php`
Expected: prints a list of changed blade files with replacement counts (the exact list depends on the repo's current state — verify it includes at minimum `advert-card.blade.php`, `advert-list.blade.php`, `chat.blade.php`, `order-details.blade.php`, `payments.blade.php`, `make-payment.blade.php`, `buy-direct-payment.blade.php`, admin adboost/payments/settlements views).

- [ ] **Step 3: Manually fix `SendFollowerPushNotification::formatPrice`** (not a blade file, script above only targets `resources/views`)

In `app/Jobs/SendFollowerPushNotification.php`, replace:
```php
        return '₦' . number_format($price, 0);
```
with:
```php
        return money($price);
```

- [ ] **Step 4: Verify no mechanical pattern remains**

Run: `grep -rn '₦\s*{{\s*number_format' resources/views/`
Expected: no output (empty)

- [ ] **Step 5: Delete the sweep script and commit**

```bash
rm scripts/sweep-currency.php
git add -A
git commit -m "feat: sweep hardcoded Naira price displays to money() helper"
```

---

### Task 3: Fix non-mechanical currency call sites

**Files:**
- Modify: `resources/views/user/chat.blade.php` (lines ~34, ~267, ~364, ~378, ~394, ~404, ~506)
- Modify: `resources/views/user/post-boost-ad.blade.php` (lines ~183-187)
- Modify: `resources/views/public/pages/payments-refunds.blade.php` (line ~207)
- Modify: `app/Helpers/ContentHelper.php:104`

**Interfaces:**
- Consumes: `money()` from Task 1 (server-rendered spots only — JS spots use a plain string prefix since `money()` is a PHP helper).

Task 2's script only matches the `₦{{ number_format(...) }}` blade pattern. These sites use `₦` as a bare symbol in server-rendered spans, or inside JavaScript template strings, which need manual handling.

- [ ] **Step 1: Fix `chat.blade.php` server-rendered price display (line ~34)**

Replace:
```blade
₦{{ number_format((float) preg_replace('/[^\d.]/', '', $advert->price), 0) }}
```
with:
```blade
{{ money((float) preg_replace('/[^\d.]/', '', $advert->price), 0) }}
```

- [ ] **Step 2: Fix `chat.blade.php` static currency symbol span (line ~267)**

Replace:
```blade
<span class="px-3 py-2 bg-gray-50 text-gray-500 border-r border-gray-300 text-sm">₦</span>
```
with:
```blade
<span class="px-3 py-2 bg-gray-50 text-gray-500 border-r border-gray-300 text-sm">UGX</span>
```

- [ ] **Step 3: Fix `chat.blade.php` JS `fmtNaira` usages (lines ~364, ~378, ~394, ~404, ~506)**

Rename the function `fmtNaira` → `fmtMoney` at its definition (line ~364) and at every call site. Replace every occurrence of the template-literal pattern `` `I'd like to offer ₦${fmtNaira(amountInput.value)}` `` with `` `I'd like to offer UGX ${fmtMoney(amountInput.value)}` `` (3 occurrences: lines ~378, ~394, ~404), and the line ~506 usage `!message.includes(fmtNaira(amountInput.value))` → `!message.includes(fmtMoney(amountInput.value))` (keep the message-building line above it consistent with the same `UGX ${fmtMoney(...)}` swap).

- [ ] **Step 4: Fix `post-boost-ad.blade.php` JS price strings (lines ~183-187)**

Replace:
```js
      let priceText = '₦' + Math.round(finalPrice).toLocaleString();

      if (discountPercentage > 0) {
        const basePrice = parseFloat(data.data.pricing.base_price);
        priceText += ' <span class="text-sm text-gray-600">(Save ₦' +
                     Math.round(basePrice - finalPrice).toLocaleString() + ')</span>';
      }
```
with:
```js
      let priceText = 'UGX ' + Math.round(finalPrice).toLocaleString();

      if (discountPercentage > 0) {
        const basePrice = parseFloat(data.data.pricing.base_price);
        priceText += ' <span class="text-sm text-gray-600">(Save UGX ' +
                     Math.round(basePrice - finalPrice).toLocaleString() + ')</span>';
      }
```

- [ ] **Step 5: Fix currency-mention copy in `payments-refunds.blade.php`**

Replace the line containing `All charges are in Nigerian Naira (₦)` with `All charges are in Ugandan Shillings (UGX)`.

- [ ] **Step 6: Remove `₦` from the sanitizer whitelist in `ContentHelper.php:104`**

Find the regex whitelist (`[^\p{L}\p{N}\s\-.,!?$€£¥₦&@()\'\"\/]` or similar) and remove `₦` from the allowed-character set.

- [ ] **Step 7: Verify no `₦` remains anywhere in the codebase**

Run: `grep -rn '₦' app/ resources/`
Expected: no output (empty)

- [ ] **Step 8: Commit**

```bash
git add resources/views/user/chat.blade.php resources/views/user/post-boost-ad.blade.php resources/views/public/pages/payments-refunds.blade.php app/Helpers/ContentHelper.php
git commit -m "fix: remove remaining hardcoded Naira symbol from chat, boost, and content sanitizer"
```

---

### Task 4: Replace Naira salary-range option lists with UGX bands

**Files:**
- Modify: `resources/views/user/post-ad.blade.php` (lines ~517, ~535)
- Modify: `resources/views/user/edit-ad.blade.php` (lines ~426, ~435)

**Interfaces:** none (self-contained view data).

These are literal `<select>` option value lists stored verbatim on `adverts.salary`/`adverts.expected_salary` when a user posts a job ad — not a display-formatting concern, so `money()` doesn't apply. Since this is pre-launch, replacing the values outright is safe (confirmed no live data depends on the old Naira bands).

- [ ] **Step 1: Replace `post-ad.blade.php` salary ranges (line ~517)**

Replace:
```php
$salaryRanges = ['Commission', 'Below ₦20,000', '₦20,000 - ₦40,000', '₦40,000 - ₦60,000', '₦60,000 - ₦80,000', '₦80,000 - ₦100,000', '₦100,000 - ₦120,000', '₦120,000 - ₦140,000', '₦140,000 - ₦160,000', '₦160,000 - ₦180,000', '₦180,000 - ₦200,000', '₦200,000 - ₦220,000', '₦220,000 - ₦250,000', '₦250,000 - ₦300,000', '₦300,000 - ₦350,000', '₦350,000 - ₦400,000', '₦400,000 - ₦450,000', '₦450,000 - ₦500,000', 'Above ₦500,000'];
```
with:
```php
$salaryRanges = ['Commission', 'Below UGX 200,000', 'UGX 200,000 - UGX 400,000', 'UGX 400,000 - UGX 600,000', 'UGX 600,000 - UGX 800,000', 'UGX 800,000 - UGX 1,000,000', 'UGX 1,000,000 - UGX 1,200,000', 'UGX 1,200,000 - UGX 1,400,000', 'UGX 1,400,000 - UGX 1,600,000', 'UGX 1,600,000 - UGX 1,800,000', 'UGX 1,800,000 - UGX 2,000,000', 'UGX 2,000,000 - UGX 2,200,000', 'UGX 2,200,000 - UGX 2,500,000', 'UGX 2,500,000 - UGX 3,000,000', 'UGX 3,000,000 - UGX 3,500,000', 'UGX 3,500,000 - UGX 4,000,000', 'UGX 4,000,000 - UGX 4,500,000', 'UGX 4,500,000 - UGX 5,000,000', 'Above UGX 5,000,000'];
```

- [ ] **Step 2: Replace `post-ad.blade.php` expected-salary ranges (line ~535)**

Replace:
```php
$expectedSalaryRanges = ['Below ₦50,000', '₦50,000 - ₦75,000', '₦75,000 - ₦100,000', '₦100,000 - ₦120,000', '₦120,000 - ₦140,000', '₦140,000 - ₦160,000', '₦160,000 - ₦180,000', '₦180,000 - ₦200,000', '₦200,000 - ₦220,000', '₦220,000 - ₦250,000', '₦250,000 - ₦300,000', '₦300,000 - ₦350,000', '₦350,000 - ₦400,000', '₦400,000 - ₦450,000', '₦450,000 - ₦500,000', 'Above ₦500,000'];
```
with:
```php
$expectedSalaryRanges = ['Below UGX 500,000', 'UGX 500,000 - UGX 750,000', 'UGX 750,000 - UGX 1,000,000', 'UGX 1,000,000 - UGX 1,200,000', 'UGX 1,200,000 - UGX 1,400,000', 'UGX 1,400,000 - UGX 1,600,000', 'UGX 1,600,000 - UGX 1,800,000', 'UGX 1,800,000 - UGX 2,000,000', 'UGX 2,000,000 - UGX 2,200,000', 'UGX 2,200,000 - UGX 2,500,000', 'UGX 2,500,000 - UGX 3,000,000', 'UGX 3,000,000 - UGX 3,500,000', 'UGX 3,500,000 - UGX 4,000,000', 'UGX 4,000,000 - UGX 4,500,000', 'UGX 4,500,000 - UGX 5,000,000', 'Above UGX 5,000,000'];
```

- [ ] **Step 3: Replace `edit-ad.blade.php` salary array (line ~426)**

Replace:
```php
@foreach(['Commission', 'Below ₦20,000', '₦20,000 - ₦40,000', 'Above ₦500,000'] as $sal)
```
with:
```php
@foreach(['Commission', 'Below UGX 200,000', 'UGX 200,000 - UGX 400,000', 'Above UGX 5,000,000'] as $sal)
```

- [ ] **Step 4: Replace `edit-ad.blade.php` expected-salary array (line ~435)**

Replace:
```php
@foreach(['Below ₦50,000', '₦50,000 - ₦75,000', 'Above ₦500,000'] as $expSal)
```
with:
```php
@foreach(['Below UGX 500,000', 'UGX 500,000 - UGX 750,000', 'Above UGX 5,000,000'] as $expSal)
```

- [ ] **Step 5: Manually verify in browser**

Run the app (`php artisan serve`), open the post-ad form, toggle to a job category, confirm both salary dropdowns show UGX-labeled options with no `₦` remaining.

- [ ] **Step 6: Commit**

```bash
git add resources/views/user/post-ad.blade.php resources/views/user/edit-ad.blade.php
git commit -m "feat: replace Naira salary-range options with UGX bands"
```

---

### Task 5: Update price-range filter buckets to UGX thresholds

**Files:**
- Modify: `app/Services/FilterService.php:77-98`
- Modify: `app/Http/Controllers/Shop/SearchFilter.php` (the `PRICE_RANGE_SLUGS` const)
- Modify: `app/Http/Controllers/Api/SearchController.php` (the `price_ranges` array)
- Modify: `tests/Unit/Services/FilterServiceTest.php`

**Interfaces:**
- Produces: `FilterService::applyPriceRange(Builder $query, string $range): Builder` — same signature, same range-key set (`under_20k`, `20k_120k`, `120k_1m`, `1m_10m`, `above_10m` kept as opaque internal identifiers, only the underlying thresholds and display labels change).

The range keys (`under_20k` etc.) are internal filter codes referenced by URL query params and the label maps below — kept unchanged to avoid touching every consumer; only the numeric thresholds (Naira-scale) and their user-facing labels (still saying "20k"/"120k" etc., which would now be misleading) change.

- [ ] **Step 1: Write the failing test for the new threshold**

Add to `tests/Unit/Services/FilterServiceTest.php`, replacing `test_apply_price_filters_range_under_20k`:

```php
    public function test_apply_price_filters_range_under_20k_uses_ugx_threshold(): void
    {
        $request = Request::create('/', 'GET', ['range' => 'under_20k']);
        $query   = Advert::query();

        $this->service->applyPriceFilters($query, $request);

        $sql = $query->toSql();
        $bindings = $query->getBindings();
        $this->assertStringContainsString('price', $sql);
        $this->assertContains(100000, $bindings);
    }
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=test_apply_price_filters_range_under_20k_uses_ugx_threshold`
Expected: FAIL (binding is 20000, not 100000)

- [ ] **Step 3: Update `FilterService::applyPriceRange` thresholds**

Replace the `switch` body in `app/Services/FilterService.php:79-94` with:
```php
        switch ($range) {
            case 'under_20k':
                $query->where('price', '<', 100000);
                break;
            case '20k_120k':
                $query->whereBetween('price', [100000, 500000]);
                break;
            case '120k_1m':
                $query->whereBetween('price', [500000, 2000000]);
                break;
            case '1m_10m':
                $query->whereBetween('price', [2000000, 20000000]);
                break;
            case 'above_10m':
                $query->where('price', '>', 20000000);
                break;
        }
```

- [ ] **Step 4: Update `SearchFilter.php` `PRICE_RANGE_SLUGS` labels**

Replace:
```php
    private const PRICE_RANGE_SLUGS = [
        'under-20k' => ['key' => 'under_20k', 'label' => 'Under ₦20,000'],
        '20k-120k'  => ['key' => '20k_120k',  'label' => '₦20,000 - ₦120,000'],
        '120k-1m'   => ['key' => '120k_1m',   'label' => '₦120,000 - ₦1 Million'],
        '1m-10m'    => ['key' => '1m_10m',    'label' => '₦1 Million - ₦10 Million'],
        'above-10m' => ['key' => 'above_10m', 'label' => 'Above ₦10 Million'],
    ];
```
with:
```php
    private const PRICE_RANGE_SLUGS = [
        'under-100k' => ['key' => 'under_20k', 'label' => 'Under UGX 100,000'],
        '100k-500k'  => ['key' => '20k_120k',  'label' => 'UGX 100,000 - UGX 500,000'],
        '500k-2m'    => ['key' => '120k_1m',   'label' => 'UGX 500,000 - UGX 2 Million'],
        '2m-20m'     => ['key' => '1m_10m',    'label' => 'UGX 2 Million - UGX 20 Million'],
        'above-20m'  => ['key' => 'above_10m', 'label' => 'Above UGX 20 Million'],
    ];
```

- [ ] **Step 5: Update `Api/SearchController.php` `price_ranges` array**

Replace:
```php
                'price_ranges' => [
                    ['value' => 'under_20k', 'label' => 'Under ₦20,000'],
                    ['value' => '20k_120k', 'label' => '₦20,000 - ₦120,000'],
                    ['value' => '120k_1m', 'label' => '₦120,000 - ₦1,000,000'],
                    ['value' => '1m_10m', 'label' => '₦1,000,000 - ₦10,000,000'],
                    ['value' => 'above_10m', 'label' => 'Above ₦10,000,000'],
                ],
```
with:
```php
                'price_ranges' => [
                    ['value' => 'under_20k', 'label' => 'Under UGX 100,000'],
                    ['value' => '20k_120k', 'label' => 'UGX 100,000 - UGX 500,000'],
                    ['value' => '120k_1m', 'label' => 'UGX 500,000 - UGX 2,000,000'],
                    ['value' => '1m_10m', 'label' => 'UGX 2,000,000 - UGX 20,000,000'],
                    ['value' => 'above_10m', 'label' => 'Above UGX 20,000,000'],
                ],
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test --filter=FilterServiceTest`
Expected: PASS (all FilterService tests, including the updated one)

- [ ] **Step 7: Run the full test suite to catch any other regression**

Run: `php artisan test`
Expected: PASS (no new failures vs. baseline before this task)

- [ ] **Step 8: Commit**

```bash
git add app/Services/FilterService.php app/Http/Controllers/Shop/SearchFilter.php app/Http/Controllers/Api/SearchController.php tests/Unit/Services/FilterServiceTest.php
git commit -m "feat: rescale price-range filter buckets to UGX thresholds"
```

---

## Phase 2 — Locations (Uganda regions/districts)

### Task 6: Uganda region seeder

**Files:**
- Modify: `database/seeders/StateSeeder.php`

**Interfaces:**
- Produces: `states` table rows — `id, name, slug`, 4 rows (Central, Eastern, Northern, Western). `station_id` column is not in the `states` migration schema (dead `$fillable` entry on the `State` model, pre-existing — leave as-is, don't populate it).

- [ ] **Step 1: Replace the seeder's data set**

Rewrite `database/seeders/StateSeeder.php` to truncate `states` and insert exactly:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StateSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('states')->truncate();

        $regions = ['Central', 'Eastern', 'Northern', 'Western'];

        foreach ($regions as $index => $name) {
            DB::table('states')->insert([
                'id' => $index + 1,
                'name' => $name,
                'slug' => Str::slug($name),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
```

(Keep the class name `StateSeeder` — referenced by `DatabaseSeeder` and any `--class=StateSeeder` calls; only the data changes.)

- [ ] **Step 2: Run the seeder against a test/local DB**

Run: `php artisan db:seed --class=StateSeeder`
Expected: `states` table has exactly 4 rows: Central, Eastern, Northern, Western.

- [ ] **Step 3: Verify**

Run: `php artisan tinker --execute="echo App\Models\State::count();"`
Expected: `4`

- [ ] **Step 4: Commit**

```bash
git add database/seeders/StateSeeder.php
git commit -m "feat: seed Uganda's 4 regions in place of Nigerian states"
```

---

### Task 7: Uganda district seeder

**Files:**
- Modify: `database/seeders/LgaSeeder.php`

**Interfaces:**
- Consumes: `states` table rows from Task 6 (region `id`s 1=Central, 2=Eastern, 3=Northern, 4=Western).
- Produces: `lgas` table rows — `state_id, name, slug`, ~133 rows.

- [ ] **Step 1: Replace the seeder's data set**

Rewrite `database/seeders/LgaSeeder.php` to truncate `lgas` and insert Uganda's districts keyed by region id, following the existing seeder's structure (keyed array → slugify → insert):

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LgaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('lgas')->truncate();

        // Region ids from StateSeeder: 1=Central, 2=Eastern, 3=Northern, 4=Western
        $data = [
            1 => ['Buikwe', 'Bukomansimbi', 'Butambala', 'Buvuma', 'Gomba', 'Kalangala', 'Kalungu', 'Kampala', 'Kassanda', 'Kayunga', 'Kiboga', 'Kyankwanzi', 'Kyotera', 'Luwero', 'Lwengo', 'Lyantonde', 'Masaka', 'Mityana', 'Mpigi', 'Mubende', 'Mukono', 'Nakaseke', 'Nakasongola', 'Rakai', 'Sembabule', 'Wakiso'],
            2 => ['Amuria', 'Budaka', 'Bududa', 'Bugiri', 'Bugweri', 'Bukedea', 'Bukwa', 'Bulambuli', 'Busia', 'Butaleja', 'Butebo', 'Buyende', 'Iganga', 'Jinja', 'Kaberamaido', 'Kalaki', 'Kamuli', 'Kapchorwa', 'Kapelebyong', 'Kibuku', 'Kumi', 'Kween', 'Luuka', 'Manafwa', 'Mayuge', 'Mbale', 'Namayingo', 'Namisindwa', 'Namutumba', 'Ngora', 'Pallisa', 'Serere', 'Sironko', 'Soroti', 'Tororo'],
            3 => ['Abim', 'Adjumani', 'Agago', 'Alebtong', 'Amolatar', 'Amudat', 'Amuru', 'Apac', 'Arua', 'Dokolo', 'Gulu', 'Kaabong', 'Kitgum', 'Koboko', 'Kole', 'Kotido', 'Kwania', 'Lamwo', 'Lira', 'Madi-Okollo', 'Maracha', 'Moroto', 'Moyo', 'Nabilatuk', 'Nakapiripirit', 'Napak', 'Nebbi', 'Nwoya', 'Obongi', 'Omoro', 'Otuke', 'Oyam', 'Pader', 'Pakwach', 'Terego', 'Yumbe', 'Zombo'],
            4 => ['Buhweju', 'Buliisa', 'Bundibugyo', 'Bunyangabu', 'Bushenyi', 'Hoima', 'Ibanda', 'Isingiro', 'Kabale', 'Kabarole', 'Kagadi', 'Kakumiro', 'Kamwenge', 'Kanungu', 'Kasese', 'Kazo', 'Kibaale', 'Kikuube', 'Kiruhura', 'Kiryandongo', 'Kisoro', 'Kitagwenda', 'Kyegegwa', 'Kyenjojo', 'Mbarara', 'Mitooma', 'Ntoroko', 'Ntungamo', 'Rubanda', 'Rubirizi', 'Rukiga', 'Rukungiri', 'Rwampara', 'Sheema'],
        ];

        foreach ($data as $stateId => $districts) {
            foreach ($districts as $name) {
                DB::table('lgas')->insert([
                    'state_id' => $stateId,
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
```

**Note:** this district list was compiled from general knowledge, not cross-checked against a live UBOS/Ministry of Local Government source. Uganda periodically creates new districts by Act of Parliament — flag to the user that this list should be verified against an authoritative source before production launch, but it's a reasonable, usable starting set for development/staging.

- [ ] **Step 2: Run both seeders together (order matters — states before lgas for the FK)**

Run: `php artisan db:seed --class=StateSeeder && php artisan db:seed --class=LgaSeeder`
Expected: no FK errors, `lgas` table has 133 rows.

- [ ] **Step 3: Verify region→district relationship**

Run: `php artisan tinker --execute="echo App\Models\State::where('name','Central')->first()->lgas()->count();"`
Expected: `26`

- [ ] **Step 4: Commit**

```bash
git add database/seeders/LgaSeeder.php
git commit -m "feat: seed Uganda's ~133 districts in place of Nigerian LGAs"
```

---

### Task 8: Add `shipping_fee` to districts, retire GigLogistic-as-city model

**Files:**
- Create: migration `database/migrations/2026_08_02_000001_add_shipping_fee_to_lgas_table.php`
- Modify: `app/Models/Lga.php`
- Test: `tests/Unit/Models/LgaTest.php`

**Interfaces:**
- Produces: `lgas.shipping_fee` (decimal, nullable, default `0`) and `Lga::shipping_fee` as a fillable attribute.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Unit\Models;

use App\Models\Lga;
use App\Models\State;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LgaTest extends TestCase
{
    use RefreshDatabase;

    public function test_shipping_fee_is_fillable_and_defaults_to_zero(): void
    {
        $state = State::create(['name' => 'Central', 'slug' => 'central']);
        $district = Lga::create(['state_id' => $state->id, 'name' => 'Kampala', 'slug' => 'kampala']);

        $this->assertEquals(0, $district->fresh()->shipping_fee);
    }

    public function test_shipping_fee_can_be_set(): void
    {
        $state = State::create(['name' => 'Central', 'slug' => 'central']);
        $district = Lga::create(['state_id' => $state->id, 'name' => 'Kampala', 'slug' => 'kampala', 'shipping_fee' => 15000]);

        $this->assertEquals(15000, $district->fresh()->shipping_fee);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Unit/Models/LgaTest.php`
Expected: FAIL (`shipping_fee` column doesn't exist / not fillable)

- [ ] **Step 3: Create the migration**

`database/migrations/2026_08_02_000001_add_shipping_fee_to_lgas_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lgas', function (Blueprint $table) {
            $table->decimal('shipping_fee', 10, 2)->default(0)->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('lgas', function (Blueprint $table) {
            $table->dropColumn('shipping_fee');
        });
    }
};
```

- [ ] **Step 4: Update `Lga` model**

In `app/Models/Lga.php`, change:
```php
    protected $fillable = ['state_id', 'name', 'slug'];
```
to:
```php
    protected $fillable = ['state_id', 'name', 'slug', 'shipping_fee'];
```

- [ ] **Step 5: Run migration and test**

Run: `php artisan migrate && php artisan test tests/Unit/Models/LgaTest.php`
Expected: migration runs clean, both tests PASS.

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_08_02_000001_add_shipping_fee_to_lgas_table.php app/Models/Lga.php tests/Unit/Models/LgaTest.php
git commit -m "feat: add shipping_fee column to districts for flat-rate shipping"
```

---

### Task 9: Retire Agility integration

**Files:**
- Modify: `app/Http/Controllers/Shop/LocationController.php`
- Modify: `app/Http/Controllers/Api/LocationController.php`
- Modify: `config/services.php:39-46` (remove `agility` block)
- Modify: `.env.example:68-74` (remove `AGILITY_*` keys)

**Interfaces:**
- Produces: `LocationController::calculateShippingCost($request)` (Api) now returns a fee looked up from `Lga::shipping_fee` instead of calling the Agility API — same route/response contract (`{ shipping_cost: <number> }`), different implementation.

- [ ] **Step 1: Remove Agility-calling methods from `Shop/LocationController.php`**

Delete `getAgilityToken()`, `buildShippingPayload()`, `makeAgilityApiRequest()`, `getSenderLocation()`, `getRecieverLocation()`, and `getAgilityShippingCost()`/`calculateShippingCost()` in their entirety.

- [ ] **Step 2: Remove the same methods from `Api/LocationController.php`**

Same deletions as Step 1.

- [ ] **Step 3: Replace `Api/LocationController::calculateShippingCost` with a district-fee lookup**

Add back a `calculateShippingCost(Request $request)` method with this implementation (adjust the request-input field name to match whatever the route currently expects — verify against the route's existing form/JSON field, likely `district_id` or `city` per the request payload used by `buy-direct-payment.blade.php`'s hidden `state`/`city` inputs):

```php
    public function calculateShippingCost(Request $request)
    {
        $request->validate([
            'district_id' => 'required|exists:lgas,id',
        ]);

        $district = \App\Models\Lga::findOrFail($request->input('district_id'));

        return response()->json([
            'success' => true,
            'shipping_cost' => (float) $district->shipping_fee,
        ]);
    }
```

- [ ] **Step 4: Remove the `agility` config block**

Delete lines 39-46 from `config/services.php`:
```php
    'agility' => [
        'url' => env('AGILITY_URL', 'https://thirdpartynode.theagilitysystems.com/price'),
        'email' => env('AGILITY_EMAIL'),
        'password' => env('AGILITY_PASSWORD'),
        'customer_code' => env('AGILITY_CUSTOMER_CODE', 'IND1875642'),
        'vehicle_type' => env('AGILITY_VEHICLE_TYPE', 3),
        'default_weight' => env('AGILITY_DEFAULT_WEIGHT', 5),
    ],
```

- [ ] **Step 5: Remove `AGILITY_*` keys from `.env.example`**

Delete the 6 `AGILITY_*` lines (63-74 range, adjust after Task 12 also edits nearby Paystack lines — do this task first or re-check line numbers before deleting).

- [ ] **Step 6: Search for any remaining references**

Run: `grep -rn "Agility\|agility" app/ config/ .env.example`
Expected: no output (empty)

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/Shop/LocationController.php app/Http/Controllers/Api/LocationController.php config/services.php .env.example
git commit -m "refactor: retire Agility Logistics integration, replace with flat district shipping fee"
```

---

### Task 10: Wire district shipping-fee lookup into checkout flow

**Files:**
- Modify: whichever controller currently builds the `$shipping` array consumed by `resources/views/public/buy-direct-payment.blade.php` (the view references `$shipping['shipping_cost']`, `$shipping['reciever_city']`, `$shipping['reciever_state']` — locate via `grep -rn "reciever_city\|shipping_cost" app/Http/Controllers/` to find the exact controller/method before editing)
- Modify: `resources/views/public/buy-direct-payment.blade.php:118-119` (hidden `city`/`state` inputs)

**Interfaces:**
- Consumes: `Lga::shipping_fee` from Task 8.

- [ ] **Step 1: Locate the controller building the buy-direct shipping context**

Run: `grep -rln "reciever_city\|reciever_state\|shipping_cost" app/Http/Controllers/`

- [ ] **Step 2: Replace the Agility/GigLogistic-based shipping cost computation**

In the located method, replace whatever call previously produced `shipping_cost` (an Agility API call or a `GigLogistic`-based lookup) with:
```php
$district = \App\Models\Lga::findOrFail($request->input('district_id'));
$shippingCost = (float) $district->shipping_fee;
```
and pass `$shippingCost` into the `$shipping['shipping_cost']` key as before. Replace `$shipping['reciever_city']`/`$shipping['reciever_state']` (previously `GigLogistic`/`State` records) with the `$district` (Lga) record and its `state` relation, keeping the same array keys so the blade view's `$shipping['reciever_city']->id` / `$shipping['reciever_state']->id` references keep working unchanged.

- [ ] **Step 3: Update the blade hidden inputs if the underlying id source changed**

In `buy-direct-payment.blade.php:118-119`, confirm `{{ $shipping['reciever_city']->id }}` and `{{ $shipping['reciever_state']->id }}` still resolve correctly against the new `Lga`/`State` records (district id and region id respectively) — no edit needed if Step 2 kept the same array shape.

- [ ] **Step 4: Manually test the buy-direct flow end to end**

Run the app, open an advert with buy-direct enabled, select a district, confirm the shipping cost shown matches that district's `shipping_fee` and the checkout total adds up correctly.

- [ ] **Step 5: Commit**

```bash
git add -A
git commit -m "feat: compute buy-direct shipping cost from district flat fee"
```

---

### Task 11: Uganda phone number validation

**Files:**
- Create: `app/Rules/UgandanPhoneNumber.php`
- Modify: `app/Http/Controllers/Auth/AccountController.php:550`
- Modify: `app/Http/Requests/User/UpdateProfileRequest.php:28`
- Modify: `app/Http/Controllers/Api/AccountController.php:342`
- Modify: `app/Http/Controllers/User/UserProfile.php:192`
- Modify: `app/Http/Controllers/Api/UserProfile.php:289`
- Delete: `app/Rules/NigerianPhoneNumber.php`
- Test: `tests/Unit/Rules/UgandanPhoneNumberTest.php`

**Interfaces:**
- Produces: `new UgandanPhoneNumber()` — a Laravel validation rule object, drop-in replacement for `new NigerianPhoneNumber()`.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Unit\Rules;

use App\Rules\UgandanPhoneNumber;
use Tests\TestCase;

class UgandanPhoneNumberTest extends TestCase
{
    /** @dataProvider validNumbers */
    public function test_passes_valid_ugandan_numbers(string $number): void
    {
        $rule = new UgandanPhoneNumber();
        $failed = false;
        $rule->validate('phone', $number, function () use (&$failed) { $failed = true; });
        $this->assertFalse($failed);
    }

    public static function validNumbers(): array
    {
        return [
            ['0700123456'],
            ['0771234567'],
            ['0751234567'],
        ];
    }

    /** @dataProvider invalidNumbers */
    public function test_fails_invalid_numbers(string $number): void
    {
        $rule = new UgandanPhoneNumber();
        $failed = false;
        $rule->validate('phone', $number, function () use (&$failed) { $failed = true; });
        $this->assertTrue($failed);
    }

    public static function invalidNumbers(): array
    {
        return [
            ['08034814561'],   // Nigerian format
            ['070012345'],     // too short
            ['07001234567'],   // too long
            ['1234567890'],    // no leading 0
        ];
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Unit/Rules/UgandanPhoneNumberTest.php`
Expected: FAIL (class doesn't exist)

- [ ] **Step 3: Create the rule**

`app/Rules/UgandanPhoneNumber.php` (mirrors `NigerianPhoneNumber`'s structure — `implements ValidationRule`, `0` + one of Uganda's mobile network prefixes `7[0-9]` + 7 more digits = 10 digits total):

```php
<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UgandanPhoneNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! preg_match('/^07[0-9]{8}$/', (string) $value)) {
            $fail('The :attribute must be a valid Ugandan mobile number (e.g. 07XXXXXXXX).');
        }
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Unit/Rules/UgandanPhoneNumberTest.php`
Expected: PASS (6 tests)

- [ ] **Step 5: Swap the rule at all 5 usage sites**

In each of `AccountController.php:550`, `UpdateProfileRequest.php:28`, `Api/AccountController.php:342`, `UserProfile.php:192`, `Api/UserProfile.php:289`: replace `new NigerianPhoneNumber()` with `new UgandanPhoneNumber()`, and update the corresponding `use App\Rules\NigerianPhoneNumber;` import to `use App\Rules\UgandanPhoneNumber;`.

- [ ] **Step 6: Delete the old rule**

```bash
rm app/Rules/NigerianPhoneNumber.php
```

- [ ] **Step 7: Verify no remaining references**

Run: `grep -rn "NigerianPhoneNumber" app/`
Expected: no output (empty)

- [ ] **Step 8: Run the full test suite**

Run: `php artisan test`
Expected: PASS (no regressions)

- [ ] **Step 9: Commit**

```bash
git add app/Rules/UgandanPhoneNumber.php tests/Unit/Rules/UgandanPhoneNumberTest.php app/Http/Controllers/Auth/AccountController.php app/Http/Requests/User/UpdateProfileRequest.php app/Http/Controllers/Api/AccountController.php app/Http/Controllers/User/UserProfile.php app/Http/Controllers/Api/UserProfile.php
git rm app/Rules/NigerianPhoneNumber.php
git commit -m "feat: replace Nigerian phone validation with Ugandan mobile number rule"
```

---

### Task 12: Admin district shipping-fee CRUD screen

**Files:**
- Create: `app/Http/Controllers/Admin/DistrictShippingController.php`
- Create: `resources/views/admin/settings/shipping/districts.blade.php`
- Modify: `routes/admin.php` (add routes — check existing admin route file name/group pattern by reading `routes/admin.php` first)
- Delete: `resources/views/admin/settings/gig/locations.blade.php` and its controller methods (find via `grep -rn "gig-locations\|update-gig-location" routes/`)

**Interfaces:**
- Produces: `GET /admin/settings/shipping` (list districts + fees), `POST /admin/settings/shipping/{lga}` (update one district's `shipping_fee`).

- [ ] **Step 1: Locate the existing GIG-locations route/controller to replace**

Run: `grep -rn "gig-locations\|update-gig-location\|GigLogistic" routes/admin.php app/Http/Controllers/Admin/`

- [ ] **Step 2: Create the controller**

`app/Http/Controllers/Admin/DistrictShippingController.php`:
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lga;
use App\Models\State;
use Illuminate\Http\Request;

class DistrictShippingController extends Controller
{
    public function index()
    {
        $title = 'District Shipping Fees | ' . config('global.site_name');
        $regions = State::with(['lgas' => fn ($q) => $q->orderBy('name')])->orderBy('name')->get();

        return view('admin.settings.shipping.districts', compact('title', 'regions'));
    }

    public function update(Request $request, Lga $lga)
    {
        $request->validate([
            'shipping_fee' => 'required|numeric|min:0',
        ]);

        $lga->update(['shipping_fee' => $request->input('shipping_fee')]);

        return redirect()->back()->with('success', "Shipping fee updated for {$lga->name}.");
    }
}
```

- [ ] **Step 3: Create the view**

`resources/views/admin/settings/shipping/districts.blade.php` — adapt the existing `admin/settings/gig/locations.blade.php` layout wrapper (`@extends`/`@section` directives — copy those from the file being replaced), body content:

```blade
@section('content')
<div class="p-6">
    <h1 class="text-xl font-semibold mb-4">District Shipping Fees</h1>

    @if (session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
    @endif

    @foreach ($regions as $region)
        <h2 class="text-lg font-medium mt-6 mb-2">{{ $region->name }}</h2>
        <table class="w-full text-left border">
            <thead>
                <tr class="bg-gray-50">
                    <th class="p-2">District</th>
                    <th class="p-2">Shipping Fee (UGX)</th>
                    <th class="p-2"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($region->lgas as $district)
                <tr class="border-t">
                    <form method="POST" action="{{ route('admin.district-shipping.update', $district->id) }}">
                        @csrf
                        <td class="p-2">{{ $district->name }}</td>
                        <td class="p-2">
                            <input type="number" step="0.01" min="0" name="shipping_fee" value="{{ $district->shipping_fee }}" class="border rounded px-2 py-1 w-32">
                        </td>
                        <td class="p-2">
                            <button type="submit" class="px-3 py-1 bg-primary text-white rounded">Save</button>
                        </td>
                    </form>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
</div>
@endsection
```

- [ ] **Step 4: Add routes**

In `routes/admin.php`, replace the `gig-locations`/`update-gig-location` route entries with:
```php
Route::get('/settings/shipping', [App\Http\Controllers\Admin\DistrictShippingController::class, 'index'])->name('admin.district-shipping.index');
Route::post('/settings/shipping/{lga}', [App\Http\Controllers\Admin\DistrictShippingController::class, 'update'])->name('admin.district-shipping.update');
```
(match whatever middleware group the surrounding admin routes use.)

- [ ] **Step 5: Delete the old GIG-locations controller methods and view**

Remove the corresponding methods from wherever `gig-locations`/`update-gig-location` were handled, and delete `resources/views/admin/settings/gig/locations.blade.php`.

- [ ] **Step 6: Manually test**

Log in as admin, navigate to `/admin/settings/shipping`, confirm all 4 regions and their districts list with editable fee fields, update one fee, confirm it saves and the flash message shows.

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/Admin/DistrictShippingController.php resources/views/admin/settings/shipping/districts.blade.php routes/admin.php
git rm resources/views/admin/settings/gig/locations.blade.php
git commit -m "feat: add admin CRUD for per-district shipping fees, replacing GIG locations screen"
```

---

### Task 13: Relabel location UI copy (State→Region, LGA→District)

**Files:**
- Modify: all blade files under `resources/views/public/components/advert/` referencing "State"/"LGA" labels (`modal-locations.blade.php`, `modal-brand-locations.blade.php`, `modal-subcat-locations.blade.php`, `advert-location.blade.php`, `advert-location-mobile.blade.php`)
- Modify: `resources/js/lga.js`, `public/frontend/js/lga.js` (label strings/comments only — cascading dropdown logic itself is generic and doesn't need behavioral changes)

**Interfaces:** none — copy-only change.

- [ ] **Step 1: Find all user-facing "State"/"LGA" label strings**

Run: `grep -rn '>State<\|>LGA<\|Select State\|Select LGA\|--State--\|--LGA--' resources/views/public/components/advert/`

- [ ] **Step 2: Replace each match**

For each match found in Step 1, replace `State` → `Region` and `LGA` → `District` in the label text only (not in variable names, route names, or model references — those stay as `state`/`lga` per Task 6/7's decision to not rename the schema).

- [ ] **Step 3: Update JS label strings**

Run: `grep -n "State\|LGA" resources/js/lga.js public/frontend/js/lga.js` and replace any user-facing placeholder text (e.g. `"Select LGA"` dropdown placeholders) the same way. Leave function/variable names (`loadLgas`, etc.) unchanged — internal naming, not user-facing.

- [ ] **Step 4: Manually verify in browser**

Load the advert-posting form and an advert search/filter page, confirm location dropdowns now read "Region"/"District" instead of "State"/"LGA".

- [ ] **Step 5: Commit**

```bash
git add -A
git commit -m "chore: relabel location UI copy from State/LGA to Region/District"
```

---

### Task 14: Regenerate location sitemap

**Files:**
- Modify or locate the sitemap generation source for `public/sitemap-locations.xml` (search first — likely an Artisan command or a `spatie/laravel-sitemap`-based generator, given that package is a dependency)

**Interfaces:** none — build artifact regeneration.

- [ ] **Step 1: Find the generator**

Run: `grep -rln "sitemap-locations" app/Console/Commands/ app/Services/ routes/`

- [ ] **Step 2: Update the generator's data source if it references `GigLogistic`/old LGA data directly**

If the generator queries `GigLogistic` (retired in Task 9) for city-level URLs, remove that portion — the design's location depth is Region+District only, so the sitemap should list region and district slugs (from `states`/`lgas`, already Uganda-seeded by Tasks 6-7), not a city level.

- [ ] **Step 3: Regenerate**

Run whatever command Step 1 found (e.g. `php artisan sitemap:generate` — confirm exact command name from the `Console/Commands` directory listing).

- [ ] **Step 4: Verify**

Run: `grep -c "kampala\|wakiso\|mbarara" public/sitemap-locations.xml`
Expected: non-zero (contains Uganda district slugs)

Run: `grep -i "lagos\|abuja\|port-harcourt" public/sitemap-locations.xml`
Expected: no output (no Nigerian slugs remain)

- [ ] **Step 5: Commit**

```bash
git add public/sitemap-locations.xml
git commit -m "chore: regenerate location sitemap with Uganda districts"
```

---

## Phase 3 — Payment gateway (Flutterwave)

### Task 15: Flutterwave config + env

**Files:**
- Modify: `config/services.php` (replace `paystack` block, lines 33-37)
- Modify: `.env.example` (replace `PAYSTACK_*` lines 63-66)

**Interfaces:**
- Produces: `config('services.flutterwave.publicKey')`, `config('services.flutterwave.secretKey')`, `config('services.flutterwave.encryptionKey')`, `config('services.flutterwave.paymentUrl')`, `config('services.flutterwave.webhookHash')`.

- [ ] **Step 1: Replace the `paystack` config block**

Replace:
```php
    'paystack' => [
        'publicKey' => env('PAYSTACK_PUBLIC_KEY'),
        'secretKey' => env('PAYSTACK_SECRET_KEY'),
        'paymentUrl' => env('PAYSTACK_PAYMENT_URL'),
    ],
```
with:
```php
    'flutterwave' => [
        'publicKey' => env('FLW_PUBLIC_KEY'),
        'secretKey' => env('FLW_SECRET_KEY'),
        'encryptionKey' => env('FLW_ENCRYPTION_KEY'),
        'paymentUrl' => env('FLW_PAYMENT_URL', 'https://api.flutterwave.com/v3'),
        'webhookHash' => env('FLW_WEBHOOK_HASH'),
    ],
```

- [ ] **Step 2: Replace `.env.example` keys**

Replace the `PAYSTACK_PUBLIC_KEY`/`PAYSTACK_SECRET_KEY`/`PAYSTACK_PAYMENT_URL` lines with:
```
FLW_PUBLIC_KEY=
FLW_SECRET_KEY=
FLW_ENCRYPTION_KEY=
FLW_PAYMENT_URL=https://api.flutterwave.com/v3
FLW_WEBHOOK_HASH=
```

- [ ] **Step 3: Add real keys to local `.env`** (not committed — instruct the developer running this task to obtain sandbox keys from the Flutterwave dashboard and set them locally before Task 16's manual testing)

- [ ] **Step 4: Commit**

```bash
git add config/services.php .env.example
git commit -m "feat: add Flutterwave config, remove Paystack config"
```

---

### Task 16: `User\FlutterwaveController` — checkout + boost payments

**Files:**
- Create: `app/Http/Controllers/User/FlutterwaveController.php`
- Delete: `app/Http/Controllers/User/PaystackController.php`

**Interfaces:**
- Consumes: `config('services.flutterwave.*')` from Task 15.
- Produces: `initialize()`, `callback()`, `failed()`, `success()`, `buyDirectSuccess()`, `resumePayment()`, `initialize_boost()`, `callback_boost()`, `initialize_post_boost()`, `retry_boost_payment()` — same method names/signatures as the old `PaystackController` (routes in Task 18 point at these unchanged names, only the class changes).

Read the full current `app/Http/Controllers/User/PaystackController.php` before starting — this task is a line-for-line rewrite of its HTTP calls and amount handling, not new business logic. The order-fulfillment side effects inside `callback()` (updating `Payment`, marking `Advert.sold`, sending `BuyDirectMail`/`SellerMail`, creating `Notification`, dispatching `SendAdSoldPushNotification`) stay exactly as they are — only the gateway API calls and amount units change.

- [ ] **Step 1: Copy `PaystackController.php` to `FlutterwaveController.php`, rename the class**

```bash
cp app/Http/Controllers/User/PaystackController.php app/Http/Controllers/User/FlutterwaveController.php
```
Rename `class PaystackController extends Controller` → `class FlutterwaveController extends Controller`.

- [ ] **Step 2: Replace every initialize-transaction HTTP call**

Every occurrence of:
```php
$response = Http::withToken(config('services.paystack.secretKey'))
    ->post(config('services.paystack.paymentUrl') . '/transaction/initialize', [
        'email' => $email,
        'amount' => $price * 100, // kobo
        ...
    ]);
```
becomes:
```php
$response = Http::withToken(config('services.flutterwave.secretKey'))
    ->post(config('services.flutterwave.paymentUrl') . '/payments', [
        'tx_ref' => (string) Str::uuid(),
        'amount' => $price,
        'currency' => config('currency.code'),
        'redirect_url' => route('flutterwave.callback'),
        'customer' => ['email' => $email],
        ...
    ]);
```
(Preserve every other existing key in the payload — e.g. `metadata` — under the same structure; only the HTTP target, `amount` unit, and the request-shape wrapper (`customer.email` instead of top-level `email`, `tx_ref` instead of relying on Paystack's server-generated `reference`) change. Generate and persist `tx_ref` yourself since Flutterwave requires the caller to supply it — store it on the `Payment` row's `payment_reference` field exactly where the old code stored Paystack's returned `reference`.)

Add `use Illuminate\Support\Str;` import if not already present.

- [ ] **Step 3: Replace the initialize-response handling**

Every occurrence of:
```php
if ($data['status']) {
    return redirect($data['data']['authorization_url']);
}
```
becomes:
```php
if ($data['status'] === 'success') {
    return redirect($data['data']['link']);
}
```

- [ ] **Step 4: Replace every verify-transaction HTTP call**

Every occurrence of:
```php
$response = Http::withToken(config('services.paystack.secretKey'))
    ->get(config('services.paystack.paymentUrl') . "/transaction/verify/{$reference}");
```
becomes:
```php
$response = Http::withToken(config('services.flutterwave.secretKey'))
    ->get(config('services.flutterwave.paymentUrl') . "/transactions/verify_by_reference", [
        'tx_ref' => $reference,
    ]);
```

- [ ] **Step 5: Remove all kobo unit conversions**

Every `$amount / 100` on the verify/callback side and every `* 100` on the initialize side (lines that were `:41,128,234,265,358,425` in the original Paystack file) — remove the `* 100`/`/ 100` entirely, using the raw shilling amount directly, since UGX has no minor-unit convention here.

- [ ] **Step 6: Update `callback()`'s status check**

Flutterwave's verify response uses `data.status === 'successful'` (not Paystack's `data.status === 'success'` inside `data.data.status`) — find the exact conditional in the copied `callback()` method and update the string comparison to `'successful'`.

- [ ] **Step 7: Verify no `Paystack`/kobo references remain in the file**

Run: `grep -n "Paystack\|kobo\|\* 100\|/ 100" app/Http/Controllers/User/FlutterwaveController.php`
Expected: no output (empty)

- [ ] **Step 8: Delete the old controller**

```bash
rm app/Http/Controllers/User/PaystackController.php
```

- [ ] **Step 9: Manually test against Flutterwave sandbox**

With sandbox keys set in `.env` (Task 15, Step 3), trigger a buy-direct checkout, confirm redirect to Flutterwave's hosted payment page, complete a sandbox test-card payment, confirm the callback marks the advert sold and sends the expected emails/notification.

- [ ] **Step 10: Commit**

```bash
git add app/Http/Controllers/User/FlutterwaveController.php
git rm app/Http/Controllers/User/PaystackController.php
git commit -m "feat: replace User PaystackController with FlutterwaveController"
```

---

### Task 17: `Api\FlutterwaveController` — API/mobile checkout + boost payments

**Files:**
- Create: `app/Http/Controllers/Api/FlutterwaveController.php`
- Delete: `app/Http/Controllers/Api/PaystackController.php`

**Interfaces:**
- Same transformation as Task 16, applied to the API controller's `initializePayment`, `handleCallback`, `handleBuyDirectPayment`, `handleBoostPayment`, `initializeBoost`, `getPayment`, `getUserPayments`, `getUserBoosts` methods.

- [ ] **Step 1: Copy and rename the class**

```bash
cp app/Http/Controllers/Api/PaystackController.php app/Http/Controllers/Api/FlutterwaveController.php
```
Rename `class PaystackController extends Controller` → `class FlutterwaveController extends Controller`.

- [ ] **Step 2: Apply the same 5 transformations as Task 16 (Steps 2-6)** — initialize-call payload shape, response-field mapping (`authorization_url`→`link`, `reference`→`tx_ref`), verify-call endpoint, kobo-conversion removal (lines `:108,251,348` in the original), and the `successful` status-string fix.

- [ ] **Step 3: Update the `@OA\*` / Scribe annotations**

Find and update any annotation summary/description text mentioning "Paystack" to say "Flutterwave" instead — these feed API docs, not runtime behavior, but should stay accurate.

- [ ] **Step 4: Verify no `Paystack`/kobo references remain**

Run: `grep -n "Paystack\|kobo\|\* 100\|/ 100" app/Http/Controllers/Api/FlutterwaveController.php`
Expected: no output (empty)

- [ ] **Step 5: Delete the old controller**

```bash
rm app/Http/Controllers/Api/PaystackController.php
```

- [ ] **Step 6: Manually test via API client (Postman/curl) against sandbox**

`POST /api/payments/initialize` with a valid Sanctum token and advert id, confirm a `link` is returned; complete the sandbox payment; `GET /api/payments/{paymentId}` to confirm status updated.

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/Api/FlutterwaveController.php
git rm app/Http/Controllers/Api/PaystackController.php
git commit -m "feat: replace Api PaystackController with FlutterwaveController"
```

---

### Task 18: `Api\FlutterwaveWebhookController`

**Files:**
- Create: `app/Http/Controllers/Api/FlutterwaveWebhookController.php`
- Delete: `app/Http/Controllers/Api/PaystackWebhookController.php`
- Test: `tests/Feature/FlutterwaveWebhookTest.php`

**Interfaces:**
- Consumes: `config('services.flutterwave.webhookHash')` from Task 15.
- Produces: `handle(Request $request)` — same contract as the old webhook controller (always returns HTTP 200), preserving the legacy `AdvertBoost`-existence-check fallback for inferring `payment_type` when metadata lacks it, and the idempotent `activateBoost()` check.

- [ ] **Step 1: Write the failing signature-verification test**

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class FlutterwaveWebhookTest extends TestCase
{
    public function test_rejects_request_with_missing_signature(): void
    {
        config(['services.flutterwave.webhookHash' => 'test-secret']);

        $response = $this->postJson('/api/flutterwave/webhook', ['event' => 'charge.completed']);

        $response->assertStatus(200);
        // Rejected webhooks still return 200 (per existing gateway-retry-suppression convention)
        // but must not process the payload — assert no side effect occurred, e.g. no Payment updated.
    }

    public function test_accepts_request_with_correct_signature(): void
    {
        config(['services.flutterwave.webhookHash' => 'test-secret']);

        $response = $this->postJson('/api/flutterwave/webhook', ['event' => 'charge.completed'], [
            'verif-hash' => 'test-secret',
        ]);

        $response->assertStatus(200);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/FlutterwaveWebhookTest.php`
Expected: FAIL (route doesn't exist yet)

- [ ] **Step 3: Copy and rename the class**

```bash
cp app/Http/Controllers/Api/PaystackWebhookController.php app/Http/Controllers/Api/FlutterwaveWebhookController.php
```
Rename `class PaystackWebhookController extends Controller` → `class FlutterwaveWebhookController extends Controller`.

- [ ] **Step 4: Replace signature verification**

Replace the HMAC-SHA512 block:
```php
        $signature = $request->header('X-Paystack-Signature');
        $computed = hash_hmac('sha512', $request->getContent(), config('services.paystack.secretKey'));

        if (! hash_equals($computed, $signature ?? '')) {
            return response()->json(['status' => 'invalid signature'], 200);
        }
```
with:
```php
        $signature = $request->header('verif-hash');
        $expected = config('services.flutterwave.webhookHash');

        if (! hash_equals((string) $expected, (string) $signature)) {
            return response()->json(['status' => 'invalid signature'], 200);
        }
```

- [ ] **Step 5: Update the event name check**

Replace `if ($event === 'charge.success')` with `if ($event === 'charge.completed')`.

- [ ] **Step 6: Update the server-side re-verification call**

Same transformation as Task 16 Step 4 — swap the `/transaction/verify/{reference}` Paystack call for Flutterwave's `/transactions/verify_by_reference?tx_ref=...`, and update the returned-status check to `'successful'` per Task 16 Step 6.

- [ ] **Step 7: Wire the route (temporary, formalized in Task 19)**

Add to `routes/api.php` right after the old `POST /paystack/webhook` line: `Route::post('/flutterwave/webhook', [\App\Http\Controllers\Api\FlutterwaveWebhookController::class, 'handle']);` — Task 19 will remove the old line and finalize route naming.

- [ ] **Step 8: Run test to verify it passes**

Run: `php artisan test tests/Feature/FlutterwaveWebhookTest.php`
Expected: PASS

- [ ] **Step 9: Delete the old webhook controller**

```bash
rm app/Http/Controllers/Api/PaystackWebhookController.php
```

- [ ] **Step 10: Commit**

```bash
git add app/Http/Controllers/Api/FlutterwaveWebhookController.php tests/Feature/FlutterwaveWebhookTest.php routes/api.php
git rm app/Http/Controllers/Api/PaystackWebhookController.php
git commit -m "feat: replace Paystack webhook handler with Flutterwave verif-hash verification"
```

---

### Task 19: Finalize routes (public/user/api)

**Files:**
- Modify: `routes/public.php:59,84-87`
- Modify: `routes/user.php:27,98-101`
- Modify: `routes/api.php:194-198,202,205` (and remove the temporary line added in Task 18 Step 7, replacing it with the final version)

**Interfaces:**
- Produces: route names `flutterwave.pay`, `flutterwave.callback` (replacing `paystack.pay`/`paystack.callback`); all other route names (`payment.success`, `payment.failed`, `buy.direct.success`, `post-boost.pay`, `boost.pay`, `boost.retry-payment`, `boost.callback`, `user.resume.payment`, `/payments/initialize`, `/payments/initialize-boost`, `/payments/{paymentId}`, `/payments/user/{userId}`, `/boosts/user/{userId}`, `/payments/callback`) keep their existing names — only their controller class references change, and `/paystack/webhook` becomes `/flutterwave/webhook`.

- [ ] **Step 1: Update `routes/public.php`**

Replace the `paystack.pay`/`paystack.callback` route definitions and controller import to reference `App\Http\Controllers\User\FlutterwaveController`, renaming route names `paystack.pay` → `flutterwave.pay` and `paystack.callback` → `flutterwave.callback`. Update the other nearby route definitions (`payment.success`, `payment.failed`, `buy.direct.success`) to point at `FlutterwaveController` methods (same method names — `success`, `failed`, `buyDirectSuccess`).

- [ ] **Step 2: Update `routes/user.php`**

Replace the controller import/reference for `post-boost.pay`, `boost.pay`, `boost.retry-payment`, `boost.callback`, `user.resume.payment` to `FlutterwaveController` (route names unchanged — only controller class changes).

- [ ] **Step 3: Update `routes/api.php`**

Replace the controller import/reference for `/payments/initialize`, `/payments/initialize-boost`, `/payments/{paymentId}`, `/payments/user/{userId}`, `/boosts/user/{userId}`, `/payments/callback` to `Api\FlutterwaveController`. Remove the old `POST /paystack/webhook` line entirely, and remove the temporary duplicate `/flutterwave/webhook` line added in Task 18 Step 7, replacing both with a single final: `Route::post('/flutterwave/webhook', [App\Http\Controllers\Api\FlutterwaveWebhookController::class, 'handle'])->name('flutterwave.webhook');` (public, no auth middleware — signature verified inside the controller, matching the prior convention).

- [ ] **Step 4: Grep for any remaining `paystack.` route-name references**

Run: `grep -rn "paystack\.\|'paystack'" resources/views/ app/ routes/`
Expected: only non-route-name mentions remain (e.g. leftover copy text, handled in Task 21) — no `route('paystack.*')` calls.

- [ ] **Step 5: Run route list sanity check**

Run: `php artisan route:list | grep -i flutterwave`
Expected: lists `flutterwave.pay`, `flutterwave.callback`, boost routes, API payment routes, and `flutterwave.webhook`, all pointing at the new controllers.

- [ ] **Step 6: Commit**

```bash
git add routes/public.php routes/user.php routes/api.php
git commit -m "feat: rename payment routes from paystack.* to flutterwave.*"
```

---

### Task 20: Update checkout copy referencing Paystack

**Files:**
- Modify: `resources/views/public/buy-direct-payment.blade.php:107,204-205`
- Modify: `resources/views/admin/settlements/pending-settlements.blade.php`
- Modify: `resources/views/admin/adboost/unpaid.blade.php`
- Modify: `resources/views/admin/adboost/confirm-payment.blade.php`
- Modify: `resources/views/public/pages/billing.blade.php`
- Modify: `resources/views/user/settings/payment-info.blade.php`
- Modify: `resources/views/public/pages/payments-refunds.blade.php`
- Modify: `resources/views/public/pages/cookie.blade.php`

**Interfaces:** none — copy/route-reference only.

- [ ] **Step 1: Update the form action in `buy-direct-payment.blade.php:107`**

Replace `action="{{ route('paystack.pay') }}"` with `action="{{ route('flutterwave.pay') }}"`.

- [ ] **Step 2: Update the marketing copy at `buy-direct-payment.blade.php:204-205`**

Replace:
```blade
<h4 class="font-medium text-gray-800">Secure Payments with Paystack</h4>
<p class="text-gray-600">All payments are processed safely through Paystack, supporting cards, bank transfers, and other local methods.</p>
```
with:
```blade
<h4 class="font-medium text-gray-800">Secure Payments with Flutterwave</h4>
<p class="text-gray-600">All payments are processed safely through Flutterwave, supporting cards, mobile money, and bank transfers.</p>
```

- [ ] **Step 3: Sweep remaining "Paystack" text mentions**

Run: `grep -rln "Paystack" resources/views/`, and for each file listed (the 7 admin/public views above, plus any others found), replace "Paystack" with "Flutterwave" in the surrounding copy/labels — these are label-only mentions with no route/logic behind them (confirmed in the earlier research pass).

- [ ] **Step 4: Verify**

Run: `grep -rln "Paystack" resources/views/ app/`
Expected: no output (empty)

- [ ] **Step 5: Manually verify the buy-direct page**

Load the buy-direct-payment page in browser, confirm the "Buy Now Securely" button posts successfully and the trust-badge copy reads "Flutterwave".

- [ ] **Step 6: Commit**

```bash
git add -A
git commit -m "chore: update remaining Paystack copy references to Flutterwave"
```

---

## Phase 4 — Payouts

### Task 21: Rename `Bank` model column, update `SyncBanks` for Flutterwave

**Files:**
- Create: migration `database/migrations/2026_08_02_000002_rename_paystack_bank_code_on_banks_table.php`
- Modify: `app/Models/Bank.php`
- Modify: `app/Console/Commands/SyncBanks.php`
- Test: `tests/Unit/Console/SyncBanksTest.php` (create if no existing test covers this command — check `tests/` first)

**Interfaces:**
- Produces: `banks.bank_code` (renamed from `paystack_bank_code`), `Bank::$fillable = ['name', 'bank_code']`.

- [ ] **Step 1: Create the rename migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banks', function (Blueprint $table) {
            $table->renameColumn('paystack_bank_code', 'bank_code');
        });
    }

    public function down(): void
    {
        Schema::table('banks', function (Blueprint $table) {
            $table->renameColumn('bank_code', 'paystack_bank_code');
        });
    }
};
```

(Renaming a unique-indexed column via Laravel's `renameColumn` requires `doctrine/dbal` — check `composer.json` for it; if absent, run `composer require doctrine/dbal --dev` first, or write the migration as raw `DB::statement('ALTER TABLE banks CHANGE paystack_bank_code bank_code VARCHAR(255) NOT NULL')` instead to avoid the dependency.)

- [ ] **Step 2: Update the `Bank` model**

Change:
```php
protected $fillable = ['name', 'paystack_bank_code'];
```
to:
```php
protected $fillable = ['name', 'bank_code'];
```

- [ ] **Step 3: Update `SyncBanks.php`**

Replace:
```php
$response = Http::withToken(config('services.paystack.secret'))
    ->get('https://api.paystack.co/bank?currency=NGN');
```
with:
```php
$response = Http::withToken(config('services.flutterwave.secretKey'))
    ->get(config('services.flutterwave.paymentUrl') . '/banks/UG');
```
and update the `Bank::updateOrCreate([...])` call's key from `'paystack_bank_code' => ...` to `'bank_code' => ...`, mapping to whatever field name Flutterwave's `/banks/UG` response uses for the bank code (confirm the exact field name — Flutterwave returns `code` per bank entry — mapping `'bank_code' => $bank['code']`, `'name' => $bank['name']`).

- [ ] **Step 4: Run the migration**

Run: `php artisan migrate`
Expected: clean run, `banks.bank_code` column exists, `paystack_bank_code` gone.

- [ ] **Step 5: Manually test the sync command against sandbox**

Run: `php artisan banks:sync`
Expected: `banks` table populated with Uganda bank names/codes from Flutterwave's sandbox response.

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_08_02_000002_rename_paystack_bank_code_on_banks_table.php app/Models/Bank.php app/Console/Commands/SyncBanks.php
git commit -m "feat: rename Bank.paystack_bank_code to bank_code, sync from Flutterwave's Uganda bank list"
```

---

### Task 22: Add mobile-money payout columns to `users`

**Files:**
- Create: migration `database/migrations/2026_08_02_000003_add_payout_method_to_users_table.php`

**Interfaces:**
- Produces: `users.payout_method` (string, nullable, `'bank'` or `'mobile_money'`), `users.mobile_network` (string, nullable, `'MTN'` or `'AIRTEL'`), `users.mobile_money_number` (string, nullable).

- [ ] **Step 1: Create the migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('payout_method')->nullable()->after('account_number');
            $table->string('mobile_network')->nullable()->after('payout_method');
            $table->string('mobile_money_number', 15)->nullable()->after('mobile_network');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['payout_method', 'mobile_network', 'mobile_money_number']);
        });
    }
};
```

- [ ] **Step 2: Run the migration**

Run: `php artisan migrate`
Expected: clean run, 3 new nullable columns on `users`.

- [ ] **Step 3: Commit**

```bash
git add database/migrations/2026_08_02_000003_add_payout_method_to_users_table.php
git commit -m "feat: add mobile money payout columns to users table"
```

---

### Task 23: Payout-method selector in payment-info form

**Files:**
- Modify: `app/Http/Requests/User/UpdatePaymentInfoRequest.php`
- Modify: `app/Http/Controllers/User/UserProfile.php:277-303` (`payment_info` method)
- Modify: `resources/views/user/settings/payment-info.blade.php`
- Test: `tests/Feature/UpdatePaymentInfoTest.php`

**Interfaces:**
- Consumes: `users.payout_method/mobile_network/mobile_money_number` from Task 22.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpdatePaymentInfoTest extends TestCase
{
    use RefreshDatabase;

    public function test_bank_payout_method_requires_bank_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->withSession(['user_id' => $user->user_id])
            ->post(route('user.payment-info'), [
                'payout_method' => 'bank',
                'bank_name' => 'Stanbic Bank',
                'paystack_bank_code' => '540',
                'account_name' => 'Test User',
                'account_number' => '1234567890',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'user_id' => $user->user_id,
            'payout_method' => 'bank',
            'account_number' => '1234567890',
        ]);
    }

    public function test_mobile_money_payout_method_requires_mobile_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->withSession(['user_id' => $user->user_id])
            ->post(route('user.payment-info'), [
                'payout_method' => 'mobile_money',
                'mobile_network' => 'MTN',
                'mobile_money_number' => '0771234567',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'user_id' => $user->user_id,
            'payout_method' => 'mobile_money',
            'mobile_network' => 'MTN',
            'mobile_money_number' => '0771234567',
        ]);
    }

    public function test_mobile_money_without_number_fails_validation(): void
    {
        $user = User::factory()->create();

        $response = $this->withSession(['user_id' => $user->user_id])
            ->post(route('user.payment-info'), [
                'payout_method' => 'mobile_money',
                'mobile_network' => 'MTN',
            ]);

        $response->assertSessionHasErrors('mobile_money_number');
    }
}
```

(Adjust the route name/session key to match whatever `UserProfile::payment_info`'s route is actually named and however the existing test suite establishes an authenticated user session — check an existing passing feature test, e.g. `tests/Feature/AdvertManagementTest.php`, for the established auth pattern before finalizing this test.)

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/UpdatePaymentInfoTest.php`
Expected: FAIL (validation rules don't yet branch on `payout_method`, `mobile_network`/`mobile_money_number` aren't persisted)

- [ ] **Step 3: Update `UpdatePaymentInfoRequest`**

Replace the rules array with conditional rules based on `payout_method`:
```php
    public function rules(): array
    {
        return [
            'payout_method' => 'required|in:bank,mobile_money',
            'bank_name' => 'required_if:payout_method,bank',
            'paystack_bank_code' => 'required_if:payout_method,bank',
            'account_number' => 'required_if:payout_method,bank',
            'account_name' => 'required_if:payout_method,bank',
            'mobile_network' => 'required_if:payout_method,mobile_money|in:MTN,AIRTEL',
            'mobile_money_number' => ['required_if:payout_method,mobile_money', new \App\Rules\UgandanPhoneNumber()],
        ];
    }
```
(keep the existing `paystack_bank_code` field name as the request input key — it maps to `Bank::bank_code` downstream in the controller, per Task 21 — renaming the form field itself is a separate, optional cleanup not required for correctness.)

- [ ] **Step 4: Update `UserProfile::payment_info`**

Replace the `DB::table('users')->update([...])` call to include the new fields:
```php
            $user = DB::table('users')
                ->where('user_id', $user_id)
                ->update([
                    'payout_method' => $request->input('payout_method'),
                    'bank_name' => $request->input('bank_name'),
                    'bank_code' => $request->input('paystack_bank_code'),
                    'account_name' => $request->input('account_name'),
                    'account_number' => $request->input('account_number'),
                    'mobile_network' => $request->input('mobile_network'),
                    'mobile_money_number' => $request->input('mobile_money_number'),
                ]);
```
(`bank_code` per Task 21's rename; unset fields for the non-selected method are written as `null` via `$request->input(...)` returning `null` when absent, which is fine since those columns are nullable.)

- [ ] **Step 5: Update the view with a method toggle**

In `resources/views/user/settings/payment-info.blade.php`, wrap the existing bank fields (lines ~21-54) in a `<div id="bankFields">` and add a parallel `<div id="mobileMoneyFields">` with a network `<select name="mobile_network">` (options MTN/AIRTEL) and a phone `<input name="mobile_money_number">`, plus a `<select name="payout_method">` (options: Bank Transfer / Mobile Money) above both, with a small inline `<script>` toggling visibility of the two field groups based on the select's value (mirror the existing show/hide pattern already used elsewhere in the same view for conditional fields, if any — otherwise a plain `addEventListener('change', ...)` toggling `.hidden` class).

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test tests/Feature/UpdatePaymentInfoTest.php`
Expected: PASS (3 tests)

- [ ] **Step 7: Manually verify in browser**

Load the payment-info settings page, toggle between Bank Transfer and Mobile Money, confirm the right fields show/hide and both submit correctly.

- [ ] **Step 8: Commit**

```bash
git add app/Http/Requests/User/UpdatePaymentInfoRequest.php app/Http/Controllers/User/UserProfile.php resources/views/user/settings/payment-info.blade.php tests/Feature/UpdatePaymentInfoTest.php
git commit -m "feat: support mobile money as a seller payout method alongside bank transfer"
```

---

### Task 24: Rewrite `ManagePayments::sendPayout` for Flutterwave Transfers

**Files:**
- Modify: `app/Http/Controllers/Admin/ManagePayments.php:190-276`
- Test: `tests/Feature/SendPayoutTest.php`

**Interfaces:**
- Consumes: `users.payout_method/mobile_network/mobile_money_number` (Task 22), `banks.bank_code` (Task 21), `config('services.flutterwave.*')` (Task 15).

Flutterwave's Transfers API takes destination details directly per-call — no separate "create recipient" step like Paystack's NUBAN flow, simplifying this from 3 HTTP calls to 1.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SendPayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_bank_payout_calls_flutterwave_transfer_with_bank_type(): void
    {
        Http::fake([
            '*/transfers' => Http::response(['status' => 'success', 'data' => ['id' => 12345]], 200),
        ]);

        $user = User::factory()->create([
            'payout_method' => 'bank',
            'bank_code' => '540',
            'account_number' => '1234567890',
            'account_name' => 'Test User',
        ]);

        app(\App\Http\Controllers\Admin\ManagePayments::class)->sendPayout($user, 100000);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/transfers')
                && $request['type'] === 'account'
                && $request['account_bank'] === '540'
                && $request['account_number'] === '1234567890'
                && $request['currency'] === 'UGX';
        });
    }

    public function test_mobile_money_payout_calls_flutterwave_transfer_with_mobile_type(): void
    {
        Http::fake([
            '*/transfers' => Http::response(['status' => 'success', 'data' => ['id' => 12346]], 200),
        ]);

        $user = User::factory()->create([
            'payout_method' => 'mobile_money',
            'mobile_network' => 'MTN',
            'mobile_money_number' => '0771234567',
        ]);

        app(\App\Http\Controllers\Admin\ManagePayments::class)->sendPayout($user, 50000);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/transfers')
                && $request['type'] === 'mobilemoneyuganda'
                && $request['account_number'] === '0771234567'
                && $request['network'] === 'MTN'
                && $request['currency'] === 'UGX';
        });
    }
}
```

(Adjust `sendPayout`'s actual signature to match — check its current parameter list in `ManagePayments.php:190-276` before finalizing the test; the transformation below assumes it currently takes a `Payment`/`User` and an amount, matching the existing 3-step flow's inputs.)

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/SendPayoutTest.php`
Expected: FAIL (method still calls Paystack endpoints)

- [ ] **Step 3: Rewrite `sendPayout`**

Replace the existing 3-step `GET /bank/resolve` → `POST /transferrecipient` → `POST /transfer` flow with a single call, branching on `payout_method`:

```php
    public function sendPayout($user, $amount)
    {
        $payload = [
            'amount' => $amount,
            'currency' => config('currency.code'),
            'narration' => 'MarketplaceUG seller payout',
            'reference' => 'payout_' . uniqid(),
        ];

        if ($user->payout_method === 'mobile_money') {
            $payload['type'] = 'mobilemoneyuganda';
            $payload['account_number'] = $user->mobile_money_number;
            $payload['network'] = $user->mobile_network;
        } else {
            $payload['type'] = 'account';
            $payload['account_bank'] = $user->bank_code;
            $payload['account_number'] = $user->account_number;
            $payload['beneficiary_name'] = $user->account_name;
        }

        $response = Http::withToken(config('services.flutterwave.secretKey'))
            ->post(config('services.flutterwave.paymentUrl') . '/transfers', $payload);

        $data = $response->json();

        if ($response->successful() && ($data['status'] ?? null) === 'success') {
            Mail::to($user->email)->send(new PayoutMail($user, $amount));
            return true;
        }

        return false;
    }
```

(Preserve whatever settlement-status-updating side effects the original method had around the Paystack calls — re-check the original 190-276 block for any `Settlement`/`Payment` model updates surrounding the transfer call and carry them forward unchanged, only the transfer-API portion is replaced.)

- [ ] **Step 4: Fix the currency hardcode and secret-key typo bug**

Confirm no `'currency' => 'NGN'` or `config('services.paystack.secret')` (missing `Key`) remain — the rewrite above already uses `config('currency.code')` and `config('services.flutterwave.secretKey')` correctly; this step is a verification, not new work.

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test tests/Feature/SendPayoutTest.php`
Expected: PASS (2 tests)

- [ ] **Step 6: Manually test against sandbox**

Trigger a payout for a test bank-transfer seller and a test mobile-money seller against Flutterwave's sandbox, confirm both succeed and the payout email sends.

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/Admin/ManagePayments.php tests/Feature/SendPayoutTest.php
git commit -m "feat: rewrite seller payouts to use Flutterwave Transfers with bank and mobile money support"
```

---

## Phase 5 — Branding

### Task 25: Update site config and SEO meta

**Files:**
- Modify: `config/global.php`
- Modify: `resources/views/public/layouts/header-location.blade.php:1-35`

**Interfaces:** none — config/copy only.

- [ ] **Step 1: Update `config/global.php`**

Replace:
```php
'site_name' => 'Marketplace Naija',
'site_title' => 'Buy & Sell on Marketplace Naija',
'email_title' => 'Marketplace Naija',
'admin_email' => 'adminstrator@marketplace.ng',
'dispute_email' => 'disputes@marketplace.ng',
'site_phone' => '+234 8034 814 561',
'site_email' => 'adminstrator@marketplace.ng',
'site_address' => 'Plot 11 Okwelle layout Irete, Owerri, IMO State',
```
with:
```php
'site_name' => 'Marketplace Uganda',
'site_title' => 'Buy & Sell on Marketplace Uganda',
'email_title' => 'Marketplace Uganda',
'admin_email' => 'administrator@marketplaceug.com',
'dispute_email' => 'disputes@marketplaceug.com',
'site_phone' => '+256 700 000 000',
'site_email' => 'administrator@marketplaceug.com',
'site_address' => 'Kampala, Uganda',
```
(also fix the pre-existing duplicate `dispute_email` array-key bug noted during research while touching this file — ensure it's defined exactly once. Placeholder phone/email/domain values above should be swapped for the real ones before launch — flag this to the user.)

- [ ] **Step 2: Update `header-location.blade.php` SEO meta**

Replace every occurrence of "Marketplace Naija"/"Nigeria" in `<title>`, meta description, meta keywords, `og:site_name`, `og:title`, `og:description`, twitter:title, twitter:description with "Marketplace Uganda"/"Uganda". Change `og:locale` from `en_NG` to `en_UG`.

- [ ] **Step 3: Note the OG image reference**

The `og:image`/`twitter:image` meta currently reference `frontend/images/Marketplace-Naija.png`. Flag to the user that a new branded image asset is needed at that path (or a new path referenced instead) — this plan doesn't include producing new artwork.

- [ ] **Step 4: Manually verify**

Load any public page, view source, confirm `<title>` and OG meta read "Marketplace Uganda" and `og:locale` is `en_UG`.

- [ ] **Step 5: Commit**

```bash
git add config/global.php resources/views/public/layouts/header-location.blade.php
git commit -m "feat: update site branding and SEO meta from Nigeria to Uganda"
```

---

### Task 26: Branding sweep across emails, legal pages, and layouts

**Files:**
- Modify: all files matched by `grep -rli 'naija\|nigeria' --include=*.blade.php --include=*.php .` not already covered by Task 25 (email templates in `app/Mail/*.php` + `resources/views/email/*.blade.php`, legal/marketing pages under `resources/views/public/pages/`, layout partials `header*.blade.php`/`footer-links`/`nav`)

**Interfaces:** none — copy-only sweep.

- [ ] **Step 1: Enumerate current matches**

Run: `grep -rli 'naija\|nigeria' --include=*.blade.php --include=*.php . | grep -v vendor`

- [ ] **Step 2: Sweep each file**

For each file listed, replace "Marketplace Naija" → "Marketplace Uganda", "Nigeria" → "Uganda", and any Naira-currency mentions in copy (e.g. legal/terms pages referencing "Nigerian Naira") → "Ugandan Shillings" — read each file's matched lines in context (`grep -n 'naija\|nigeria' -i <file>`) before editing, since legal-page copy needs sense-checking (e.g. jurisdiction clauses in terms/privacy pages naming Nigerian law should reference Uganda instead, not just a word swap — read the surrounding paragraph and adjust the legal reference sensibly).

- [ ] **Step 3: Verify**

Run: `grep -rli 'naija\|nigeria' --include=*.blade.php --include=*.php . | grep -v vendor`
Expected: no output (empty)

- [ ] **Step 4: Manually spot-check 3-4 pages**

Load `/about`, `/terms`, `/privacy`, and one transactional email preview (if the app has a mail-preview route, or trigger a real one in a local mail-trap), confirm branding reads Uganda throughout.

- [ ] **Step 5: Commit**

```bash
git add -A
git commit -m "chore: sweep remaining Nigeria/Naija branding references to Uganda"
```

---

### Task 27: Final regression sweep

**Files:** none created/modified — verification only.

**Interfaces:** none.

- [ ] **Step 1: Confirm no Paystack references remain**

Run: `grep -rli "paystack" --include=*.php --include=*.blade.php . | grep -v vendor`
Expected: no output (empty)

- [ ] **Step 2: Confirm no Naira/NGN references remain**

Run: `grep -rn '₦\|NGN' --include=*.php --include=*.blade.php app/ resources/ config/ .env.example`
Expected: no output (empty)

- [ ] **Step 3: Confirm no Nigerian states/LGAs/Agility references remain**

Run: `grep -rli "agility\|nigeria\|naija" --include=*.php --include=*.blade.php . | grep -v vendor`
Expected: no output (empty)

- [ ] **Step 4: Run the full test suite**

Run: `php artisan test`
Expected: PASS, zero failures.

- [ ] **Step 5: Manual smoke test of the full flow**

Post an advert with a Uganda district selected, view it (confirm UGX price display), initiate a buy-direct purchase through Flutterwave sandbox, confirm the order completes, then check the admin panel's district shipping-fee screen and a seller's payment-info page (both payout methods) render correctly.

- [ ] **Step 6: Final commit (if any cleanup was needed)**

```bash
git add -A
git commit -m "chore: final regression cleanup for Uganda localization"
```
