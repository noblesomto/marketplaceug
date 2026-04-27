<?php

namespace App\Http\Controllers\Shop;
use App\Http\Controllers\Controller;

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
use App\Traits\GeneratesSeoMeta;
use App\Services\FilterService;
use App\Services\AdvertQueryService;

class SearchFilter extends Controller
{
    use HasUserSession, GeneratesSeoMeta;

    public function __construct(
        private FilterService $filterService,
        private AdvertQueryService $advertQueryService,
    ) {}

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
                  ->orWhere('title_slug', 'LIKE', '%' . $request->product . '%')
                  ->orWhere('ad_id', $request->product);
            });
        }

        $this->filterService->applyContextFilters($query, $request);

        if ($request->filled('buydirect')) {
            $query->where('buy_direct', $request->buydirect);
        }

        // Order and paginate results
        $ads = $query->orderWithFeatured()
             ->paginate(20)
             ->appends($request->except('page'));

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $categories = Category::with('subCategories')->get();

        $hasMore = $ads->hasMorePages();

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
        return view('public.adverts', compact('title', 'ads', 'user', 'categories', 'hasMore', 'searchParams','isMobile'));
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
        $cat = Category::where('category_slug', $slug)->firstOrFail();
        $seo   = $this->buildSeoMeta($cat->category, $cat->seo_group ?? 'product', url("/{$location}/{$slug}"), $location);
        $title = $seo['seoTitle'];

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

        return view('public.category', array_merge(compact('title', 'ads', 'user', 'cat', 'categories', 'count_cat', 'hasMore', 'isMobile', 'location'), $seo));
    }

    public function location_subcat(Request $request, $location, $slug)
    {
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

        $brands = $this->advertQueryService->getBrandsForSubcat($subcat->id);

        $cat   = $subcat->category;
        $seo   = $this->buildSeoMeta($subcat->sub_category, $cat->seo_group ?? 'product', url("/{$location}/{$slug}"), $location);
        $title = $seo['seoTitle'];

        $hasMore = $ads->hasMorePages();
        $agent = new Agent();
        $isMobile = $agent->isMobile();

        return view('public.sub-category', array_merge(compact('title', 'ads', 'user', 'categories', 'subcat', 'count_subcat', 'hasMore', 'isMobile', 'location', 'brands', 'cat'), $seo));
    }

    public function location_brand(Request $request, $location, $slug)
    {
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

        $subcat = $brand->subcategory;
        $cat = $subcat ? $subcat->category : null;

        $brands = $this->advertQueryService->getBrandsForSubcat($brand->subcat_id);

        $count_subcat = Advert::activeNotRecentlySold()
                        ->where('sub_category', $brand->subcat_id)
                        ->count();

        $seoName = $brand->brand . ($subcat ? ' ' . $subcat->sub_category : '');
        $seoGrp  = $cat->seo_group ?? 'product';
        $seo     = $this->buildSeoMeta($seoName, $seoGrp, url("/{$location}/{$slug}"), $location);
        $title   = $seo['seoTitle'];

        $hasMore = $ads->hasMorePages();
        $agent = new Agent();
        $isMobile = $agent->isMobile();

        return view('public.brand', array_merge(compact('title', 'ads', 'user', 'categories', 'brand', 'count_brand', 'hasMore', 'isMobile', 'location', 'brands', 'cat', 'subcat', 'count_subcat'), $seo));
    }

    public function filter(Request $request)
    {
        $query = Advert::with('firstImage')
                    ->where('ad_status', 'active')
                    ->where('sold', 'No');

        $this->filterService->applyContextFilters($query, $request);
        $this->filterService->applyPriceFilters($query, $request);

        $adverts = $query->orderWithFeatured()
                 ->paginate(20)
                 ->appends($request->except('page'));

        return response()->json([
            'html'    => $this->filterService->renderAdvertCards($adverts),
            'hasMore' => $adverts->hasMorePages(),
        ]);
    }

    public function filterBySeller(Request $request)
    {
        $query = Advert::with('firstImage')
            ->where('ad_status', 'active')
            ->where('sold', 'No');

        // Seller filter
        $sellers = $request->input('sellers', 'all');
        if ($sellers !== 'all') {
            $query->whereHas('owner', function ($q) use ($sellers) {
                $q->where('verified', $sellers);
            });
        }

        $this->filterService->applyContextFilters($query, $request);

        $adverts = $query->orderWithFeatured()
                 ->paginate(20)
                 ->appends(['sellers' => $sellers]);

        return response()->json([
            'html'    => $this->filterService->renderAdvertCards($adverts),
            'hasMore' => $adverts->hasMorePages(),
        ]);
    }

    public function filterByBuydirect(Request $request)
    {
        $query = Advert::with('firstImage')
            ->where('ad_status', 'active')
            ->where('sold', 'No');

        if ($request->filled('buydirect')) {
            $query->where('buy_direct', $request->buydirect);
        }

        $this->filterService->applyContextFilters($query, $request);

        $adverts = $query->orderWithFeatured()
                 ->paginate(20)
                 ->appends(['buydirect' => $request->input('buydirect')]);

        return response()->json([
            'html'    => $this->filterService->renderAdvertCards($adverts),
            'hasMore' => $adverts->hasMorePages(),
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
                  ->orWhere('title_slug', 'LIKE', '%' . $request->product . '%')
                  ->orWhere('ad_id', $request->product);
            });
        }

        $this->filterService->applyContextFilters($query, $request);

        if ($request->filled('buydirect')) {
            $query->where('buy_direct', $request->buydirect);
        }

        $ads = $query->orderWithFeatured()->paginate(20);

        return response()->json([
            'html'    => $this->filterService->renderAdvertCards($ads),
            'hasMore' => $ads->hasMorePages(),
        ]);
    }

    public function filterByCarDetails(Request $request)
    {
        $adverts = $this->filterService->buildCarDetailQuery($request)->paginate(20);

        return response()->json([
            'html'    => $this->filterService->renderAdvertCards($adverts),
            'hasMore' => $adverts->hasMorePages(),
        ]);
    }

    public function filterByPhoneDetails(Request $request)
    {
        $adverts = $this->filterService->buildPhoneDetailQuery($request)->paginate(20);

        return response()->json([
            'html'    => $this->filterService->renderAdvertCards($adverts),
            'hasMore' => $adverts->hasMorePages(),
        ]);
    }
}
