<?php

namespace App\Http\Controllers;
use App\Http\Controllers\LocationController;
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
use App\Models\Shipping;
use App\Models\GigLogistic;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Rules\ReCaptcha;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\FeaturedAdPaginator;
use App\Mail\ReportMail;
use Mail;
use Jenssegers\Agent\Agent;
use App\Traits\HasUserSession;


class AdvertController extends Controller
{
    use HasUserSession;
    public function index(Request $request)
    {
        $title = config('global.site_name') . " | " . config('global.site_title');
        /*
        $gallery = Advert::inRandomOrder()
            //->where('featured', "Yes")
            ->where('sold', 'No')
            ->where('ad_status', 'active')
            ->whereHas('boost', function($query) {
                $query->where('boost_status', 'active');
            })
            ->with(['firstImage', 'boost' => function($query) {
                $query->where('boost_status', 'active');
            }])
            ->get()
            ->filter(function ($advert) {
                return $advert->boost->contains(function ($boost) {
                    return $boost->is_active;
                });
            })
            ->take(6) // Take only 6 after filtering
            ->values(); // Reindex collection
        */
        $agent = new Agent();
        if (request()->has('view')) {
            $isMobile = request()->get('view') === 'mobile';
        } else {
            $isMobile = $agent->isMobile() || $agent->isTablet();
        }

        // Configuration
        $perPage = 30;
        $featuredLimit = $isMobile ? 8 : 10;
        $currentPage = request()->get('page', 1);

        // Get featured ads (only on first page)
        if ($currentPage == 1) {
            $featured = Advert::with('firstImage', 'owner')
                ->activeNotSold()
                ->where('featured', 'yes')
                ->inRandomOrder()
                ->limit($featuredLimit)
                ->get();

            $featuredCount = $featured->count();
            $remainingSlots = $perPage - $featuredCount;
        } else {
            $featured = collect();
            $featuredCount = 0;
            $remainingSlots = $perPage;
        }

        // Fetch regular listings to fill remaining slots
        $excludeIds = $featured->pluck('id')->toArray();

        // Split remaining slots: 40% recent, 60% random
        $recentCount = (int) ceil($remainingSlots * 0.4);
        $randomCount = $remainingSlots - $recentCount;

        // Calculate offset for pagination
        $recentOffset = $currentPage == 1 ? 0 : (($currentPage - 1) * $perPage) - $featuredCount;

        // Get recent listings
        $recentListings = Advert::with('firstImage', 'owner')
            ->activeNotSold()
            ->whereNotIn('id', $excludeIds)
            ->where('featured', '!=', 'yes')
            ->orderByDesc('created_at')
            ->skip($recentOffset)
            ->limit($recentCount)
            ->get();

        // Update exclude IDs
        $excludeIds = array_merge($excludeIds, $recentListings->pluck('id')->toArray());

        // Get random listings
        $randomListings = Advert::with('firstImage', 'owner')
            ->activeNotSold()
            ->whereNotIn('id', $excludeIds)
            ->where('featured', '!=', 'yes')
            ->inRandomOrder()
            ->limit($randomCount)
            ->get();

        // Interleave recent and random posts
        $mixed = collect();
        $maxCount = max($recentListings->count(), $randomListings->count());

        for ($i = 0; $i < $maxCount; $i++) {
            if ($recentListings->has($i)) {
                $mixed->push($recentListings->get($i));
            }
            if ($randomListings->has($i)) {
                $mixed->push($randomListings->get($i));
            }
        }

        // Combine featured with mixed listings
        $allListings = $currentPage == 1 ? $featured->concat($mixed) : $mixed;

        // Calculate total count for pagination
        $totalActiveListings = Advert::activeNotSold()
            ->where('featured', '!=', 'yes')
            ->count();

        $totalCount = $totalActiveListings + $featuredCount;

        // Create paginator
        $listings = new \Illuminate\Pagination\LengthAwarePaginator(
            $allListings,
            $totalCount,
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Gallery
        $gallery = Advert::with('firstImage', 'owner')
            ->activeNotSold()
            ->inRandomOrder()
            ->limit(10)
            ->get();

        // Category-specific listings
        $cars = Advert::with('firstImage', 'owner')
            ->activeNotSold()
            ->where('sub_category', 2)
            ->inRandomOrder()
            ->limit(10)
            ->get();

        $phones = Advert::with('firstImage', 'owner')
            ->activeNotSold()
            ->where('sub_category', 6)
            ->inRandomOrder()
            ->limit(10)
            ->get();

        $fashion = Advert::with('firstImage', 'owner')
            ->activeNotSold()
            ->where('category', 5)
            ->inRandomOrder()
            ->limit(10)
            ->get();

        // User and categories
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $categories = Category::with('subCategories')->get();
        //dd($categories);
        return view('frontend.index', compact('title','gallery','listings','featured','user','categories','cars','phones','fashion','isMobile'));
    }

    public function advert(Request $request, $location, $slug, $id)
    {
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();

        if ($id== 47428 || $id == 86031 || $id == 34955 || $id == 86795 || $id == 44956) {

            return redirect('/');
        }
        $ad = Advert::with(['images', 'owner'])
            ->where('title_slug', $slug)
            ->first();

        // 1. If ad does not exist → 404
        if (!$ad) {
            return redirect('/');
        }

        // 2. If ad exists and redirect is Yes → redirect
        if ($ad->redirect === 'Yes') {
            return redirect('/');
        }



         $sessionKey = 'back_url_for_ad_' . $id;

        if (!session()->has($sessionKey)) {
            $referer = request()->headers->get('referer');
            // Only store if referrer exists and is from your own domain
            if ($referer && str_starts_with($referer, url('/'))) {
                // Don't store the current page as referrer (prevents loops)
                if ($referer !== request()->url()) {
                    session([$sessionKey => $referer]);
                }
            }
        }

        $data['ad'] = $ad;
        $ad_id = $ad->id;

        $data['title'] = $ad->ad_title.' - '.config('global.site_name');
        $title = $ad->ad_title;
        $cat_id = $ad->category;
        $brand_id = $ad->brand;
        $ad_owner = $ad->user_id;
        $subcat_id = $ad->sub_category;
        $user_id = $request->session()->get('user_id');

        // Check if images exist before processing
        if ($ad->images) {
            foreach ($ad->images as $img) {
                $path = public_path('uploads/images/' . $img->image);
                if (File::exists($path)) {
                    [$width, $height] = getimagesize($path);
                    $img->is_portrait = $height > $width;
                } else {
                    $img->is_portrait = false; // default to landscape
                }
            }
        }

        $data['user'] = User::where('user_id', $user_id)->first();
        $data['ad_owner'] = User::where('user_id', $ad_owner)->first();
        $data['cat'] = Category::where('id', $cat_id)->first();
        $data['sub_cat'] = SubCategory::where('id', $subcat_id)->first();
        $data['brand'] = Brands::where('id', $brand_id)->first();

        $data['car'] = CarDetail::where('advert_id', $ad_id)->first();
        if ($data['car']) {
            $model_id = $data['car']->model;
            $data['model'] = Models::where('id', $model_id)->first();
        }

        $data['phone'] = PhoneDetail::where('advert_id', $ad_id)->first();
        if ($data['phone']) {
            $model_id = $data['phone']->model;
            $data['model'] = Models::where('id', $model_id)->first();
        }

        $data['count_ads'] = Advert::where('user_id', $ad_owner)->activeNotRecentlySold()->count();
        $agent = new Agent();
        $isMobile = $agent->isMobile();

        $data['isMobile'] = $isMobile;

        /*
        |--------------------------------------------------------------------------
        | Related Adverts
        |--------------------------------------------------------------------------
        */
        $query = Advert::with('images')
            ->inRandomOrder()
            ->where('user_id', $ad_owner)
            ->activeNotRecentlySold()
            ->where('id', '!=', $ad_id);

        // 1. Set the maximum potential limit
        $limit = $isMobile ? 4 : 5;

        // 2. Fetch the adverts
        $adverts = $query->limit($limit)->get();

        // 3. Ensure the actual count is even for mobile
        // This handles the case where the DB returns 3 items even though the limit was 4.
        if ($isMobile && $adverts->count() % 2 !== 0) {
            $adverts = $adverts->slice(0, -1); // Remove the last item to make it even
        }

        $data['isMobile']     = $isMobile;
        $data['adverts']      = $adverts;
        $data['advertsCount'] = $adverts->count();

        /*
        |--------------------------------------------------------------------------
        | Similar Adverts
        |--------------------------------------------------------------------------
        */
        $data['similar_ads'] = Advert::with('images')
            ->inRandomOrder()
            ->where(function ($q) use ($title, $cat_id) {
                $q->where('ad_title', 'LIKE', '%' . $title . '%')
                  ->orWhere('category', $cat_id);
            })
            ->where('id', '!=', $ad_id)
            ->activeNotRecentlySold()
            ->where('user_id', '!=', $ad_owner)
            ->limit($isMobile ? 20 : 25)
            ->get();


        \DB::table('adverts')
            ->where('id', $ad_id)
            ->increment('views', 1);

        if ($user) {
            $notification = Notification::where('user_id', $user->id)
                ->where('advert_id', $ad_id)
                ->first();

            if ($notification) {
                $notification->update(['is_read' => true]);
            }
        }


        return view('frontend.advert', $data);
    }

    public function loadMoreAds(Request $request)
{


    $perPage = 20;
    $currentPage = $request->get('page', 1);

    // If it's page 1, get featured posts
    if ($currentPage == 1) {
        $featured = Advert::with('firstImage', 'owner')
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
            ->inRandomOrder()
            ->limit(10)
            ->get();

        $excludeIds = $featured->pluck('id')->toArray();
    } else {
        $featured = collect();
        $excludeIds = [];
    }

    // Split: 30% recent, 70% random older posts
    $recentCount = (int) ($perPage * 0.3);
    $randomCount = $perPage - $recentCount;

    // Get recent listings (excluding featured)
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

    // Get random older listings
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

    // Interleave recent and random posts
    $mixed = collect();
    $maxCount = max($recentListings->count(), $randomListings->count());

    for ($i = 0; $i < $maxCount; $i++) {
        if ($recentListings->has($i)) {
            $mixed->push($recentListings->get($i));
        }
        if ($randomListings->has($i)) {
            $mixed->push($randomListings->get($i));
        }
    }

    // On first page, prepend featured posts
    if ($currentPage == 1) {
        $ads = $featured->concat($mixed);
    } else {
        $ads = $mixed;
    }

    // Generate HTML
    $html = '';
    foreach ($ads as $row) {
        $html .= view('frontend.components.advert.advert-card', compact('row'))->render();
    }

    // Check if there are more pages
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

    $hasMorePages = ($currentPage * $perPage) < $totalCount;


    // ✅ FIX: Use snake_case to match JavaScript
    return response()->json([
        'html' => $html,
        'next_page' => $hasMorePages ? $currentPage + 1 : null,  // ← Changed from 'nextPage'
    ]);
}


    public function loadMoreAdsMobile(Request $request)
    {

        $perPage = 20;
        $currentPage = $request->get('page', 1);

        // If it's page 1, get featured posts
        if ($currentPage == 1) {
            $featured = Advert::with('firstImage', 'owner')
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
                ->inRandomOrder()
                ->limit(10)
                ->get();

            $excludeIds = $featured->pluck('id')->toArray();
        } else {
            $featured = collect();
            $excludeIds = [];
        }

        // Split: 50% recent, 50% random older posts
        $recentCount = (int) ($perPage * 0.3); // 10 posts
        $randomCount = $perPage - $recentCount; // 10 posts

        // Get recent listings (excluding featured)
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

        // Get random older listings
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

        // Interleave recent and random posts
        $mixed = collect();
        $maxCount = max($recentListings->count(), $randomListings->count());

        for ($i = 0; $i < $maxCount; $i++) {
            if ($recentListings->has($i)) {
                $mixed->push($recentListings->get($i));
            }
            if ($randomListings->has($i)) {
                $mixed->push($randomListings->get($i));
            }
        }

        // On first page, prepend featured posts
        if ($currentPage == 1) {
            $ads = $featured->concat($mixed);
        } else {
            $ads = $mixed;
        }

        // Generate HTML
        $html = '';
        foreach ($ads as $row) {
            $html .= view('frontend.components.advert.advert-card-mobile', compact('row'))->render();
        }

        // Check if there are more pages
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

        $hasMorePages = ($currentPage * $perPage) < $totalCount;

        return response()->json([
            'html' => $html,
            'next_page' => $hasMorePages ? $currentPage + 1 : null,
        ]);
    }


    // REMOVED: chat() method - duplicate functionality, replaced by MessageController::showMessages()

    public function adverts(Request $request)
    {
        $title = config('global.site_name').' | '.config('global.site_title');
        $page = request()->get('page', 1);
        $perPage = 20;
        $featuredLimit = 6;

        // Step 1: Fetch top 6 featured boosted ads (not paginated)
        $featured = Advert::with(['firstImage', 'boost' => fn($q) => $q->where('boost_status', 'active')])
            ->featuredBoosted()
            ->inRandomOrder()
            ->limit($featuredLimit)
            ->get();

        // Step 2: Calculate how many regular ads are needed for this page
        $offset = max(0, ($page - 1) * $perPage - $featuredLimit);
        $regularLimit = $perPage - ($page == 1 ? $featured->count() : 0);

        // Step 3: Fetch regular ads
        $regular = Advert::with('firstImage')
            ->regularAds()
            ->orderBy('created_at', 'desc')
            ->skip($offset)
            ->take($regularLimit)
            ->get();

        // Step 4: Merge for page 1, use regular only for later pages
        $ads = $page == 1
            ? $featured->merge($regular)
            : $regular;

        // Step 5: Total count for pagination (add featured only to first page count)
        $total = Advert::regularAds()->count() + ($page == 1 ? $featured->count() : 0);

        // Step 6: Custom paginator
        $paginated = new LengthAwarePaginator(
            $ads,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $categories = Category::with('subCategories')->get();
        $agent = new Agent();
        $isMobile = $agent->isMobile();

        //dd($categories);
        return view('frontend.adverts', compact('title', 'ads', 'user', 'categories','mobile'));
    }

   public function seller(Request $request, $name, $id)
    {
        // Check if owner exists
        $owner = User::where('user_id', $id)->first();
        if (!$owner) {
            return redirect('/');
        }


        // Fetch seller's other ads
        $ads = Advert::with('firstImage', 'owner')
            ->where('user_id', $id)
            ->activeNotRecentlySold()
            ->orderBy('created_at', 'desc')
            ->paginate(20);


        $hasMore = $ads->hasMorePages(); // Add this line

        $title = config('global.site_name') . ' | ' . config('global.site_title');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $categories = Category::with('subCategories')->get();
        $count_ads = Advert::where('user_id', $id)->activeNotRecentlySold()->count();

        return view('frontend.seller-adverts', compact('title', 'ads', 'user', 'owner', 'categories', 'count_ads', 'hasMore')); // Add hasMore to compact
    }

    public function loadMoreSellerAds(Request $request, $name, $id)
    {
        $owner = User::where('user_id', $id)->first();
        if (!$owner) {
            return response()->json(['error' => 'Seller not found'], 404);
        }

        $ads = Advert::with('firstImage', 'owner')
            ->where('user_id', $id)
            ->activeNotRecentlySold()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'html' => view('frontend.components.advert.advert-list', ['ads' => $ads])->render(),
            'hasMore' => $ads->hasMorePages()
        ]);
    }
   

