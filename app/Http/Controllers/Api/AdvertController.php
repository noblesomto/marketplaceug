<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Advert;
use App\Models\AdvertImage;
use App\Models\Brands;
use App\Models\CarDetail;
use App\Models\Category;
use App\Models\Models;
use App\Models\PhoneDetail;
use App\Models\SubCategory;
use App\Models\User;
use App\Models\Reports;
use App\Models\State;
use App\Models\Message;
use App\Models\GigLogistic;
use App\Models\Shipping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReportMail;
use App\Services\FeaturedAdPaginator;


/**
 * @group Adverts
 *
 * Public APIs for viewing and browsing adverts
 */
class AdvertController extends Controller
{
    /**
     * Get all adverts with pagination and categorized sections
     * GET /api/adverts
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $currentPage = $request->get('page', 1);
        $section = $request->get('section', 'all');

        $response = ['success' => true, 'data' => []];

        // Gallery - Random active ads
        if ($section === 'all' || $section === 'gallery') {
            $response['data']['gallery'] = Advert::inRandomOrder()
                ->where('ad_status', 'active')
                ->where(function($query) {
                    $query->where('sold', '!=', 'Yes')
                          ->orWhere(function($query) {
                              $query->where('sold', 'Yes')
                                    ->whereNotNull('sold_date')
                                    ->where('sold_date', '>=', now()->subDays(30));
                          });
                })
                ->with('firstImage', 'owner')
                ->limit(10)
                ->get();
        }

        // Featured ads
        if ($section === 'all' || $section === 'featured') {
            $featured = Advert::inRandomOrder()
                ->where('ad_status', 'active')
                ->where('featured', 'yes')
                ->where(function($query) {
                    $query->where('sold', '!=', 'Yes')
                          ->orWhere(function($query) {
                              $query->where('sold', 'Yes')
                                    ->whereNotNull('sold_date')
                                    ->where('sold_date', '>=', now()->subDays(30));
                          });
                })
                ->with('firstImage', 'owner')
                ->limit(10)
                ->get();

            $response['data']['featured'] = $featured;
        } else {
            $featured = collect();
        }

        // Main listings
        if ($section === 'all' || !$section) {
            $excludeIds = $featured->pluck('id')->toArray();
            $recentCount = (int) ($perPage * 0.3);
            $randomCount = $perPage - $recentCount;

            $recentListings = Advert::with('firstImage', 'owner')
                ->where('ad_status', 'active')
                ->whereNotIn('id', $excludeIds)
                ->where('featured', '!=', 'yes')
                ->where(function ($query) {
                    $query->where('sold', '!=', 'Yes')
                          ->orWhere(function ($q) {
                              $q->where('sold', 'Yes')
                                ->whereNotNull('sold_date')
                                ->where('sold_date', '>=', now()->subDays(30));
                          });
                })
                ->orderByDesc('created_at')
                ->skip(($currentPage - 1) * $recentCount)
                ->limit($recentCount)
                ->get();

            $excludeIds = array_merge($excludeIds, $recentListings->pluck('id')->toArray());

            $randomListings = Advert::with('firstImage', 'owner')
                ->where('ad_status', 'active')
                ->whereNotIn('id', $excludeIds)
                ->where('featured', '!=', 'yes')
                ->where(function ($query) {
                    $query->where('sold', '!=', 'Yes')
                          ->orWhere(function ($q) {
                              $q->where('sold', 'Yes')
                                ->whereNotNull('sold_date')
                                ->where('sold_date', '>=', now()->subDays(30));
                          });
                })
                ->inRandomOrder()
                ->limit($randomCount)
                ->get();

            $mixed = collect();
            $maxCount = max($recentListings->count(), $randomListings->count());

            for ($i = 0; $i < $maxCount; $i++) {
                if ($recentListings->has($i)) $mixed->push($recentListings->get($i));
                if ($randomListings->has($i)) $mixed->push($randomListings->get($i));
            }

            $allListings = $currentPage == 1 ? $featured->concat($mixed) : $mixed;

            $totalCount = Advert::where('ad_status', 'active')
                ->where('featured', '!=', 'yes')
                ->where(function ($query) {
                    $query->where('sold', '!=', 'Yes')
                          ->orWhere(function ($q) {
                              $q->where('sold', 'Yes')
                                ->whereNotNull('sold_date')
                                ->where('sold_date', '>=', now()->subDays(30));
                          });
                })
                ->count();

            if ($currentPage == 1) $totalCount += $featured->count();

            $response['data']['listings'] = [
                'data' => $allListings,
                'current_page' => $currentPage,
                'per_page' => $perPage,
                'total' => $totalCount,
                'last_page' => ceil($totalCount / $perPage),
                'has_more' => ($currentPage * $perPage) < $totalCount
            ];
        }

        // Cars section
        if ($section === 'all' || $section === 'cars') {
            $response['data']['cars'] = Advert::inRandomOrder()
                ->where('ad_status', 'active')
                ->where('sub_category', 2)
                ->where(function($query) {
                    $query->where('sold', '!=', 'Yes')
                          ->orWhere(function($query) {
                              $query->where('sold', 'Yes')
                                    ->whereNotNull('sold_date')
                                    ->where('sold_date', '>=', now()->subDays(30));
                          });
                })
                ->with('firstImage', 'owner')
                ->orderBy('views', 'desc')
                ->limit(10)
                ->get();
        }

        // Phones section
        if ($section === 'all' || $section === 'phones') {
            $response['data']['phones'] = Advert::inRandomOrder()
                ->where('ad_status', 'active')
                ->where('sub_category', 6)
                ->where(function($query) {
                    $query->where('sold', '!=', 'Yes')
                          ->orWhere(function($query) {
                              $query->where('sold', 'Yes')
                                    ->whereNotNull('sold_date')
                                    ->where('sold_date', '>=', now()->subDays(30));
                          });
                })
                ->with('firstImage', 'owner')
                ->orderBy('views', 'desc')
                ->limit(10)
                ->get();
        }

        // Fashion section
        if ($section === 'all' || $section === 'fashion') {
            $response['data']['fashion'] = Advert::inRandomOrder()
                ->where('ad_status', 'active')
                ->where('category', 5)
                ->where(function($query) {
                    $query->where('sold', '!=', 'Yes')
                          ->orWhere(function($query) {
                              $query->where('sold', 'Yes')
                                    ->whereNotNull('sold_date')
                                    ->where('sold_date', '>=', now()->subDays(30));
                          });
                })
                ->with('firstImage', 'owner')
                ->orderBy('views', 'desc')
                ->limit(10)
                ->get();
        }

        return response()->json($response);
    }

    /**
     * Get advert details
     * GET /api/adverts/{id}
     */
    public function show($id)
    {
        $ad = Advert::with(['owner'])->find($id);

        if (!$ad) {
            return response()->json([
                'success' => false,
                'message' => 'Advert not found'
            ], 404);
        }

        // Get images with all required conversions
        $images = $ad->getMedia('images')->map(function ($media) {
            return [
                'id' => $media->id,
                'original' => $media->getUrl(),
                'large' => $media->getUrl('large'),
                'optimized' => $media->getUrl('optimized'),
                'thumbnail' => $media->getUrl('thumbnail'),
                'thumb_sm' => $media->getUrl('thumb-sm'),  // Mobile optimized
                'thumb_md' => $media->getUrl('thumb-md'),  // Desktop optimized
                'order' => $media->getCustomProperty('position', $media->order_column),
                'name' => $media->name,
                'size' => $media->size,
            ];
        })->sortBy('order')->values();

        $data = [
            'ad' => $ad,
            'images' => $images,
            'ad_owner' => User::where('user_id', $ad->user_id)->first(),
            'cat' => Category::find($ad->category),
            'sub_cat' => SubCategory::find($ad->sub_category),
            'brand' => Brands::find($ad->brand),
            'count_ads' => Advert::where('user_id', $ad->user_id)->count()
        ];

        // Car details
        $data['car'] = CarDetail::where('advert_id', $id)->first();
        if ($data['car']) {
            $data['model'] = Models::find($data['car']->model);
        }

        // Phone details
        $data['phone'] = PhoneDetail::where('advert_id', $id)->first();
        if ($data['phone']) {
            $data['model'] = Models::find($data['phone']->model);
        }


        // Related adverts with images
        $relatedAdverts = Advert::inRandomOrder()
            ->where('user_id', $ad->user_id)
            ->activeNotRecentlySold()
            ->where('id', '!=', $id)
            ->limit(6)
            ->get();

        $data['adverts'] = $relatedAdverts->map(function ($advert) {
            return [
                'id' => $advert->id,
                'ad_id' => $advert->ad_id,
                'ad_title' => $advert->ad_title,
                'price' => $advert->price,
                'price_type' => $advert->price_type,
                'state' => $advert->state,
                'buy_direct' => $advert->buy_direct,
                'sold' => $advert->sold,
                'sold_date' => $advert->sold_date,
                'featured' => $advert->featured,
                'image_large' => $advert->getFirstImageUrl('large'),
                'image_optimized' => $advert->getFirstImageUrl('optimized'),
                'image_thumb' => $advert->getFirstImageUrl('thumbnail'),
            ];
        });

        // Similar adverts with images
        $similarAds = Advert::inRandomOrder()
            ->where(function($q) use ($ad) {
                $q->where('ad_title', 'LIKE', '%' . $ad->ad_title . '%')
                  ->orWhere('category', $ad->category);
            })
            ->where('id', '!=', $id)
            ->activeNotRecentlySold()
            ->where('user_id', '!=', $ad->user_id)
            ->limit(20)
            ->get();

        $data['similar_ads'] = $similarAds->map(function ($advert) {
            return [
                'id' => $advert->id,
                'ad_id' => $advert->ad_id,
                'ad_title' => $advert->ad_title,
                'price' => $advert->price,
                'price_type' => $advert->price_type,
                'state' => $advert->state,
                'buy_direct' => $advert->buy_direct,
                'sold' => $advert->sold,
                'sold_date' => $advert->sold_date,
                'state' => $advert->state,
                'featured' => $advert->featured,
                'image_large' => $advert->getFirstImageUrl('large'),
                'image_optimized' => $advert->getFirstImageUrl('optimized'),
                'image_thumb' => $advert->getFirstImageUrl('thumbnail'),
            ];
        });

        // Increment views
        Advert::where('id', $id)->increment('views', 1);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get related adverts for a given ad.
     *
     * Returns ads in the SAME category AND same sub-category.
     * For vehicles (category 1) the same car model is ranked highest.
     * Results ordered by relevance:
     *   Cars  → 1) same model + similar title  2) same model  3) similar title  4) rest
     *   Other → 1) similar title  2) rest
     *
     * @group Adverts
     *
     * @urlParam id integer required The ad_id of the source advert. Example: 12345
     * @queryParam page integer Page number. Default: 1. Example: 1
     * @queryParam per_page integer Items per page (max 40). Default: 20. Example: 20
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 10,
     *       "ad_id": 54321,
     *       "ad_title": "Toyota Camry 2020",
     *       "price": 8500000,
     *       "contact_price": "no",
     *       "price_type": "Fixed",
     *       "state": "Lagos",
     *       "state_slug": "lagos",
     *       "title_slug": "toyota-camry-2020",
     *       "category": 1,
     *       "sub_category": 2,
     *       "car_model_id": "15",
     *       "buy_direct": "No",
     *       "featured": "No",
     *       "sold": "No",
     *       "views": 80,
     *       "image_thumb": "https://marketplace.ng/...",
     *       "image_optimized": "https://marketplace.ng/..."
     *     }
     *   ],
     *   "meta": {
     *     "total": 30,
     *     "page": 1,
     *     "per_page": 20,
     *     "has_more": true,
     *     "source_ad": {
     *       "ad_id": 12345,
     *       "ad_title": "Toyota Camry 2020",
     *       "category": 1,
     *       "sub_category": 2,
     *       "car_model_id": "15"
     *     }
     *   }
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Advert not found"
     * }
     */
    public function related(Request $request, $id)
    {
        $ad = Advert::where('ad_id', $id)->first();

        if (!$ad) {
            return response()->json([
                'success' => false,
                'message' => 'Advert not found',
            ], 404);
        }

        $perPage = min((int) $request->get('per_page', 20), 40);
        $page    = max(1, (int) $request->get('page', 1));

        // First 2 words of the title for keyword matching
        $titleWords = array_filter(explode(' ', $ad->ad_title));
        $keyword    = implode(' ', array_slice(array_values($titleWords), 0, 2));

        // Progressive fallback — tries each level until results are found:
        //   1. category + sub_category + brand
        //   2. category + sub_category
        //   3. category only
        //   4. any active ads (last resort)
        $baseQuery = (function () use ($ad, $keyword) {
            $titleOrder = ['CASE WHEN ad_title LIKE ? THEN 1 ELSE 2 END ASC, RAND()', ['%' . $keyword . '%']];
            $base       = fn () => Advert::where('id', '!=', $ad->id)->activeNotRecentlySold();

            if ($ad->brand) {
                $q = $base()
                    ->where('category', $ad->category)
                    ->where('sub_category', $ad->sub_category)
                    ->where('brand', $ad->brand);
                if ((clone $q)->count() > 0) {
                    return $q->orderByRaw(...$titleOrder);
                }
            }

            if ($ad->sub_category) {
                $q = $base()
                    ->where('category', $ad->category)
                    ->where('sub_category', $ad->sub_category);
                if ((clone $q)->count() > 0) {
                    return $q->orderByRaw(...$titleOrder);
                }
            }

            $q = $base()->where('category', $ad->category);
            if ((clone $q)->count() > 0) {
                return $q->orderByRaw(...$titleOrder);
            }

            return $base()->inRandomOrder();
        })();

        $total = (clone $baseQuery)->count();
        $ads   = $baseQuery->skip(($page - 1) * $perPage)->take($perPage)->get();

        $data = $ads->map(function ($advert) {
            return [
                'id'             => $advert->id,
                'ad_id'          => $advert->ad_id,
                'ad_title'       => $advert->ad_title,
                'price'          => $advert->price,
                'contact_price'  => $advert->contact_price,
                'price_type'     => $advert->price_type,
                'state'          => $advert->state,
                'state_slug'     => $advert->state_slug,
                'title_slug'     => $advert->title_slug,
                'category'       => $advert->category,
                'sub_category'   => $advert->sub_category,
                'brand_id'       => $advert->brand ?: null,
                'buy_direct'     => $advert->buy_direct,
                'featured'       => $advert->featured,
                'sold'           => $advert->sold,
                'views'          => $advert->views,
                'image_thumb'    => $advert->getFirstImageUrl('thumbnail'),
                'image_optimized'=> $advert->getFirstImageUrl('optimized'),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
            'meta'    => [
                'total'      => $total,
                'page'       => $page,
                'per_page'   => $perPage,
                'has_more'   => $total > ($page * $perPage),
                'source_ad'  => [
                    'ad_id'        => $ad->ad_id,
                    'ad_title'     => $ad->ad_title,
                    'category'     => $ad->category,
                    'sub_category' => $ad->sub_category,
                    'brand_id'     => $ad->brand ?: null,
                ],
            ],
        ]);
    }

    /**
     * Get seller storefront — profile, ratings, followers and paginated ads
     * GET /api/adverts/seller/{seller_id}?page=1&per_page=20
     *
     * seller_id: the seller's unique user_id (5-char code)
     */
    public function sellerAdverts($seller_id, Request $request)
    {
        $owner = User::where('user_id', $seller_id)->first();

        if (!$owner) {
            return response()->json([
                'success' => false,
                'message' => 'Seller not found'
            ], 404);
        }

        $perPage = (int) $request->get('per_page', 20);

        $ads = Advert::with('firstImage', 'owner')
            ->where('user_id', $seller_id)
            ->activeNotRecentlySold()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $feedbackAverages = get_user_feedback_averages($owner->user_id);
        $feedbackLabels   = feedback_rating_labels($owner->user_id);

        return response()->json([
            'success' => true,
            'data' => [
                'seller' => [
                    'user_id'      => $owner->user_id,
                    'name'         => $owner->name,
                    'slug'         => \Illuminate\Support\Str::slug($owner->name),
                    'store_url'    => url('/seller/' . \Illuminate\Support\Str::slug($owner->name) . '/' . $owner->user_id),
                    'avatar_url'   => $owner->profile_thumbnail_url,
                    'acc_type'     => $owner->acc_type,
                    'verified'     => $owner->verified === 'yes',
                    'city'         => $owner->city,
                    'state'        => $owner->state,
                    'member_since' => $owner->created_at->format('F Y'),
                    'followers'    => countUserFollowers($owner->user_id),
                    'ads_count'    => Advert::where('user_id', $seller_id)->activeNotRecentlySold()->count(),
                    'feedback' => [
                        'review_count' => $feedbackAverages['count'] ?? 0,
                        'satisfaction' => $feedbackLabels['satisfaction'],
                        'friendly'     => $feedbackLabels['friendly'],
                        'reliable'     => $feedbackLabels['reliable'],
                    ],
                ],
                'ads' => $ads,
            ]
        ]);
    }

    /**
     * Get all categories with counts
     * GET /api/categories
     */
    public function categories()
    {
        $categoryCounts = Category::leftJoin('adverts', function($join) {
                $join->on('categories.id', '=', 'adverts.category')
                     ->where('adverts.ad_status', 1)
                     ->where(function($q) {
                         $q->where('adverts.sold_date', '>=', now()->subDays(30))
                           ->orWhereNull('adverts.sold_date');
                     });
            })
            ->leftJoin('sub_categories as sc', function($join) {
                $join->on('adverts.sub_category', '=', 'sc.id')
                    ->orOn('categories.id', '=', 'sc.cat_id');
            })
            ->selectRaw('categories.id, categories.category AS category_name, categories.category_slug,
                        sc.id as sub_category_id, sc.sub_category AS sub_category_name, sc.sub_cat_slug,
                        COUNT(DISTINCT adverts.id) AS advert_count')
            ->groupBy('categories.id', 'categories.category', 'categories.category_slug',
                     'sc.id', 'sc.sub_category', 'sc.sub_cat_slug')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categoryCounts
        ]);
    }

    /**
     * Get adverts by category
     * GET /api/categories/{category_slug}
     */
    public function categoryAdverts($category_slug, Request $request)
    {
        $cat = Category::where('category_slug', $category_slug)->first();

        if (!$cat) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $page = $request->get('page', 1);
        $result = (new FeaturedAdPaginator($page))
            ->filters(['category' => $cat->id])
            ->get();

        $count_cat = Advert::activeNotRecentlySold()
                          ->where('category', $cat->id)
                          ->count();

        $subcategories = DB::table('sub_categories')
            ->leftJoin('adverts', function ($join) {
                $join->on('sub_categories.id', '=', 'adverts.sub_category')
                    ->where('adverts.ad_status', 1)
                    ->where(function ($q) {
                        $q->where('adverts.sold_date', '>=', now()->subDays(30))
                          ->orWhereNull('adverts.sold_date');
                    });
            })
            ->where('sub_categories.cat_id', $cat->id)
            ->select(
                'sub_categories.id',
                'sub_categories.sub_category',
                'sub_categories.sub_cat_slug',
                DB::raw('COUNT(adverts.id) as advert_count')
            )
            ->groupBy('sub_categories.id', 'sub_categories.sub_category', 'sub_categories.sub_cat_slug')
            ->orderBy('advert_count', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'ads' => $result['ads'],
                'cat' => $cat,
                'count_cat' => $count_cat,
                'subcategories' => $subcategories,
                'has_more' => $result['hasMore'],
                'next_page' => $result['nextPage']
            ]
        ]);
    }

    /**
     * Get adverts by subcategory
     * GET /api/categories/{category_slug}/{subcat_slug}
     */
    public function subcategoryAdverts($category_slug, $subcat_slug, Request $request)
    {
        $cat = Category::where('category_slug', $category_slug)->first();

        if (!$cat) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $subcat = SubCategory::where('sub_cat_slug', $subcat_slug)
                             ->where('cat_id', $cat->id)
                             ->first();

        if (!$subcat) {
            return response()->json([
                'success' => false,
                'message' => 'Subcategory not found'
            ], 404);
        }

        $page = $request->get('page', 1);
        $result = (new FeaturedAdPaginator($page))
            ->filters(['sub_category' => $subcat->id])
            ->get();

        $count_subcat = Advert::activeNotRecentlySold()
                            ->where('sub_category', $subcat->id)
                            ->count();

        $brands = DB::table('brands')
            ->leftJoin('adverts', 'brands.id', '=', 'adverts.brand')
            ->where('brands.subcat_id', $subcat->id)
            ->where(function($query) {
                $query->where('adverts.ad_status', 1)
                      ->where(function($q) {
                          $q->where('adverts.sold_date', '>=', now()->subDays(30))
                            ->orWhereNull('adverts.sold_date');
                      });
            })
            ->select('brands.id', 'brands.brand', 'brands.brand_slug', DB::raw('COUNT(adverts.id) as advert_count'))
            ->groupBy('brands.id', 'brands.brand', 'brands.brand_slug')
            ->orderBy('advert_count', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'ads' => $result['ads'],
                'subcat' => $subcat,
                'count_subcat' => $count_subcat,
                'brands' => $brands,
                'has_more' => $result['hasMore'],
                'next_page' => $result['nextPage']
            ]
        ]);
    }

    /**
     * Get adverts by brand
     * GET /api/brands/{category_slug}/{subcat_slug}/{brand_slug}
     */
    public function brandAdverts($category_slug, $subcat_slug, $brand_slug, Request $request)
    {
        $cat = Category::where('category_slug', $category_slug)->first();

        if (!$cat) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $subcat = SubCategory::where('sub_cat_slug', $subcat_slug)
                             ->where('cat_id', $cat->id)
                             ->first();

        if (!$subcat) {
            return response()->json([
                'success' => false,
                'message' => 'Subcategory not found'
            ], 404);
        }

        $brand = Brands::where('brand_slug', $brand_slug)
                       ->where('subcat_id', $subcat->id)
                       ->first();

        if (!$brand) {
            return response()->json([
                'success' => false,
                'message' => 'Brand not found'
            ], 404);
        }

        $page = $request->get('page', 1);
        $result = (new FeaturedAdPaginator($page))
            ->filters(['brand' => $brand->id])
            ->get();

        $count_subcat = Advert::activeNotRecentlySold()
                        ->where('sub_category', $subcat->id)
                        ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'ads' => $result['ads'],
                'brand' => $brand,
                'subcat' => $subcat,
                'count_subcat' => $count_subcat,
                'has_more' => $result['hasMore'],
                'next_page' => $result['nextPage']
            ]
        ]);
    }

    /**
     * Get adverts by location
     * GET /api/location/{state_slug}
     */
    public function locationAdverts($state_slug, Request $request)
    {
        $page = $request->get('page', 1);
        $result = (new FeaturedAdPaginator($page))
            ->filters(['state_slug' => $state_slug])
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'ads' => $result['ads'],
                'location' => $state_slug,
                'has_more' => $result['hasMore'],
                'next_page' => $result['nextPage']
            ]
        ]);
    }

    /**
     * Report an advert
     *
     * Submit a report for an inappropriate or suspicious advert.
     * The report is sent to the admin team for review.
     *
     * @group Adverts
     * @authenticated
     *
     * @urlParam id integer required The advert ID to report. Example: 123
     *
     * @bodyParam subject string required Report subject/category. Example: Spam or misleading content
     * @bodyParam message string required Detailed description of the issue. Example: This advert contains false information about the product.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Your report has been received"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Advert not found"
     * }
     * @response 422 {
     *   "success": false,
     *   "errors": {
     *     "subject": ["The subject field is required."]
     *   }
     * }
     */
    public function reportAdvert($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $advert = Advert::find($id);

        if (!$advert) {
            return response()->json([
                'success' => false,
                'message' => 'Advert not found'
            ], 404);
        }

        $user = auth()->user();

        Reports::updateOrCreate(
            [
                'advert_id' => $id,
                'user_id' => $user->id,
            ],
            [
                'subject' => $request->subject,
                'message' => $request->message,
            ]
        );

        $details = [
            'advert' => $advert->ad_title,
            'name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'subject' => $request->subject,
            'message' => $request->message,
        ];

        try {
            Mail::to(config('global.admin_email'))->send(new ReportMail($details));
        } catch (\Exception $e) {
            // Log but don't fail the request
            \Log::error('Report email failed', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Your report has been received'
        ]);
    }

    /**
     * Apply for a job advert
     * POST /api/adverts/{id}/apply
     */
    public function applyJob($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $advert = Advert::find($id);

        if (!$advert) {
            return response()->json([
                'success' => false,
                'message' => 'Advert not found'
            ], 404);
        }

        $user = auth()->user();

        Message::create([
            'advert_id' => $id,
            'sender_id' => $user->id,
            'receiver_id' => $advert->user_id,
            'message_content' => $request->message,
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'You have successfully applied for the job'
        ]);
    }

    /**
     * Get featured adverts
     * GET /api/adverts/featured
     */
    public function featuredAdverts()
    {
        $featured = Advert::inRandomOrder()
            ->where('ad_status', 'active')
            ->where('featured', 'yes')
            ->where(function($query) {
                $query->where('sold', '!=', 'Yes')
                      ->orWhere(function($query) {
                          $query->where('sold', 'Yes')
                                ->whereNotNull('sold_date')
                                ->where('sold_date', '>=', now()->subDays(30));
                      });
            })
            ->with('firstImage', 'owner')
            ->orderBy('views', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $featured
        ]);
    }

    /**
     * Load more adverts (pagination helper)
     * GET /api/adverts/load-more
     */
    public function loadMore(Request $request)
    {
        $page = $request->get('page', 1);
        $filters = $request->only(['category', 'sub_category', 'brand', 'model', 'state', 'state_slug', 'city']);

        $result = (new FeaturedAdPaginator($page))
            ->filters($filters)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'ads' => $result['ads'],
                'has_more' => $result['hasMore'],
                'next_page' => $result['nextPage']
            ]
        ]);
    }

    /**
     * Calculate shipping cost for direct purchase
     * POST /api/shipping/calculate/{ad_id}
     */
    public function calculate_shipping(Request $request, $id)
    {
        //dd($request);
        try {
            // Validate input
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'city' => 'required|integer',
                'state' => 'required|integer',
                'shipping_selected' => 'required|integer',
                //'ship_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get authenticated user
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }

            // Get ad details
            $ad = Advert::with('images', 'shippings')->findOrFail($id);


            // Get state and city details
            $sender_station = State::where('name', $ad->state)->firstOrFail();
            $reciever_station = State::findOrFail($request->state);
            $reciever_city = GigLogistic::findOrFail($request->city);

            $reciever_address = $reciever_city->city . ", " . $reciever_station->name;


            // Prepare shipping calculation details
            $details = [
                'advert_id' => $id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'reciever_station' => $reciever_station->station_id,
                'reciever_address' => $reciever_address,
                'sender_station' => $sender_station->station_id,
                'sender_address' => $ad->lga . ', ' . $ad->state,
                'ad_title' => $ad->ad_title,
                'ad_price' => $ad->price,
                'ad_des' => $ad->description,
            ];

            // Call shipping cost calculation using API LocationController
            $shippingResponse = app()->make(\App\Http\Controllers\Api\LocationController::class)
                ->calculateShippingCost(new Request($details));

            $responseData = $shippingResponse->getData();

            if (!$responseData->success) {
                return response()->json([
                    'success' => false,
                    'message' => $responseData->error ?? 'Failed to calculate shipping cost'
                ], 400);
            }

            // Calculate commission (2% for items >= ₦300,000, 3% otherwise)
            $commission = $responseData->data->DeclaredValue >= 300000
                ? 0.02 * $responseData->data->DeclaredValue
                : 0.03 * $responseData->data->DeclaredValue;

            $shipping_cost = $responseData->data->GrandTotal ?? 0;
            $grand_total = $responseData->data->DeclaredValue + $shipping_cost + $commission;

            // Get shipping method details
            $shipping_method = Shipping::findOrFail($request->shipping_selected);
            //dd($ad->ad_title);
            return response()->json([
                'success' => true,
                'data' => [
                    'ad' => [
                        'id' => $ad->id,
                        'title' => $ad->ad_title,
                        'price' => $ad->price,
                        'image_large' => $ad->getFirstImageUrl('large'),
                        'image_optimized' => $ad->getFirstImageUrl('optimized'),
                        'image_thumb' => $ad->getFirstImageUrl('thumbnail'),
                    ],
                    'shipping_cost' => $shipping_cost,
                    'commission' => $commission,
                    'grand_total' => $grand_total,
                    'shipping_details' => [
                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'phone' => $request->phone,
                        'receiver_state' => [
                            'id' => $reciever_station->id,
                            'name' => $reciever_station->name,
                            'station_id' => $reciever_station->station_id,
                        ],
                        'receiver_city' => [
                            'id' => $reciever_city->id,
                            'city' => $reciever_city->city,
                        ],
                        'receiver_address' => $reciever_address,
                        'sender_address' => $ad->lga . ', ' . $ad->state,
                    ],
                    'shipping_method' => [
                        'id' => $shipping_method->id,
                        'name' => $shipping_method->name,
                        'description' => $shipping_method->description,
                    ],
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while calculating shipping',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get direct purchase details
     * GET /api/buy-direct/{ad_id}
     */
    public function buy_direct(Request $request, $id)
    {
        try {
            // Get authenticated user
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }

            $ad = Advert::with('images', 'shippings')->where('id', $id)->firstOrFail();
            $states = State::all();

            return response()->json([
                'success' => true,
                'data' => [
                    'ad' => $ad,
                    'user' => $user,
                    'states' => $states,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch purchase details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment details for direct purchase
     * GET /api/buy-direct-payment/{ad_id}
     */
    public function buy_direct_payment(Request $request, $id)
    {
        try {
            // Get authenticated user
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }

            $ad = Advert::with('images', 'shippings')->where('id', $id)->firstOrFail();

            // Shipping data should be passed from previous step or stored
            // For API, we expect client to send this data
            $validator = Validator::make($request->all(), [
                'shipping_data' => 'required|array',
                'shipping_method_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shipping data required',
                    'errors' => $validator->errors()
                ], 422);
            }

            $shipping_method = Shipping::findOrFail($request->shipping_method_id);

            return response()->json([
                'success' => true,
                'data' => [
                    'ad' => $ad,
                    'user' => $user,
                    'shipping_method' => $shipping_method,
                    'shipping_data' => $request->shipping_data,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payment details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
