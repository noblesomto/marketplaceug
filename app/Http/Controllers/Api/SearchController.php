<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Advert;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Brands;
use App\Models\User;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SearchController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/search",
     *     summary="Search adverts with filters",
     *     tags={"Search"},
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Search query",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Category ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sub_category",
     *         in="query",
     *         description="Subcategory ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="brand",
     *         in="query",
     *         description="Brand ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="location",
     *         in="query",
     *         description="State/location",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="min_price",
     *         in="query",
     *         description="Minimum price",
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(
     *         name="max_price",
     *         in="query",
     *         description="Maximum price",
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(
     *         name="price_range",
     *         in="query",
     *         description="Price range category",
     *         @OA\Schema(type="string", enum={"under_20k", "20k_120k", "120k_1m", "1m_10m", "above_10m"})
     *     ),
     *     @OA\Parameter(
     *         name="seller_type",
     *         in="query",
     *         description="Seller verification status",
     *         @OA\Schema(type="string", enum={"all", "yes", "no"})
     *     ),
     *     @OA\Parameter(
     *         name="buy_direct",
     *         in="query",
     *         description="Buy direct availability",
     *         @OA\Schema(type="string", enum={"yes", "no"})
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Search results",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/AdvertList")
     *         )
     *     )
     * )
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q' => 'nullable|string|min:3',
            'category' => 'nullable|integer|exists:categories,id',
            'sub_category' => 'nullable|integer|exists:sub_categories,id',
            'brand' => 'nullable|integer|exists:brands,id',
            'location' => 'nullable|string',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'price_range' => 'nullable|string|in:under_20k,20k_120k,120k_1m,1m_10m,above_10m',
            'seller_type' => 'nullable|string|in:all,yes,no',
            'buy_direct' => 'nullable|string|in:yes,no',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $query = Advert::with(['firstImage', 'owner', 'category', 'subCategory', 'brand'])
            ->activeNotRecentlySold();

        // Search query
        if ($request->filled('q')) {
            $query->where('ad_title', 'LIKE', '%' . $request->q . '%');
        }

        // Category filters
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('sub_category')) {
            $query->where('sub_category', $request->sub_category);
        }

        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        // Location filter
        if ($request->filled('location')) {
            $query->where('state', $request->location);
        }

        // Price filters
        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->max_price);
        }

        if ($request->filled('price_range')) {
            switch ($request->price_range) {
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

        // Seller type filter
        if ($request->filled('seller_type') && $request->seller_type !== 'all') {
            $query->whereHas('owner', function ($q) use ($request) {
                $q->where('verified', $request->seller_type);
            });
        }

        // Buy direct filter
        if ($request->filled('buy_direct')) {
            $query->where('buy_direct', $request->buy_direct);
        }

        // Order and paginate
        $ads = $query->orderWithFeatured()
            ->paginate($request->get('per_page', 10))
            ->appends($request->except('page'));

        return response()->json([
            'success' => true,
            'data' => $ads
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/search/location/{location}/{slug}",
     *     summary="Search by location and category/brand/subcategory",
     *     tags={"Search"},
     *     @OA\Parameter(
     *         name="location",
     *         in="path",
     *         required=true,
     *         description="Location/state",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Category, subcategory, or brand slug",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Location-based search results",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="type", type="string"),
     *                 @OA\Property(property="item", type="object"),
     *                 @OA\Property(property="ads", ref="#/components/schemas/AdvertList"),
     *                 @OA\Property(property="count", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category/Subcategory/Brand not found"
     *     )
     * )
     */
    public function locationSearch($location, $slug)
    {
        $category = Category::where('category_slug', $slug)->first();
        $subcategory = SubCategory::where('sub_cat_slug', $slug)->first();
        $brand = Brands::where('brand_slug', $slug)->first();

        if ($category) {
            return $this->locationCategorySearch($location, $category);
        } elseif ($subcategory) {
            return $this->locationSubcategorySearch($location, $subcategory);
        } elseif ($brand) {
            return $this->locationBrandSearch($location, $brand);
        }

        return response()->json([
            'success' => false,
            'message' => 'No matching category, subcategory, or brand found'
        ], 404);
    }

    private function locationCategorySearch($location, $category)
    {
        $ads = Advert::with(['firstImage', 'owner'])
            ->activeNotRecentlySold()
            ->where('state', $location)
            ->where('category', $category->id)
            ->orderWithFeatured()
            ->paginate(request()->get('per_page', 10));

        $count = Advert::activeNotRecentlySold()
            ->where('category', $category->id)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'type' => 'category',
                'item' => $category,
                'ads' => $ads,
                'count' => $count
            ]
        ]);
    }

    private function locationSubcategorySearch($location, $subcategory)
    {
        $ads = Advert::with(['firstImage', 'owner'])
            ->activeNotRecentlySold()
            ->where('state', $location)
            ->where('sub_category', $subcategory->id)
            ->orderWithFeatured()
            ->paginate(request()->get('per_page', 10));

        $count = Advert::activeNotRecentlySold()
            ->where('sub_category', $subcategory->id)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'type' => 'subcategory',
                'item' => $subcategory,
                'ads' => $ads,
                'count' => $count
            ]
        ]);
    }

    private function locationBrandSearch($location, $brand)
    {
        $ads = Advert::with(['firstImage', 'owner'])
            ->activeNotRecentlySold()
            ->where('state', $location)
            ->where('brand', $brand->id)
            ->orderWithFeatured()
            ->paginate(request()->get('per_page', 10));

        $count = Advert::activeNotRecentlySold()
            ->where('brand', $brand->id)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'type' => 'brand',
                'item' => $brand,
                'ads' => $ads,
                'count' => $count
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/search/filters",
     *     summary="Get available search filters",
     *     tags={"Search"},
     *     @OA\Response(
     *         response=200,
     *         description="Available filters",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="categories", type="array", @OA\Items(ref="#/components/schemas/Category")),
     *                 @OA\Property(property="locations", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="price_ranges", type="array", @OA\Items(type="object",
     *                     @OA\Property(property="value", type="string"),
     *                     @OA\Property(property="label", type="string")
     *                 ))
     *             )
     *         )
     *     )
     * )
     */
    public function getFilters()
    {
        $categories = Category::with('subCategories')->get();

        $locations = Advert::where('ad_status', 1)
            ->where(function($query) {
                $query->where('sold', '!=', 'Yes')
                      ->orWhere(function($q) {
                          $q->where('sold', 'Yes')
                            ->whereNotNull('sold_date')
                            ->where('sold_date', '>=', now()->subDays(7));
                      });
            })
            ->distinct()
            ->pluck('state')
            ->filter()
            ->values();

        $priceRanges = [
            ['value' => 'under_20k', 'label' => 'Under ₦20,000'],
            ['value' => '20k_120k', 'label' => '₦20,000 - ₦120,000'],
            ['value' => '120k_1m', 'label' => '₦120,000 - ₦1,000,000'],
            ['value' => '1m_10m', 'label' => '₦1,000,000 - ₦10,000,000'],
            ['value' => 'above_10m', 'label' => 'Above ₦10,000,000']
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories,
                'locations' => $locations,
                'price_ranges' => $priceRanges
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/search/suggestions",
     *     summary="Get search suggestions",
     *     tags={"Search"},
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         required=true,
     *         description="Search query",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of suggestions",
     *         @OA\Schema(type="integer", default=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Search suggestions",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="adverts", type="array", @OA\Items(ref="#/components/schemas/Advert")),
     *                 @OA\Property(property="categories", type="array", @OA\Items(ref="#/components/schemas/Category")),
     *                 @OA\Property(property="brands", type="array", @OA\Items(ref="#/components/schemas/Brand"))
     *             )
     *         )
     *     )
     * )
     */
    public function getSuggestions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:2',
            'limit' => 'nullable|integer|min:1|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $limit = $request->get('limit', 5);
        $query = $request->q;

        $adverts = Advert::with('firstImage')
            ->activeNotRecentlySold()
            ->where('ad_title', 'LIKE', '%' . $query . '%')
            ->orderBy('views', 'desc')
            ->limit($limit)
            ->get();

        $categories = Category::where('category', 'LIKE', '%' . $query . '%')
            ->limit($limit)
            ->get();

        $brands = Brands::where('brand', 'LIKE', '%' . $query . '%')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'adverts' => $adverts,
                'categories' => $categories,
                'brands' => $brands
            ]
        ]);
    }
}
