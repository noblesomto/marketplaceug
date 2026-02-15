<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Advert;
use App\Models\AdvertImage;
use App\Models\SubCategory;
use App\Models\Category;
use App\Models\Brands;
use App\Models\User;
use App\Models\State;
use Jenssegers\Agent\Agent;
use App\Traits\HasUserSession;

class SearchFilter extends Controller
{
    use HasUserSession;

    public function search(Request $request)
    {
        $title = config('global.site_name').' | '.config('global.site_title');

        $request->validate([
            'product' => 'nullable|string|min:3',
            'location' => 'nullable',
            'category' => 'nullable',
        ]);

        // Start with active ads
        $query = Advert::with('firstImage')
                    ->activeNotRecentlySold();

        // Add conditions only if the parameter is provided
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
            $query->where('buy_direct', $request->buydirect); // Fixed: was using $request->brand
        }

        // Order and paginate results
        $ads = $query->orderWithFeatured()
             ->paginate(20)
             ->appends($request->except('page'));

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $categories = Category::with('subCategories')->get();

        // Add hasMore for load more functionality
        $hasMore = $ads->hasMorePages();

        // Get search parameters to pass to view
        $searchParams = [
            'product' => $request->input('product'),
            'location' => $request->input('location'),
            'category' => $request->input('category'),
            'sub_category' => $request->input('sub_category'),
            'brand' => $request->input('brand'),
            'buydirect' => $request->input('buydirect'),
        ];
        $agent = new Agent();
        $isMobile = $agent->isMobile();
        return view('frontend.adverts', compact('title', 'ads', 'user', 'categories', 'hasMore', 'searchParams','isMobile'));
    }

    public function location_router($location, $slug)
    {
        if (Category::where('category_slug', $slug)->exists()) {
            return $this->location_category(request(), $location, $slug);
        } elseif (SubCategory::where('sub_cat_slug', $slug)->exists()) {
            return $this->location_subcat(request(), $location, $slug);
        } elseif (Brands::where('brand_slug', $slug)->exists()) {
            return $this->location_brand(request(), $location, $slug);
        }

        abort(404);
    }


    public function location_category(Request $request, $location, $slug)
    {
        $title = config('global.site_name').' | '.config('global.site_title');
        $cat = Category::where('category_slug', $slug)->firstOrFail();

        $ads = Advert::with('firstImage')
                    ->activeNotRecentlySold()
                    ->where('state', $location)
                    ->where('category', $cat->id)
                    ->orderWithFeatured()
                    ->paginate(20);

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $categories = Category::with('subCategories')->get();
        $count_cat = Advert::activeNotRecentlySold()
                          ->where('category', $cat->id)
                          ->count();

        $hasMore = $ads->hasMorePages();
        $agent = new Agent();
        $isMobile = $agent->isMobile();

        // Use the merged category view with location context
        return view('frontend.category', compact('title', 'ads', 'user', 'cat', 'categories', 'count_cat', 'hasMore', 'isMobile', 'location'));
    }

    public function location_subcat(Request $request, $location, $slug)
    {
        $title = config('global.site_name').' | '.config('global.site_title');
        $subcat = SubCategory::where('sub_cat_slug', $slug)->firstOrFail();

        $ads = Advert::with('firstImage')
                    ->activeNotRecentlySold()
                    ->where('sold', 'No')
                    ->where('state', $location)
                    ->where('sub_category', $subcat->id)
                    ->orderWithFeatured()
                    ->paginate(20);

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $categories = Category::with('subCategories')->get();
        $count_subcat = Advert::activeNotRecentlySold()
                            ->where('sub_category', $subcat->id)
                            ->count();

        // Get brands for non-location version (will be hidden in location view)
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

        $cat = $subcat->category;

        $hasMore = $ads->hasMorePages();
        $agent = new Agent();
        $isMobile = $agent->isMobile();

        // Use the merged sub-category view with location context
        return view('frontend.sub-category', compact('title', 'ads', 'user', 'categories', 'subcat', 'count_subcat', 'hasMore', 'isMobile', 'location', 'brands', 'cat'));
    }

    public function location_brand(Request $request, $location, $slug)
    {
        $title = config('global.site_name').' | '.config('global.site_title');
        $brand = Brands::where('brand_slug', $slug)->firstOrFail();

        $ads = Advert::with('firstImage')
                    ->activeNotRecentlySold()
                    ->where('state', $location)
                    ->where('brand', $brand->id)
                    ->orderWithFeatured()
                    ->paginate(20);

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $categories = Category::with('subCategories')->get();
        $count_brand = Advert::activeNotRecentlySold()
                        ->where('brand', $brand->id)
                        ->count();

        // Get related data for the brand
        $subcat = $brand->subcategory;
        $cat = $subcat ? $subcat->category : null;

        // Get other brands in same subcategory (for non-location version)
        $brands = DB::table('brands')
            ->leftJoin('adverts', 'brands.id', '=', 'adverts.brand')
            ->where('brands.subcat_id', $brand->subcat_id)
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

        $count_subcat = Advert::activeNotRecentlySold()
                        ->where('sub_category', $brand->subcat_id)
                        ->count();

        $hasMore = $ads->hasMorePages();
        $agent = new Agent();
        $isMobile = $agent->isMobile();

        // Use the merged brand view with location context
        return view('frontend.brand', compact('title', 'ads', 'user', 'categories', 'brand', 'count_brand', 'hasMore', 'isMobile', 'location', 'brands', 'cat', 'subcat', 'count_subcat'));
    }

    public function filter(Request $request)
    {
        $query = Advert::with('firstImage')
                    ->where('ad_status', 'active')
                    ->where('sold', 'No');

        //dd($request->location);
        // 🔁 Context Filters
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

        // 💰 Price Filters
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

        // Paginate
        $adverts = $query->orderWithFeatured()
                 ->paginate(20)
                 ->appends($request->except('page'));

        // Render based on device type
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

        // Return only partial
        return response()->json([
            'html' => $html,
            'hasMore' => $adverts->hasMorePages()
        ]);
    }


    public function filterBySeller(Request $request)
    {
        $query = Advert::with('firstImage')
            ->where('ad_status', 'active')
            ->where('sold', 'No');

        // 🟢 Seller filter
        $sellers = $request->input('sellers', 'all');
        if ($sellers !== 'all') {
            $query->whereHas('owner', function ($q) use ($sellers) {
                $q->where('verified', $sellers); // yes/no
            });
        }

        // Optional: category context
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

        //Paginate with sellers filter appended
        $adverts = $query->orderWithFeatured()
                 ->paginate(20)
                 ->appends(['sellers' => $sellers]);

        // Render based on device type
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


    public function filterByBuydirect(Request $request)
    {
        $query = Advert::with('firstImage')
            ->where('ad_status', 'active')
            ->where('sold', 'No')
            ->where('buy_direct', $request->buy_direct);

        //dd($request->buy_direct);

        // 🟢 Buy Direct filter - AND condition
        if ($request->filled('buydirect')) {
            $query->where('buy_direct', $request->buydirect);
        }

        // Optional: category context
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

        // 🟢 Paginate with sellers and buydirect filters appended
        $adverts = $query->orderWithFeatured()
                 ->paginate(20)
                 ->appends([
                     'buydirect' => $request->input('buydirect')
                 ]);

        // Render based on device type
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

    public function loadMore(Request $request)
    {
        $query = Advert::with('firstImage')
                    ->activeNotRecentlySold();

        // Product search
        if ($request->filled('product')) {
            $query->where(function ($q) use ($request) {
                $q->where('ad_title', 'LIKE', '%' . $request->product . '%')
                  ->orWhere('ad_id', 'LIKE', '%' . $request->product . '%');
            });
        }

        // Category context
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Subcategory context
        if ($request->filled('sub_category')) {
            $query->where('sub_category', $request->sub_category);
        }

        // Brand context
        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        // Location context
        if ($request->filled('location')) {
            $query->where('state', $request->location);
        }

        // Buy direct filter
        if ($request->filled('buydirect')) {
            $query->where('buy_direct', $request->buydirect);
        }

        // Get paginated results
        $ads = $query->orderWithFeatured()
                     ->paginate(20);

        // Render based on device type
        $agent = new Agent();
        $html = '';

        if ($agent->isMobile()) {
            foreach ($ads as $row) {
                $html .= view('frontend.components.advert.advert-card-mobile', compact('row'))->render();
            }
        } else {
            foreach ($ads as $row) {
                $html .= view('frontend.components.advert.advert-card', compact('row'))->render();
            }
        }

        return response()->json([
            'html' => $html,
            'hasMore' => $ads->hasMorePages()
        ]);
    }

    public function filterByCarDetails(Request $request)
    {
        $query = Advert::with('firstImage')
            ->join('car_details', 'adverts.id', '=', 'car_details.advert_id')
            ->where('adverts.ad_status', 'active')
            ->where('adverts.sold', 'No')
            ->select('adverts.*');

        // Apply car-specific filters
        if ($request->filled('condition') && !empty($request->condition)) {
            $query->whereIn('car_details.condition', $request->condition);
        }

        if ($request->filled('registration') && !empty($request->registration)) {
            $query->whereIn('car_details.registration', $request->registration);
        }

        if ($request->filled('fuel_type') && !empty($request->fuel_type)) {
            $query->whereIn('car_details.fuel', $request->fuel_type);
        }

        if ($request->filled('transmission') && !empty($request->transmission)) {
            $query->whereIn('car_details.transmission', $request->transmission);
        }

        // Apply context filters
        if ($request->filled('category')) {
            $query->where('adverts.category', $request->category);
        }

        if ($request->filled('sub_category')) {
            $query->where('adverts.sub_category', $request->sub_category);
        }

        if ($request->filled('brand')) {
            $query->where('adverts.brand', $request->brand);
        }

        if ($request->filled('location')) {
            $query->where('adverts.state', $request->location);
        }

        // Apply price filters
        if ($request->filled('min')) {
            $query->where('adverts.price', '>=', (int) $request->min);
        }

        if ($request->filled('max')) {
            $query->where('adverts.price', '<=', (int) $request->max);
        }

        // Apply buy direct filter
        if ($request->filled('buydirect')) {
            $query->where('adverts.buy_direct', $request->buydirect);
        }

        // Apply verified seller filter
        $sellers = $request->input('sellers', 'all');
        if ($sellers !== 'all') {
            $query->whereHas('owner', function ($q) use ($sellers) {
                $q->where('verified', $sellers);
            });
        }

        // Paginate results
        $adverts = $query->orderBy('adverts.featured', 'DESC')
                         ->orderBy('adverts.created_at', 'DESC')
                         ->paginate(20);

        // Render based on device type
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

    public function filterByPhoneDetails(Request $request)
    {
        $query = Advert::with('firstImage')
            ->join('phone_details', 'adverts.id', '=', 'phone_details.advert_id')
            ->where('adverts.ad_status', 'active')
            ->where('adverts.sold', 'No')
            ->select('adverts.*');

        // Apply phone-specific filters
        if ($request->filled('condition') && !empty($request->condition)) {
            $query->whereIn('phone_details.condition', $request->condition);
        }

        if ($request->filled('device_type') && !empty($request->device_type)) {
            $query->whereIn('phone_details.device', $request->device_type);
        }

        // Apply context filters
        if ($request->filled('category')) {
            $query->where('adverts.category', $request->category);
        }

        if ($request->filled('sub_category')) {
            $query->where('adverts.sub_category', $request->sub_category);
        }

        if ($request->filled('brand')) {
            $query->where('adverts.brand', $request->brand);
        }

        if ($request->filled('location')) {
            $query->where('adverts.state', $request->location);
        }

        // Apply price filters
        if ($request->filled('min')) {
            $query->where('adverts.price', '>=', (int) $request->min);
        }

        if ($request->filled('max')) {
            $query->where('adverts.price', '<=', (int) $request->max);
        }

        // Apply buy direct filter
        if ($request->filled('buydirect')) {
            $query->where('adverts.buy_direct', $request->buydirect);
        }

        // Apply verified seller filter
        $sellers = $request->input('sellers', 'all');
        if ($sellers !== 'all') {
            $query->whereHas('owner', function ($q) use ($sellers) {
                $q->where('verified', $sellers);
            });
        }

        // Paginate results
        $adverts = $query->orderBy('adverts.featured', 'DESC')
                         ->orderBy('adverts.created_at', 'DESC')
                         ->paginate(20);

        // Render based on device type
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
}
