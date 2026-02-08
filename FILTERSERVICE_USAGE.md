# FilterService Usage Guide

**File:** `app/Services/FilterService.php`
**Purpose:** Centralized, reusable filtering logic for adverts
**Usage:** Both Web and API controllers

---

## Overview

FilterService provides a **unified filtering interface** that works across:
- ✅ Web controllers (returns HTML/views)
- ✅ API controllers (returns JSON)
- ✅ Any controller that queries adverts

**Key Benefit:** Write filtering logic once, use it everywhere!

---

## Quick Start

### 1. Import the Service

```php
use App\Services\FilterService;
```

### 2. Instantiate

```php
$filterService = new FilterService();
```

### 3. Apply Filters

```php
$query = Advert::query();
$filterService->applyAllFilters($query, $request);
$adverts = $query->paginate(20);
```

---

## Available Methods

### Context Filters
Apply category, subcategory, brand, and location filters.

```php
$filterService->applyContextFilters($query, $request);
```

**Request Parameters:**
- `category` (int) - Category ID
- `sub_category` (int) - Subcategory ID
- `brand` (int) - Brand ID
- `location` (string) - State/Location

---

### Price Filters
Apply min, max, and predefined price ranges.

```php
$filterService->applyPriceFilters($query, $request);
```

**Request Parameters:**
- `min` (int) - Minimum price
- `max` (int) - Maximum price
- `range` (string) - Predefined range:
  - `under_20k` - Less than ₦20,000
  - `20k_120k` - ₦20,000 - ₦120,000
  - `120k_1m` - ₦120,000 - ₦1,000,000
  - `1m_10m` - ₦1,000,000 - ₦10,000,000
  - `above_10m` - More than ₦10,000,000

---

### Seller Filter
Filter by verified/unverified sellers.

```php
$filterService->applySellerFilter($query, $request);
```

**Request Parameters:**
- `seller` (string) - `verified` or `unverified`

---

### Buy Direct Filter
Filter by buy direct availability.

```php
$filterService->applyBuyDirectFilter($query, $request);
```

**Request Parameters:**
- `buydirect` (string) - `yes` or `no`

---

### Car Filters
Apply car-specific filters (condition, fuel type, transmission, registration).

```php
$filterService->applyCarFilters($query, $request);
```

**Request Parameters:**
- `condition` (string) - `Brand New`, `Nigerian Used`, `Foreign Used`
- `fuel_type` (string) - `Petrol`, `Diesel`, `Electric`, `Hybrid`
- `transmission` (string) - `Automatic`, `Manual`
- `registration` (string) - `Registered`, `Unregistered`

**⚠️ Important:** Query must include `carDetail` relationship:
```php
$query = Advert::with('carDetail');
```

---

### Phone Filters
Apply phone-specific filters (condition, device type).

```php
$filterService->applyPhoneFilters($query, $request);
```

**Request Parameters:**
- `condition` (string) - `Brand New`, `Nigerian Used`, `Foreign Used`, `Refurbished`
- `device_type` (string) - `Smartphone`, `Feature Phone`, `Tablet`

**⚠️ Important:** Query must include `phoneDetail` relationship:
```php
$query = Advert::with('phoneDetail');
```

---

### All Filters
Apply all standard filters at once (context, price, seller, buy direct).

```php
$filterService->applyAllFilters($query, $request);
```

**Note:** Does NOT include car/phone filters (apply separately).

---

## API Usage Examples

### Example 1: Car Filter Endpoint

**File:** `app/Http/Controllers/Api/SearchController.php`

```php
use App\Services\FilterService;

public function filterByCarDetails(Request $request)
{
    $filterService = new FilterService();

    $query = Advert::with(['firstImage', 'user', 'carDetail'])
        ->where('ad_status', 'active')
        ->where('sold', 'No');

    // Apply standard filters
    $filterService->applyContextFilters($query, $request);
    $filterService->applyPriceFilters($query, $request);

    // Apply car-specific filters
    if ($request->hasAny(['condition', 'fuel_type', 'transmission', 'registration'])) {
        $filterService->applyCarFilters($query, $request);
    }

    $adverts = $query->orderWithFeatured()->paginate(20);

    return response()->json([
        'success' => true,
        'data' => $adverts->items(),
        'pagination' => [
            'total' => $adverts->total(),
            'current_page' => $adverts->currentPage(),
            'has_more' => $adverts->hasMorePages()
        ]
    ]);
}
```

