# Blade File Refactoring Analysis
## Merging Duplicate Location/Category Pages

**Date:** 2026-02-08
**Analysis By:** Claude Code

---

## Current Duplication

We have **6 blade files** that can be merged into **3 files**:

| Location-Based View | Category-Based View | Purpose |
|-------------------|-------------------|---------|
| `location-category.blade.php` | `category.blade.php` | Display category with optional location filter |
| `location-subcat.blade.php` | `sub-category.blade.php` | Display subcategory with optional location filter |
| `location-brand.blade.php` | `brand.blade.php` | Display brand with optional location filter |

**Total:** 6 files → Can be reduced to **3 files**

---

## Route Analysis

### Category-Based Routes (AdvertController)
```php
// Routes: app/Http/Controllers/AdvertController.php
Route::get('/category/{category_slug}', [AdvertController::class, 'category']);
Route::get('/category/{category_slug}/{subcat_slug}', [AdvertController::class, 'sub_category']);
Route::get('/category/{category_slug}/{subcat_slug}/{brand_slug}', [AdvertController::class, 'brand']);
```

### Location-Based Routes (SearchFilter Controller)
```php
// Routes: app/Http/Controllers/SearchFilter.php
Route::any('/{location}/{slug}', [SearchFilter::class, 'location_router']);

// location_router() determines if slug is:
// - Category → location_category()
// - SubCategory → location_subcat()
// - Brand → location_brand()
```

---

## Key Differences Between Files

### 1. **Header Include**
| File Type | Header |
|----------|--------|
| category.blade.php | `@include('frontend.layouts.header-category')` |
| location-category.blade.php | `@include('frontend.layouts.header')` |
| sub-category.blade.php | `@include('frontend.layouts.header-subcategory')` |
| location-subcat.blade.php | `@include('frontend.layouts.header')` |
| brand.blade.php | `@include('frontend.layouts.header-brand')` |
| location-brand.blade.php | `@include('frontend.layouts.header')` |

### 2. **Subcategories/Brands Listing**
**category.blade.php** has:
```blade
<!-- Subcategories List (lines 47-73) -->
@foreach($categories->take($catLimit) as $subCategory)
    <a href="{{ url('/category/' . $cat->category_slug . '/' . $subCategory->sub_cat_slug) }}">
        {{ $subCategory->sub_category }} ({{ $subCategory->advert_count }})
    </a>
@endforeach
```

**location-category.blade.php**: NO subcategories list

---

**sub-category.blade.php** has:
```blade
<!-- Brands List (lines ~40-70) -->
@foreach($brands->take($brandLimit) as $brand)
    <a href="{{ url('/category/' . $subcat->sub_cat_slug . '/' . $brand->brand_slug) }}">
        {{ $brand->brand }} ({{ $brand->advert_count }})
    </a>
@endforeach
```

**location-subcat.blade.php**: NO brands list

### 3. **Additional Filter Sections**
**Category/Sub-category pages** have:
- Buying Options (BuyDirect filter)
- Trust & Safety (Verified Sellers filter)

**Location-based pages**: Missing these sections

### 4. **JavaScript Filter Context**
**category.blade.php**:
```javascript
const filters = {
    category: {{ $cat->id }},
};
```

**location-category.blade.php**:
```javascript
const filters = {
    category: {{ $cat->id }},
    location: '{{ $location ?? '' }}',
};
```

### 5. **CSS Classes & Layout**
**category.blade.php**:
```html
<section class="w-full max-w-[95rem] mx-auto mt-3">
  <div class="grid grid-cols-12 gap-2">
      <div class="col-span-2 hidden xl:block">
```

**location-category.blade.php**:
```html
<section class="w-full md:w-5/6 mx-auto mt-3">
  <div class="grid grid-cols-12 gap-3">
      <div class="col-span-2 hidden sm:block">
```

---

## Proposed Refactoring Strategy

### Option 1: Single File with Conditional Logic ✅ **RECOMMENDED**

Merge each pair into one file using `isset($location)` to detect location-based context.

**Advantages:**
- Minimal controller changes
- Easy to maintain
- Clear conditional logic
- No code duplication

**Example for category.blade.php:**
```blade
{{-- Dynamic header based on context --}}
@if(isset($location))
    @include('frontend.layouts.header')
@else
    @include('frontend.layouts.header-category')
@endif

{{-- Show subcategories only for non-location views --}}
@if(!isset($location))
    <!-- Subcategories List -->
    @foreach($categories->take($catLimit) as $subCategory)
        ...
    @endforeach
@endif

{{-- Additional filters only for non-location views --}}
@if(!isset($location))
    <!-- Buying Options -->
    @include('frontend.components.filter.buydirect-category')

    <!-- Trust & Safety -->
    @include('frontend.components.advert.sellers-category')
@endif

{{-- JavaScript filters --}}
<script>
const filters = {
    category: {{ $cat->id }},
    @if(isset($location))
    location: '{{ $location }}',
    @endif
};
</script>
```

---

## Implementation Plan

### Phase 1: Merge Category Files

**File:** `resources/views/frontend/category.blade.php`

**Changes Required:**
1. Add header conditional logic
2. Wrap subcategories list in `@if(!isset($location))`
3. Wrap "Buying Options" and "Trust & Safety" sections
4. Add location to JS filters if exists
5. Update controllers to pass `$location` variable

**Controller Changes:**
```php
// SearchFilter.php - location_category()
public function location_category(Request $request, $location, $slug)
{
    // ... existing code ...
    return view('frontend.category', compact(
        'title', 'ads', 'user', 'cat', 'categories',
        'count_cat', 'hasMore', 'isMobile',
        'location' // ← Add this
    ));
}
```