    public function all_categories(Request $request)
    {
        $title = config('global.site_name').' | '.config('global.site_title');

        // Featured ads with scope
        $ads = Advert::with('firstImage')
                    ->activeNotRecentlySold()
                    ->orderBy('created_at', 'asc')
                    ->limit(10)
                    ->get();

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $categories = Category::with('subCategories')->get();

        // Category counts with filtering
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

        return view('frontend.all-categories', compact('title','ads','user','categories','categoryCounts'));
    }

    public function loadMoreAdverts(Request $request)
    {
        $page = $request->get('page', 1);
        $filters = $request->only(['category', 'sub_category', 'brand', 'model', 'state', 'state_slug', 'city']);

        $result = (new FeaturedAdPaginator($page))
            ->filters($filters)
            ->get();

        $agent = new Agent();
        $html = '';

        if ($agent->isMobile()) {
            foreach ($result['ads'] as $row) {
                $html .= view('frontend.components.advert.advert-card-mobile', compact('row'))->render();
            }
        } else {
            foreach ($result['ads'] as $row) {
                $html .= view('frontend.components.advert.advert-card', compact('row'))->render();
            }
        }

        return response()->json([
            'html' => $html,
            'hasMore' => $result['hasMore'],
            'nextPage' => $result['nextPage']
        ]);
    }