**API Request:**
```bash
POST /api/search/filter-by-car
Content-Type: application/json

{
  "condition": "Nigerian Used",
  "fuel_type": "Petrol",
  "transmission": "Automatic",
  "registration": "Registered",
  "location": "Lagos",
  "min": 500000,
  "max": 5000000,
  "per_page": 20
}
```

---

### Example 2: Phone Filter Endpoint

**File:** `app/Http/Controllers/Api/SearchController.php`

```php
public function filterByPhoneDetails(Request $request)
{
    $filterService = new FilterService();

    $query = Advert::with(['firstImage', 'user', 'phoneDetail'])
        ->where('ad_status', 'active')
        ->where('sold', 'No');

    // Apply standard filters
    $filterService->applyContextFilters($query, $request);
    $filterService->applyPriceFilters($query, $request);

    // Apply phone-specific filters
    if ($request->hasAny(['condition', 'device_type'])) {
        $filterService->applyPhoneFilters($query, $request);
    }

    $adverts = $query->orderWithFeatured()->paginate(20);

    return response()->json([
        'success' => true,
        'data' => $adverts->items(),
        'pagination' => [...]
    ]);
}
```

**API Request:**
```bash
POST /api/search/filter-by-phone
Content-Type: application/json

{
  "condition": "Brand New",
  "device_type": "Smartphone",
  "category": 1,
  "brand": 5,
  "location": "Lagos",
  "min": 50000,
  "max": 500000,
  "per_page": 20
}
```

---

## Web Controller Usage

### Example: Search Filter (Web)

**File:** `app/Http/Controllers/SearchFilter.php`

```php
use App\Services\FilterService;

public function filter(Request $request)
{
    $filterService = new FilterService();

    $query = Advert::with('firstImage')
        ->where('ad_status', 'active')
        ->where('sold', 'No');

    // Apply all filters
    $filterService->applyAllFilters($query, $request);

    $adverts = $query->orderWithFeatured()->paginate(20);

    // Render HTML based on device
    $agent = new Agent();
    $html = '';

    if ($agent->isMobile()) {
        foreach ($adverts as $row) {
            $html .= view('frontend.components.advert.advert-card-mobile', compact('row'))->render();
        }
    } else {
        foreach ($adverts as $row) {
            $html .= view('frontend.components.advert.advert-card', compact('row'))->render();
        }
    }

    return response()->json([
        'html' => $html,
        'hasMore' => $adverts->hasMorePages()
    ]);
}
```

---

## API Endpoints

All new filter endpoints are in `routes/api.php`:

### Car Filter
```
POST /api/search/filter-by-car
```

**Controller:** `App\Http\Controllers\Api\SearchController@filterByCarDetails`

**Parameters:**
- `condition`, `fuel_type`, `transmission`, `registration`
- `category`, `sub_category`, `brand`, `location`
- `min`, `max`, `per_page`

---

### Phone Filter
```
POST /api/search/filter-by-phone
```

**Controller:** `App\Http\Controllers\Api\SearchController@filterByPhoneDetails`

**Parameters:**
- `condition`, `device_type`
- `category`, `sub_category`, `brand`, `location`
- `min`, `max`, `per_page`

---

## Postman Collection

Both endpoints are included in:
- **File:** `postman/Marketplace-API-Complete.postman_collection.json`
- **Folder:** `04. Search`

### Test Data Included

**Car Filter Test:**
```json
{
  "condition": "Nigerian Used",
  "fuel_type": "Petrol",
  "transmission": "Automatic",
  "registration": "Registered",
  "category": 2,
  "location": "Lagos",
  "min": 500000,
  "max": 5000000,
  "per_page": 20
}
```

**Phone Filter Test:**
```json
{
  "condition": "Brand New",
  "device_type": "Smartphone",
  "category": 1,
  "sub_category": 6,
  "brand": 5,
  "location": "Lagos",
  "min": 50000,
  "max": 500000,
  "per_page": 20
}
```

---

## Best Practices

### 1. Always Include Relationships

```php
// ✅ Good
$query = Advert::with(['firstImage', 'user', 'carDetail']);
$filterService->applyCarFilters($query, $request);

// ❌ Bad - will cause N+1 queries
$query = Advert::query();
$filterService->applyCarFilters($query, $request);
```

