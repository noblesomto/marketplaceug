<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Advert;
use App\Models\SubCategory;
use App\Models\Category;
use App\Models\Brands;
use App\Models\State;
use Jenssegers\Agent\Agent;
use App\Services\FilterService;

/**
 * @group Search
 *
 * APIs for searching and filtering adverts across the platform
 */
class SearchController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/search",
     *     summary="Search adverts with filters",
     *     tags={"Search"},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="product", type="string", description="Product search term", example="iPhone 13"),
     *             @OA\Property(property="location", type="string", description="State/Location", example="Lagos"),
     *             @OA\Property(property="category", type="integer", description="Category ID", example=1),
     *             @OA\Property(property="sub_category", type="integer", description="Sub-category ID", example=6),
     *             @OA\Property(property="brand", type="integer", description="Brand ID", example=5),
     *             @OA\Property(property="buydirect", type="string", description="Buy direct filter", example="yes"),
     *             @OA\Property(property="per_page", type="integer", description="Items per page", example=10),
     *             @OA\Property(property="page", type="integer", description="Page number", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Search results with pagination",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="pagination", type="object"),
     *             @OA\Property(property="search_params", type="object")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product' => 'nullable|string|min:3',
            'location' => 'nullable',
            'category' => 'nullable',
            'sub_category' => 'nullable',
            'brand' => 'nullable',
            'buydirect' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $query = Advert::with('firstImage')->activeNotRecentlySold();

        if ($request->filled('product')) {
            $query->where(function ($q) use ($request) {
                $q->where('ad_title', 'LIKE', '%' . $request->product . '%')
                  ->orWhere('title_slug', 'LIKE', '%' . $request->product . '%');
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('sub_category')) {
            $query->where('sub_category', $request->sub_category);
        }

        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        if ($request->filled('location')) {
            $query->where('state', $request->location);
        }

        if ($request->filled('buydirect')) {
            $query->where('buy_direct', $request->buydirect);
        }

        $ads = $query->orderWithFeatured()
                    ->paginate($request->input('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $ads->items(),
            'pagination' => [
                'current_page' => $ads->currentPage(),
                'last_page' => $ads->lastPage(),
                'per_page' => $ads->perPage(),
                'total' => $ads->total(),
                'has_more' => $ads->hasMorePages()
            ],
            'search_params' => [
                'product' => $request->input('product'),
                'location' => $request->input('location'),
                'category' => $request->input('category'),
                'sub_category' => $request->input('sub_category'),
                'brand' => $request->input('brand'),
                'buydirect' => $request->input('buydirect'),
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/search/filter",
     *     summary="Advanced filtering with price ranges",
     *     tags={"Search"},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="category", type="integer"),
     *             @OA\Property(property="sub_category", type="integer"),
     *             @OA\Property(property="brand", type="integer"),
     *             @OA\Property(property="location", type="string"),
     *             @OA\Property(property="min", type="number"),
     *             @OA\Property(property="max", type="number"),
     *             @OA\Property(property="range", type="string", enum={"under_20k", "20k_120k", "120k_1m", "1m_10m", "above_10m"})
     *         )
     *     ),
     *     @OA\Response(response=200, description="Filtered results")
     * )
     */
    public function filter(Request $request)
    {
        $query = Advert::with('firstImage')
                    ->where('ad_status', 'active')
                    ->where('sold', 'No');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('sub_category')) {
            $query->where('sub_category', $request->sub_category);
        }

        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        if ($request->filled('location')) {
            $query->where('state', $request->location);
        }

        // Price filters
        if ($request->filled('min')) {
            $query->where('price', '>=', (int) $request->input('min'));
        }

        if ($request->filled('max')) {
            $query->where('price', '<=', (int) $request->input('max'));
        }

        $range = $request->input('range');

        if ($range) {
            switch ($range) {
                case 'under_20k':
                    $query->where('price', '<', 20000);
                    break;
                case '20k_120k':
                    $query->whereBetween('price', [20000, 120000]);
                    break;
                case '120k_1m':
                    $query->whereBetween('price', [120000, 1000000]);
                    break;
                case '1m_10m':
                    $query->whereBetween('price', [1000000, 10000000]);
                    break;
                case 'above_10m':
                    $query->where('price', '>', 10000000);
                    break;
            }
        }

        $adverts = $query->orderWithFeatured()
                        ->paginate($request->input('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $adverts->items(),
            'pagination' => [
                'current_page' => $adverts->currentPage(),
                'last_page' => $adverts->lastPage(),
                'per_page' => $adverts->perPage(),
                'total' => $adverts->total(),
                'has_more' => $adverts->hasMorePages()
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/search/filter-by-seller",
     *     summary="Filter by verified/unverified sellers",
     *     tags={"Search"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="sellers", type="string", enum={"all", "yes", "no"}, description="Seller verification status")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Filtered results")
     * )
     */
    public function filterBySeller(Request $request)
    {
        $query = Advert::with('firstImage')
            ->where('ad_status', 'active')
            ->where('sold', 'No');

        $sellers = $request->input('sellers', 'all');
        if ($sellers !== 'all') {
            $query->whereHas('owner', function ($q) use ($sellers) {
                $q->where('verified', $sellers);
            });
        }

        // Context filters
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('sub_category')) {
            $query->where('sub_category', $request->sub_category);
        }

        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        if ($request->filled('location')) {
            $query->where('state', $request->location);
        }

        $adverts = $query->orderWithFeatured()
                        ->paginate($request->input('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $adverts->items(),
            'pagination' => [
                'current_page' => $adverts->currentPage(),
                'last_page' => $adverts->lastPage(),
                'per_page' => $adverts->perPage(),
                'total' => $adverts->total(),
                'has_more' => $adverts->hasMorePages()
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/search/filter-by-buydirect",
     *     summary="Filter by buy direct availability",
     *     tags={"Search"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="buydirect", type="string", description="Buy direct filter value")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Filtered results")
     * )
     */
    public function filterByBuydirect(Request $request)
    {
        $query = Advert::with('firstImage')
            ->where('ad_status', 'active')
            ->where('sold', 'No');

        if ($request->filled('buydirect')) {
            $query->where('buy_direct', $request->buydirect);
        }

        // Context filters
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('sub_category')) {
            $query->where('sub_category', $request->sub_category);
        }

        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        if ($request->filled('location')) {
            $query->where('state', $request->location);
        }

        $adverts = $query->orderWithFeatured()
                        ->paginate($request->input('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $adverts->items(),
            'pagination' => [
                'current_page' => $adverts->currentPage(),
                'last_page' => $adverts->lastPage(),
                'per_page' => $adverts->perPage(),
                'total' => $adverts->total(),
                'has_more' => $adverts->hasMorePages()
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/search/location/{location}/{slug}",
     *     summary="Search by location and category/subcategory/brand",
     *     tags={"Search"},
     *     @OA\Parameter(name="location", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Location-based search results")
     * )
     */
    public function locationSearch($location, $slug)
    {
        // Determine type based on slug existence
        if (Category::where('category_slug', $slug)->exists()) {
            return $this->locationCategory($location, $slug);
        } elseif (SubCategory::where('sub_cat_slug', $slug)->exists()) {
            return $this->locationSubcat($location, $slug);
        } elseif (Brands::where('brand_slug', $slug)->exists()) {
            return $this->locationBrand($location, $slug);
        }

        return response()->json([
            'success' => false,
            'message' => 'Resource not found'
        ], 404);
    }

    /**
     * Location + Category search
     */
    private function locationCategory($location, $slug)
    {
        $cat = Category::where('category_slug', $slug)->first();

        if (!$cat) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $ads = Advert::with('firstImage')
                    ->activeNotRecentlySold()
                    ->where('state', $location)
                    ->where('category', $cat->id)
                    ->orderWithFeatured()
                    ->paginate(request()->input('per_page', 10));

        $count_cat = Advert::activeNotRecentlySold()
                          ->where('category', $cat->id)
                          ->count();

        return response()->json([
            'success' => true,
            'data' => $ads->items(),
            'category' => $cat,
            'total_count' => $count_cat,
            'pagination' => [
                'current_page' => $ads->currentPage(),
                'last_page' => $ads->lastPage(),
                'per_page' => $ads->perPage(),
                'total' => $ads->total(),
                'has_more' => $ads->hasMorePages()
            ]
        ]);
    }

    /**
     * Location + Subcategory search
     */
    private function locationSubcat($location, $slug)
    {
        $subcat = SubCategory::where('sub_cat_slug', $slug)->first();

        if (!$subcat) {
            return response()->json([
                'success' => false,
                'message' => 'Subcategory not found'
            ], 404);
        }

        $ads = Advert::with('firstImage')
                    ->activeNotRecentlySold()
                    ->where('sold', 'No')
                    ->where('state', $location)
                    ->where('sub_category', $subcat->id)
                    ->orderWithFeatured()
                    ->paginate(request()->input('per_page', 10));

        $count_subcat = Advert::activeNotRecentlySold()
                            ->where('sub_category', $subcat->id)
                            ->count();

        return response()->json([
            'success' => true,
            'data' => $ads->items(),
            'subcategory' => $subcat,
            'total_count' => $count_subcat,
            'pagination' => [
                'current_page' => $ads->currentPage(),
                'last_page' => $ads->lastPage(),
                'per_page' => $ads->perPage(),
                'total' => $ads->total(),
                'has_more' => $ads->hasMorePages()
            ]
        ]);
    }

    /**
     * Location + Brand search
     */
    private function locationBrand($location, $slug)
    {
        $brand = Brands::where('brand_slug', $slug)->first();

        if (!$brand) {
            return response()->json([
                'success' => false,
                'message' => 'Brand not found'
            ], 404);
        }

        $ads = Advert::with('firstImage')
                    ->activeNotRecentlySold()
                    ->where('state', $location)
                    ->where('brand', $brand->id)
                    ->orderWithFeatured()
                    ->paginate(request()->input('per_page', 10));

        $count_brand = Advert::activeNotRecentlySold()
                        ->where('brand', $brand->id)
                        ->count();

        return response()->json([
            'success' => true,
            'data' => $ads->items(),
            'brand' => $brand,
            'total_count' => $count_brand,
            'pagination' => [
                'current_page' => $ads->currentPage(),
                'last_page' => $ads->lastPage(),
                'per_page' => $ads->perPage(),
                'total' => $ads->total(),
                'has_more' => $ads->hasMorePages()
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/search/load-more",
     *     summary="Load more results for infinite scroll",
     *     tags={"Search"},
     *     @OA\Parameter(name="product", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="category", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="sub_category", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="brand", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="location", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="buydirect", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="More results")
     * )
     */
    public function loadMore(Request $request)
    {
        $query = Advert::with('firstImage')->activeNotRecentlySold();

        if ($request->filled('product')) {
            $query->where(function ($q) use ($request) {
                $q->where('ad_title', 'LIKE', '%' . $request->product . '%')
                  ->orWhere('ad_id', 'LIKE', '%' . $request->product . '%');
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('sub_category')) {
            $query->where('sub_category', $request->sub_category);
        }

        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        if ($request->filled('location')) {
            $query->where('state', $request->location);
        }

        if ($request->filled('buydirect')) {
            $query->where('buy_direct', $request->buydirect);
        }

        $ads = $query->orderWithFeatured()
                    ->paginate($request->input('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $ads->items(),
            'pagination' => [
                'current_page' => $ads->currentPage(),
                'last_page'    => $ads->lastPage(),
                'per_page'     => $ads->perPage(),
                'total'        => $ads->total(),
                'has_more'     => $ads->hasMorePages(),
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/search/filters",
     *     summary="Get available filter options",
     *     tags={"Search"},
     *     @OA\Response(response=200, description="Filter options")
     * )
     */
    public function getFilters(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'categories' => Category::with('subCategories')->get(),
                'states' => State::all(),
                'price_ranges' => [
                    ['value' => 'under_20k', 'label' => 'Under ₦20,000'],
                    ['value' => '20k_120k', 'label' => '₦20,000 - ₦120,000'],
                    ['value' => '120k_1m', 'label' => '₦120,000 - ₦1,000,000'],
                    ['value' => '1m_10m', 'label' => '₦1,000,000 - ₦10,000,000'],
                    ['value' => 'above_10m', 'label' => 'Above ₦10,000,000'],
                ],
                'seller_types' => [
                    ['value' => 'all', 'label' => 'All Sellers'],
                    ['value' => 'yes', 'label' => 'Verified Sellers'],
                    ['value' => 'no', 'label' => 'Unverified Sellers'],
                ]
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/search/suggestions",
     *     summary="Get search suggestions",
     *     tags={"Search"},
     *     @OA\Parameter(name="q", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Search suggestions")
     * )
     */
    public function getSuggestions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:2'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $query = $request->input('q');

        $suggestions = Advert::where('ad_title', 'LIKE', '%' . $query . '%')
                            ->where('ad_status', 'active')
                            ->where('sold', 'No')
                            ->select('ad_title')
                            ->distinct()
                            ->limit(10)
                            ->pluck('ad_title');

        return response()->json([
            'success' => true,
            'data' => $suggestions
        ]);
    }

    /**
     * Filter adverts by car details
     *
     * @OA\Post(
     *     path="/api/search/filter-by-car",
     *     summary="Filter adverts by car-specific details",
     *     tags={"Search"},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="condition", type="string", description="Car condition", example="Nigerian Used"),
     *             @OA\Property(property="fuel_type", type="string", description="Fuel type", example="Petrol"),
     *             @OA\Property(property="transmission", type="string", description="Transmission type", example="Automatic"),
     *             @OA\Property(property="registration", type="string", description="Registration status", example="Registered"),
     *             @OA\Property(property="category", type="integer", description="Category ID"),
     *             @OA\Property(property="sub_category", type="integer", description="Sub-category ID"),
     *             @OA\Property(property="brand", type="integer", description="Brand ID"),
     *             @OA\Property(property="location", type="string", description="State/Location"),
     *             @OA\Property(property="min", type="integer", description="Minimum price"),
     *             @OA\Property(property="max", type="integer", description="Maximum price"),
     *             @OA\Property(property="per_page", type="integer", description="Items per page", example=20)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Filtered car adverts",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="pagination", type="object")
     *         )
     *     )
     * )
     */
    public function filterByCarDetails(Request $request)
    {
        $filterService = new FilterService();

        $query = Advert::with(['firstImage', 'user', 'carDetail'])
            ->where('ad_status', 'active')
            ->where('sold', 'No');

        // Apply standard filters (category, location, price)
        $filterService->applyContextFilters($query, $request);
        $filterService->applyPriceFilters($query, $request);

        // Apply car-specific filters
        if ($request->hasAny(['condition', 'fuel_type', 'transmission', 'registration'])) {
            $filterService->applyCarFilters($query, $request);
        }

        $perPage = $request->input('per_page', 20);
        $adverts = $query->orderWithFeatured()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $adverts->items(),
            'pagination' => [
                'total' => $adverts->total(),
                'per_page' => $adverts->perPage(),
                'current_page' => $adverts->currentPage(),
                'last_page' => $adverts->lastPage(),
                'from' => $adverts->firstItem(),
                'to' => $adverts->lastItem(),
                'has_more' => $adverts->hasMorePages()
            ],
            'filters_applied' => [
                'condition' => $request->input('condition'),
                'fuel_type' => $request->input('fuel_type'),
                'transmission' => $request->input('transmission'),
                'registration' => $request->input('registration'),
                'category' => $request->input('category'),
                'location' => $request->input('location'),
                'price_range' => [
                    'min' => $request->input('min'),
                    'max' => $request->input('max')
                ]
            ]
        ]);
    }

    /**
     * Filter adverts by phone details
     *
     * @OA\Post(
     *     path="/api/search/filter-by-phone",
     *     summary="Filter adverts by phone-specific details",
     *     tags={"Search"},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="condition", type="string", description="Phone condition", example="Brand New"),
     *             @OA\Property(property="device_type", type="string", description="Device type", example="Smartphone"),
     *             @OA\Property(property="category", type="integer", description="Category ID"),
     *             @OA\Property(property="sub_category", type="integer", description="Sub-category ID"),
     *             @OA\Property(property="brand", type="integer", description="Brand ID"),
     *             @OA\Property(property="location", type="string", description="State/Location"),
     *             @OA\Property(property="min", type="integer", description="Minimum price"),
     *             @OA\Property(property="max", type="integer", description="Maximum price"),
     *             @OA\Property(property="per_page", type="integer", description="Items per page", example=20)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Filtered phone adverts",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="pagination", type="object")
     *         )
     *     )
     * )
     */
    public function filterByPhoneDetails(Request $request)
    {
        $filterService = new FilterService();

        $query = Advert::with(['firstImage', 'user', 'phoneDetail'])
            ->where('ad_status', 'active')
            ->where('sold', 'No');

        // Apply standard filters (category, location, price)
        $filterService->applyContextFilters($query, $request);
        $filterService->applyPriceFilters($query, $request);

        // Apply phone-specific filters
        if ($request->hasAny(['condition', 'device_type'])) {
            $filterService->applyPhoneFilters($query, $request);
        }

        $perPage = $request->input('per_page', 20);
        $adverts = $query->orderWithFeatured()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $adverts->items(),
            'pagination' => [
                'total' => $adverts->total(),
                'per_page' => $adverts->perPage(),
                'current_page' => $adverts->currentPage(),
                'last_page' => $adverts->lastPage(),
                'from' => $adverts->firstItem(),
                'to' => $adverts->lastItem(),
                'has_more' => $adverts->hasMorePages()
            ],
            'filters_applied' => [
                'condition' => $request->input('condition'),
                'device_type' => $request->input('device_type'),
                'category' => $request->input('category'),
                'location' => $request->input('location'),
                'price_range' => [
                    'min' => $request->input('min'),
                    'max' => $request->input('max')
                ]
            ]
        ]);
    }
}