        public function category(Request $request, $category_slug)
    {
        $cat = Category::where('category_slug', $category_slug)->firstOrFail();
        $title = config('global.site_name') . ' | ' . $cat->category;

        $result = (new FeaturedAdPaginator(1))
            ->filters(['category' => $cat->id])
            ->get();

        $ads = $result['ads'];
        $hasMore = $result['hasMore'];

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();

        $count_cat = Advert::activeNotRecentlySold()
            ->where('category', $cat->id)
            ->count();

        $categories = DB::table('sub_categories')
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

            $filterType = 'category';
            $filterId = $cat->id;
            $filterIsString = false;

            $agent = new Agent();
            $isMobile = $agent->isMobile();

        return view('frontend.category', compact('title', 'ads', 'user', 'categories', 'cat', 'count_cat', 'hasMore','isMobile'));
    }

    public function sub_category(Request $request, $category_slug, $subcat_slug)
    {
        $cat = Category::where('category_slug', $category_slug)->firstOrFail();
        $subcat = SubCategory::where('sub_cat_slug', $subcat_slug)->where('cat_id', $cat->id)->firstOrFail();

        $title = config('global.site_name') . ' | ' . $subcat->sub_category;

        $result = (new FeaturedAdPaginator(1))
            ->filters(['sub_category' => $subcat->id])
            ->get();

        $ads = $result['ads'];
        $hasMore = $result['hasMore'];

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();

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

        // IMPORTANT: Set these variables for the JavaScript
        $filterType = 'sub_category';
        $filterId = $subcat->id;
        $filterIsString = false;

        $agent = new Agent();
        $isMobile = $agent->isMobile();
        return view('frontend.sub-category', compact('title', 'ads', 'user', 'brands', 'cat', 'subcat', 'count_subcat', 'hasMore', 'filterType', 'filterId','isMobile'));
    }