### 2. Check for Parameters

```php
// ✅ Good - only apply if parameters exist
if ($request->hasAny(['condition', 'fuel_type'])) {
    $filterService->applyCarFilters($query, $request);
}

// ❌ Unnecessary - works but adds overhead
$filterService->applyCarFilters($query, $request); // Always runs
```

### 3. Use Appropriate Method

```php
// For standard filters only
$filterService->applyAllFilters($query, $request);

// For car-specific
$filterService->applyCarFilters($query, $request);

// Combine as needed
$filterService->applyContextFilters($query, $request);
$filterService->applyPriceFilters($query, $request);
$filterService->applyCarFilters($query, $request);
```

### 4. Validate Input

```php
$validator = Validator::make($request->all(), [
    'condition' => 'string|in:Brand New,Nigerian Used,Foreign Used',
    'fuel_type' => 'string|in:Petrol,Diesel,Electric,Hybrid',
    'transmission' => 'string|in:Automatic,Manual',
    'min' => 'integer|min:0',
    'max' => 'integer|min:0'
]);

if ($validator->fails()) {
    return response()->json(['errors' => $validator->errors()], 422);
}
```

---

## Benefits

### 1. Code Reusability
- ✅ Write once, use in web AND API
- ✅ Consistent filtering logic everywhere
- ✅ No code duplication

### 2. Maintainability
- ✅ Single source of truth
- ✅ Easy to update filtering logic
- ✅ Centralized bug fixes

### 3. Testability
- ✅ Test service independently
- ✅ Mock in controller tests
- ✅ Clear separation of concerns

### 4. Performance
- ✅ Query builder manipulation (no extra queries)
- ✅ Eager loading support
- ✅ Efficient filtering

---

## Future Enhancements

### Planned Improvements

1. **Add Validation Rules**
   ```php
   $filterService->validate($request); // Throws validation exception
   ```

2. **Filter Presets**
   ```php
   $filterService->applyPreset('popular-cars', $query);
   ```

3. **Filter Analytics**
   ```php
   $filterService->logFilterUsage($request); // Track popular filters
   ```

4. **Advanced Queries**
   ```php
   $filterService->applyAdvancedSearch($query, $request);
   // Support: OR conditions, nested filters, etc.
   ```

---

## Testing

### Unit Test Example

```php
use Tests\TestCase;
use App\Services\FilterService;
use App\Models\Advert;

class FilterServiceTest extends TestCase
{
    public function test_applies_price_filters()
    {
        $request = new Request(['min' => 1000, 'max' => 5000]);
        $filterService = new FilterService();

        $query = Advert::query();
        $filterService->applyPriceFilters($query, $request);

        $sql = $query->toSql();
        $this->assertStringContainsString('price >= ?', $sql);
        $this->assertStringContainsString('price <= ?', $sql);
    }
}
```

---

## Troubleshooting

### Issue: No results returned

**Cause:** Missing relationship

**Solution:**
```php
// Add required relationship
$query = Advert::with('carDetail'); // For car filters
$query = Advert::with('phoneDetail'); // For phone filters
```

### Issue: Slow queries

**Cause:** N+1 queries from missing eager loading

**Solution:**
```php
// Include all needed relationships
$query = Advert::with(['firstImage', 'user', 'carDetail']);
```

### Issue: Filter not working

**Cause:** Check if parameter name matches

**Solution:**
```php
// Use exact parameter names
'condition' (not 'car_condition')
'fuel_type' (not 'fuelType' or 'fuel')
```

---

## Summary

**FilterService** is now the **single source of truth** for all advert filtering across:
- ✅ Web controllers (HTML responses)
- ✅ API controllers (JSON responses)
- ✅ Any future filtering needs

**Usage:** Simple, consistent, reusable
**Performance:** Optimized query building
**Maintenance:** Single place to update

---

## Support

**Questions?** Check:
- FilterService source: `app/Services/FilterService.php`
- API implementation: `app/Http/Controllers/Api/SearchController.php`
- Web implementation: `app/Http/Controllers/SearchFilter.php`
- Postman collection: `postman/Marketplace-API-Complete.postman_collection.json`

**Issues?** Create a ticket or check logs.