**Delete:** `location-category.blade.php`

---

### Phase 2: Merge Sub-Category Files

**File:** `resources/views/frontend/sub-category.blade.php`

**Changes Required:**
1. Add header conditional logic
2. Wrap brands list in `@if(!isset($location))`
3. Wrap "Buying Options" and "Trust & Safety" sections
4. Add location to JS filters if exists
5. Update controllers to pass `$location` variable

**Controller Changes:**
```php
// SearchFilter.php - location_subcat()
public function location_subcat(Request $request, $location, $slug)
{
    // ... existing code ...
    // Need to add $brands query for non-location version
    $brands = DB::table('brands')
        ->leftJoin('adverts', 'brands.id', '=', 'adverts.brand')
        ->where('brands.subcat_id', $subcat->id)
        ->where(function($query) { /* active filter */ })
        ->select('brands.id', 'brands.brand', 'brands.brand_slug', DB::raw('COUNT(adverts.id) as advert_count'))
        ->groupBy('brands.id', 'brands.brand', 'brands.brand_slug')
        ->orderBy('advert_count', 'desc')
        ->get();

    return view('frontend.sub-category', compact(
        'title', 'ads', 'user', 'categories', 'subcat',
        'count_subcat', 'hasMore', 'isMobile',
        'location', 'brands', 'cat' // ← Add these
    ));
}
```

**Delete:** `location-subcat.blade.php`

---

### Phase 3: Merge Brand Files

**File:** `resources/views/frontend/brand.blade.php`

**Changes Required:**
1. Add header conditional logic
2. Add location to JS filters if exists
3. Update controllers to pass `$location` variable

**Controller Changes:**
```php
// SearchFilter.php - location_brand()
public function location_brand(Request $request, $location, $slug)
{
    // ... existing code ...
    // Need to add $cat, $subcat, $brands for non-location version

    return view('frontend.brand', compact(
        'title', 'ads', 'user', 'brand', 'brands', 'cat',
        'subcat', 'count_subcat', 'hasMore', 'isMobile',
        'location' // ← Add this
    ));
}
```

**Delete:** `location-brand.blade.php`

---

## Benefits of Refactoring

### 1. **Reduced Code Duplication**
- From 6 files to 3 files (50% reduction)
- Single source of truth for each page type
- Easier to maintain consistency

### 2. **Easier Maintenance**
- Bug fixes only need to be applied once
- New features added to one file instead of two
- CSS/layout changes unified

### 3. **Better Code Organization**
- Clear conditional logic shows what's different
- Easier to understand the relationship between views
- Reduced mental overhead

### 4. **Future-Proof**
- Adding new filter types requires changes in one place
- Easier to add more conditional sections
- Simplifies testing

---

## Testing Checklist

After refactoring, test all these scenarios:

### Category Page
- [ ] `/category/vehicles` (category-only)
- [ ] `/Lagos/vehicles` (location + category)
- [ ] Subcategories list shows on category-only
- [ ] Subcategories list hidden on location+category
- [ ] Buying Options shows on category-only
- [ ] Trust & Safety shows on category-only
- [ ] Filters work correctly in both modes
- [ ] Load More pagination works

### Sub-Category Page
- [ ] `/category/vehicles/cars` (subcat-only)
- [ ] `/Lagos/cars` (location + subcat)
- [ ] Brands list shows on subcat-only
- [ ] Brands list hidden on location+subcat
- [ ] Filters work correctly in both modes
- [ ] Load More pagination works

### Brand Page
- [ ] `/category/vehicles/cars/toyota` (brand-only)
- [ ] `/Lagos/toyota` (location + brand)
- [ ] Filters work correctly in both modes
- [ ] Load More pagination works

### Mobile Testing
- [ ] All above scenarios on mobile devices
- [ ] Filter modals work correctly
- [ ] Responsive layouts work

---

## Risk Assessment

### Low Risk ✅
- The conditional logic is simple and clear
- Controllers already have all the data needed
- No database schema changes required
- Can be rolled back easily by restoring old files

### Potential Issues
1. **Missing variables** - Some location-based views might not receive all variables
   - **Solution:** Update controllers to pass all required variables

2. **Header differences** - Different headers might have different styles/scripts
   - **Solution:** Review headers and unify or add conditional includes

3. **JavaScript context** - Filter manager might need location awareness
   - **Solution:** Already handled by location variable in filters object

---

## Implementation Steps

1. **Backup current files**
   ```bash
   cp resources/views/frontend/category.blade.php resources/views/frontend/category.blade.php.backup
   cp resources/views/frontend/location-category.blade.php resources/views/frontend/location-category.blade.php.backup
   # ... repeat for other files
   ```

2. **Update category.blade.php with conditionals**

3. **Update SearchFilter::location_category() to use 'category' view**

4. **Test thoroughly**

5. **Delete location-category.blade.php**

6. **Repeat for sub-category and brand pages**

7. **Clean up backup files after verification**

---

## Estimated Effort

- **Analysis:** ✅ Complete
- **Implementation:** ~2-3 hours
- **Testing:** ~1-2 hours
- **Total:** ~4-5 hours

---

## Conclusion

✅ **YES, these pages can and should be merged.**

The differences are minimal and can be handled cleanly with conditional logic. This refactoring will:
- Reduce code duplication by 50%
- Improve maintainability
- Make future changes easier
- Reduce bugs from having duplicate code

**Recommendation:** Proceed with Option 1 (Single File with Conditional Logic)