    public function brand(Request $request, $category_slug, $subcat_slug, $brand_slug)
    {
        $cat = Category::where('category_slug', $category_slug)->firstOrFail();
        $subcat = SubCategory::where('sub_cat_slug', $subcat_slug)->where('cat_id', $cat->id)->firstOrFail();
        $brand = Brands::where('brand_slug', $brand_slug)->where('subcat_id', $subcat->id)->firstOrFail();

        $title = config('global.site_name') . ' | ' . $brand->brand;

        $result = (new FeaturedAdPaginator(1))
            ->filters(['brand' => $brand->id])
            ->get();

        $ads = $result['ads'];
        $hasMore = $result['hasMore'];

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();

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

            $filterType = 'brand';
            $filterId = $brand->id;
            $filterIsString = false;
            $agent = new Agent();
            $isMobile = $agent->isMobile();

        return view('frontend.brand', compact('title', 'ads', 'user', 'brand', 'brands', 'cat', 'subcat', 'count_subcat', 'hasMore','filterType', 'filterId','isMobile'));
    }

        public function location($location)
    {
        $title = "Adverts located at ". $location .' | '.config('global.site_title');

        $result = (new FeaturedAdPaginator(1))
            ->filters(['state_slug' => $location])
            ->get();

        $ads = $result['ads'];
        $hasMore = $result['hasMore'];


        $filterType = 'state_slug';
        $filterId = $location; // Just the raw value
        $filterIsString = true; // Flag to tell JS it's a string

        $categories = Category::with('subCategories')->get();
        $agent = new Agent();
        $isMobile = $agent->isMobile();

        return view('frontend.location', compact('title','location', 'ads','categories', 'hasMore', 'filterType', 'filterId', 'filterIsString','isMobile'));
    }

