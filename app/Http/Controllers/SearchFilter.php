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

class SearchFilter extends Controller
{
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
            $query->where('ad_title', 'LIKE', '%' . $request->product . '%');
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
            $query->where('buy_direct', $request->brand);
        }

        // Order and paginate results
        $ads = $query->orderBy('created_at', 'asc')
                    ->paginate(10)
                    ->appends($request->except('page'));

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $categories = Category::with('subCategories')->get();

        return view('frontend.adverts', compact('title', 'ads', 'user', 'categories'));
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
                    ->orderBy('created_at', 'asc')
                    ->paginate(10);

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $categories = Category::with('subCategories')->get();
        $count_cat = Advert::activeNotRecentlySold()
                          ->where('category', $cat->id)
                          ->count();

        return view('frontend.location-category', compact('title', 'ads', 'user', 'cat', 'categories','count_cat'));
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
                    ->orderBy('created_at', 'asc')
                    ->paginate(10);

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $categories = Category::with('subCategories')->get();
        $count_subcat = Advert::activeNotRecentlySold()
                            ->where('sub_category', $subcat->id)
                            ->count();

        return view('frontend.location-subcat', compact('title', 'ads', 'user', 'categories','subcat','count_subcat'));
    }


    public function location_brand(Request $request, $location, $slug)
    {
        $title = config('global.site_name').' | '.config('global.site_title');
        $brand = Brands::where('brand_slug', $slug)->firstOrFail();

        $ads = Advert::with('firstImage')
                    ->activeNotRecentlySold()
                    ->where('state', $location)
                    ->where('brand', $brand->id)
                    ->orderBy('created_at', 'asc')
                    ->paginate(10);

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $categories = Category::with('subCategories')->get();
        $count_brand = Advert::activeNotRecentlySold()
                        ->where('brand', $brand->id)
                        ->count();

        return view('frontend.location-brand', compact('title', 'ads', 'user', 'categories', 'brand', 'count_brand'));
    }

    public function filter(Request $request)
    {
        $query = Advert::with('firstImage')
                    ->where('ad_status', 1)
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
        $min = $request->input('min');
        $max = $request->input('max');
        $range = $request->input('range');

        if ($min !== null) {
            $query->where('price', '>=', (int) $min);
        }

        if ($max !== null) {
            $query->where('price', '<=', (int) $max);
        }

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
        $adverts = $query->orderBy('created_at', 'desc')
                         ->paginate(10)
                         ->appends($request->except('page'));

        // Return only partial
        return response()->json([
            'html' => view('frontend.components.advert.advert-list', ['ads' => $adverts])->render()
        ]);
    }


    public function filterBySeller(Request $request)
    {
        $query = Advert::with('firstImage')
            ->where('ad_status', 1)
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

        // 🟢 Paginate with sellers filter appended
        $adverts = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends(['sellers' => $sellers]);

        return response()->json([
            'html' => view('frontend.components.advert.advert-list', ['ads' => $adverts])->render()
        ]);
    }


        public function filterByBuydirect(Request $request)
    {
        $query = Advert::with('firstImage')
            ->where('ad_status', 1)
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

        if ($request->filled('buydirect')) {
            $query->where('buy_direct', $request->buyDirect);
        }

        if ($request->filled('location')) {
            $query->where('state', $request->location);
        }

        // 🟢 Paginate with sellers filter appended
        $adverts = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends(['sellers' => $sellers]);

        return response()->json([
            'html' => view('frontend.components.advert.advert-list', ['ads' => $adverts])->render()
        ]);
    }


}