    public function loadMoreLocation(Request $request)
    {
        $page = $request->get('page', 1);
        $filters = $request->only(['category', 'sub_category', 'brand', 'model', 'state', 'state_slug', 'city']);

        $result = (new FeaturedAdPaginator($page))
            ->filters($filters)
            ->get();

        $agent = new Agent();
        $html = '';

        if ($agent->isMobile()) {
            foreach ($result['ads'] as $row) {
                $html .= view('frontend.components.advert.advert-location-mobile', compact('row'))->render();
            }
        } else {
            foreach ($result['ads'] as $row) {
                $html .= view('frontend.components.advert.advert-location', compact('row'))->render();
            }
        }

        return response()->json([
            'html' => $html,
            'hasMore' => $result['hasMore'],
            'nextPage' => $result['nextPage']
        ]);
    }

        public function all_subcat(Request $request, $category_slug, $subcat_slug)
    {
        $cat = Category::where('category_slug', $category_slug)->firstOrFail();
        $subcat = SubCategory::where('sub_cat_slug', $subcat_slug)->where('cat_id', $cat->id)->firstOrFail();

        $title = config('global.site_name') . ' | ' . $subcat->sub_category . ' - All Brands';

        // CHANGED: Use .get() instead of .paginate()
        $result = (new FeaturedAdPaginator(1))
            ->filters(['sub_category' => $subcat->id])
            ->get();

        $ads = $result['ads'];
        $hasMore = $result['hasMore'];

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();

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

        // CHANGED: Added $hasMore to compact
        return view('frontend.all-subcat', compact('title','ads','user','cat','brands','subcat','count_subcat','subcat_slug', 'hasMore'));
    }



    public function all_cateory(Request $request, $category_slug)
    {
        $cat = Category::where('category_slug', $category_slug)->firstOrFail();

        $title = config('global.site_name') . ' | ' . $subcat->sub_category . ' - All Brands';

        // Main ads query with scope
        $ads = Advert::with('firstImage')
                    ->activeNotRecentlySold()
                    ->where('sub_category', $subcat->id)
                    ->orderBy('created_at', 'asc')
                    ->paginate(20);

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();

        // Count with scope
        $count_subcat = Advert::activeNotRecentlySold()
                            ->where('sub_category', $subcat->id)
                            ->count();

        // Brands query with filtering
        $brands = DB::table('brands')
            ->leftJoin('adverts', 'brands.id', '=', 'adverts.brand')
            ->where('brands.subcat_id', $subcat->id)
            // Add active and not recently sold conditions
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

        return view('frontend.all-subcat', compact('title','ads','user','brands','subcat','count_subcat','subcat_slug'));
    }

    public function mobile_category(Request $request, $id, $slug)
    {
        $title = config('global.site_name').' | '.config('global.site_title');
        $ads = Advert::with('firstImage')->where('category', $id)->orderBy('created_at', 'asc')->paginate(20);
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $cat = Category::where('id', $id)->first();
        $count_cat = Advert::where('category', $id)->count();
        $categories = DB::table('sub_categories')
            ->leftJoin('adverts', 'sub_categories.id', '=', 'adverts.sub_category')
            ->where('sub_categories.cat_id', $id)
            ->select('sub_categories.id', 'sub_categories.sub_category', 'sub_categories.sub_cat_slug', DB::raw('COUNT(adverts.id) as advert_count'))
            ->groupBy('sub_categories.id', 'sub_categories.sub_category', 'sub_categories.sub_cat_slug')
            ->orderBy('advert_count', 'desc') // Optional: Order by count if needed
            ->get();

        //dd($categories);
        return view('frontend.mobile-category', compact('title', 'ads', 'user', 'categories', 'cat', 'count_cat'));
    }


    public function buy_direct(Request $request, $id)
    {
        $title = "Buy Directly" .' | '.config('global.site_title');
        $data['ad'] = Advert::with('images','shippings')->where('ad_id', $id)->first();
        $data['title'] = $data['ad']->ad_title.' - '.config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $data['user'] = $user = User::where('user_id', $user_id)->first();
        $data['states'] = State::all();
        //dd($data['ad']);
        if(empty($user)) {
            $request->session()->forget('user_id');
            $currentURL = url()->current();
            $request->session()->put('previous_url', $currentURL);
            return redirect('/login')->with('error','Sorry, you need to login to Use Buy Direct');
        }

        return view('frontend.buy-direct', $data);
    }


    public function calculate_shipping(Request $request, $id)
    {
        try {
            $data['ad'] = $ad = Advert::with('images', 'shippings')->findOrFail($id);
            $user_id = $request->session()->get('user_id');
            $data['user'] = $user = User::findOrFail($user_id);

            $validated = $request->validate([
                'first_name' => 'required',
                'last_name' => 'required',
                'phone' => 'required',
                'city' => 'required',
                'state' => 'required',
                'shipping_selected' => 'required',
            ],
             [
                'shipping_selected.required' => 'Please Select a shipping option',
            ]);

            $sender_station = State::where('name', $ad->state)->firstOrFail();
            $data['reciever_state'] = $reciever_station = State::findOrFail($request->state);
            $data['reciever_city'] = GigLogistic::findOrFail($request->city);
            $data['shipping_method'] = $request->ship_id;
            $reciever_address = $data['reciever_city']['city'].",".$data['reciever_state']['name'];
            //dd($reciever_address);
            $details = [
                'advert_id' => $id,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'phone' => $validated['phone'],
                'reciever_station' => $reciever_station->station_id,
                'reciever_address' => $reciever_address,
                'sender_station' => $sender_station->station_id,
                'sender_address' => $ad->lga.','. $ad->state,
                'ad_title' => $ad->ad_title,
                'ad_price' => $ad->price,
                'ad_des' => $ad->description,
            ];

            $shippingResponse = app()->make(LocationController::class)->getAgilityShippingCost(new Request($details));
            $responseData = $shippingResponse->getData();
            //dd($shippingResponse);
            if (!$responseData->status) {
                return back()->with('error', $responseData->error ?? 'Failed to calculate shipping cost');
            }
            if($ad->price >= 300000){
                $commission = 0.02 * $ad->price;
            }else{
                $commission = 0.03 * $ad->price;
            }

            $shipping_cost = $responseData->data->GrandTotal ?? 0;
            $grand_total = $ad->price + $shipping_cost + $commission;

            // Store data in session
            $request->session()->put('shipping_data', [
                'ad' => $ad,
                'user' => $user,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'phone' => $validated['phone'],
                'shipping_cost' => $shipping_cost,
                'grand_total' => $grand_total,
                'commission' => $commission,
                'reciever_state' => $reciever_station,
                'reciever_city' => $data['reciever_city'],
                'shipping_method' => $data['shipping_method']
            ]);

            // Correct redirect syntax
            return redirect()->route('buy.direct.payment', ['id' => $ad->id]);

        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

public function buy_direct_payment(Request $request, $id)
    {
        $title = "Buy Directly" .' | '.config('global.site_title');
        $data['ad'] = Advert::with('images','shippings')->where('id', $id)->first();
        $data['title'] = $data['ad']->ad_title.' - '.config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $data['user'] = $user = User::where('user_id', $user_id)->first();

        //dd($data['ad']);

        $ship_data = $request->session()->get('shipping_data');
        $ship_id = $ship_data['shipping_method'];
        $data['shipping_method'] = Shipping::where('id',$ship_id)->first();
       // dd($data['shipping_method']);
        return view('frontend.buy-direct-payment', $data);
    }

    public function report_advert(Request $request, $id)
    {
        $data['title'] = 'Report Advert | '.config('global.site_name');
        $data['ad'] = $advert = Advert::with('images')->where('id', $id)->first();
        $user_id = $request->session()->get('user_id');
        $data['user'] = $user = User::where('user_id', $user_id)->first();


        if (!$advert) {

            abort(404, 'Advert not found');
        }

        if ($request->isMethod('GET')) {
            return view('frontend.report-ad', $data);
        }


        if ($request->isMethod('POST')) {
            // Build validation rules
            $rules = [
                'name' => 'required',
                'subject' => 'required',
                'message' => 'required',
            ];

            // Add reCAPTCHA validation only if enabled
            if (config('services.recaptcha.enabled', false)) {
                $rules['g-recaptcha-response'] = ['required', new ReCaptcha];
            }

            $request->validate($rules);

            $message = Reports::updateOrCreate(
                [
                    'advert_id' => $id,
                    'user_id' => $user_id,
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

            Mail::to(config('global.admin_email'))->queue(new ReportMail($details));

            return redirect()->back()->with('success', 'Your Report Has Been Received, We will Get back to Shortly');
        }
    }

    public function apply_job(Request $request, $id)
    {
        $data['title'] = 'Report Advert | '.config('global.site_name');
        $data['ad'] = Advert::with('images')->where('id', $id)->first();
        $user_id = $request->session()->get('user_id');
        $data['user'] = $user = User::where('user_id', $user_id)->first();
        $reciever = $data['ad']['owner']['user_id'];

        if ($request->isMethod('POST')) {
            $request->validate([
                'message' => 'required',
            ]);
            //dd($reciever);

            $message = Message::create([
                'advert_id' => $id,
                'sender_id' => $user_id,
                'receiver_id' => $reciever,
                'message_content' => $request->message,
                'is_read' => false,
            ]);

            return redirect()->back()->with('success', 'You Have successfuly Apllied for Job, The provider will Get back to Shortly');

        }
    }
}
