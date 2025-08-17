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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Rules\ReCaptcha;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\FeaturedAdPaginator;
use App\Mail\ReportMail;
use Mail;


class AdvertController extends Controller
{
    public function advert(Request $request, $location, $slug, $id)
    {
        // First get the ad and check if it exists
        $ad = Advert::with('images','owner')->where('title_slug', $slug)->first();

        if (!$ad) {

            abort(404, 'Advert not found');
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

        $data['count_ads'] = Advert::where('user_id', $ad_owner)->count();

        //Related Adverts
        $query = Advert::with('images')
            ->inRandomOrder()
            ->where('user_id', $ad_owner)
            ->activeNotRecentlySold()
            ->where('id', '!=', $ad_id);

        $data['advertsCount'] = $query->count();
        $data['adverts'] = $query->limit(6)->get();

        //Similar Adverts
        $data['similar_ads'] = Advert::with('images')
            ->inRandomOrder()
            ->where(function($q) use ($title, $cat_id) {
                $q->where('ad_title', 'LIKE', '%' . $title . '%')
                  ->orWhere('category', $cat_id);
            })
            ->where('id', '!=', $ad_id)
            ->activeNotRecentlySold()
            ->where('user_id', '!=', $ad_owner)
            ->limit(3)
            ->get();

        \DB::table('adverts')
            ->where('id', $ad_id)
            ->increment('views', 1);

        return view('frontend.advert', $data);
    }

    public function loadMoreAds(Request $request)
    {
        $perPage = 20;

        $ads = Advert::with('firstImage', 'owner')
            ->where('ad_status', 1)
            ->where(function ($query) {
                $query->where('sold', '!=', 'Yes')
                      ->orWhere(function ($q) {
                          $q->where('sold', 'Yes')
                            ->whereNotNull('sold_date')
                            ->where('sold_date', '>=', now()->subDays(7));
                      });
            })
            ->selectRaw('adverts.*, (featured = "yes") as is_featured')
            ->orderByDesc('is_featured')
            ->orderByRaw('CASE WHEN featured = "yes" THEN RAND() END')
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $request->get('page', 1));

        $html = '';
        foreach ($ads as $row) {
            $html .= view('frontend.components.advert.advert-card', compact('row'))->render();
        }

        return response()->json([
            'html' => $html,
            'next_page' => $ads->hasMorePages() ? $ads->currentPage() + 1 : null,
        ]);
    }

    public function loadMoreAdsMobile(Request $request)
    {
        $perPage = 20;

        $ads = Advert::with('firstImage', 'owner')
            ->where('ad_status', 1)
            ->where(function ($query) {
                $query->where('sold', '!=', 'Yes')
                      ->orWhere(function ($q) {
                          $q->where('sold', 'Yes')
                            ->whereNotNull('sold_date')
                            ->where('sold_date', '>=', now()->subDays(7));
                      });
            })
            ->selectRaw('adverts.*, (featured = "yes") as is_featured')
            ->orderByDesc('is_featured')
            ->orderByRaw('CASE WHEN featured = "yes" THEN RAND() END')
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $request->get('page', 1));

        $html = '';
        foreach ($ads as $row) {
            $html .= view('frontend.components.advert.advert-card-mobile', compact('row'))->render();
        }

        return response()->json([
            'html' => $html,
            'next_page' => $ads->hasMorePages() ? $ads->currentPage() + 1 : null,
        ]);
    }


    public function chat(Request $request, $user_id, $id)
    {
        $data['ad'] = Advert::where('ad_id', $id)->first();
        $data['title'] = $data['ad']->ad_title.' - '.config('global.site_name');
        $data['images'] = AdvertImage::where('ad_id', $id)->get();
        $cat_id = $data['ad']->category;
        $brand_id = $data['ad']->brand;
        $ad_owner = $data['ad']->user_id;
        $data['ad_owner'] = $user_id;
        $subcat_id = $data['ad']->sub_category;
        $user_id = $request->session()->get('user_id');
        $data['user'] = User::where('user_id', $user_id)->first();
        $data['cat'] = Category::where('cat_id', $cat_id)->first();
        $data['brand'] = Brands::where('cat_id', $cat_id)->first();
        $data['model'] = Models::where('cat_id', $cat_id)->first();
        $data['car'] = CarDetail::where('brand_id', $brand_id)->first();
        $data['phone'] = PhoneDetail::where('brand_id', $brand_id)->first();
        $data['count_ads'] = Advert::where('user_id', $ad_owner)->count();

        //dd($data['ad']->sub_category);
        $currentURL = url()->current();
        $request->session()->put('previous_url', $currentURL);
        $user_id = $request->session()->get('user_id');
        if (empty($user_id)) {
            return redirect('/login')->with('error', 'Sorry, you need to login to chat with Ad Owner');
        }

        return view('frontend.chat', $data);
    }

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

        //dd($categories);
        return view('frontend.adverts', compact('title', 'ads', 'user', 'categories'));
    }

    public function seller(Request $request, $id)
    {
        $title = config('global.site_name').' | '.config('global.site_title');
        $ads = Advert::with('firstImage','owner')->where('user_id', $id)->activeNotRecentlySold()->orderBy('created_at', 'desc')->paginate(10);
        $user_id = $request->session()->get('user_id');
        $owner = User::where('user_id', $id)->first();
        $user = User::where('user_id', $user_id)->first();
        $categories = Category::with('subCategories')->get();
        $count_ads = Advert::where('user_id', $id)->count();

        return view('frontend.seller-adverts', compact('title', 'ads', 'user', 'owner', 'categories','count_ads'));
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
                         $q->where('adverts.sold_date', '>=', now()->subDays(7))
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

    public function category(Request $request, $category_slug)
    {
        $cat = Category::where('category_slug', $category_slug)->firstOrFail();
        $title = config('global.site_name') . ' | ' . $cat->category;

        $ads = (new FeaturedAdPaginator(request()->get('page', 1)))
            ->filters(['category' => $cat->id])
            ->paginate();

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();

        // Update count to also use the scope
        $count_cat = Advert::activeNotRecentlySold()
                          ->where('category', $cat->id)
                          ->count();

        $categories = DB::table('sub_categories')
            ->leftJoin('adverts', function ($join) {
                $join->on('sub_categories.id', '=', 'adverts.sub_category')
                    ->where('adverts.ad_status', 1)
                    ->where(function ($q) {
                        $q->where('adverts.sold_date', '>=', now()->subDays(7))
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
         //dd($categories);

        return view('frontend.category', compact('title', 'ads', 'user', 'categories', 'cat', 'count_cat'));
    }

    public function sub_category(Request $request, $category_slug, $subcat_slug)
    {
        $cat = Category::where('category_slug', $category_slug)->firstOrFail();
        $subcat = SubCategory::where('sub_cat_slug', $subcat_slug)->where('cat_id', $cat->id)->firstOrFail();

        $title = config('global.site_name') . ' | ' . $subcat->sub_category;

        $ads = (new FeaturedAdPaginator(request()->get('page', 1)))
            ->filters(['sub_category' => $subcat->id])
            ->paginate();

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
                          $q->where('adverts.sold_date', '>=', now()->subDays(7))
                            ->orWhereNull('adverts.sold_date');
                      });
            })
            ->select('brands.id', 'brands.brand', 'brands.brand_slug', DB::raw('COUNT(adverts.id) as advert_count'))
            ->groupBy('brands.id', 'brands.brand', 'brands.brand_slug')
            ->orderBy('advert_count', 'desc')
            ->get();

        return view('frontend.sub-category', compact('title', 'ads', 'user', 'brands', 'subcat', 'count_subcat'));
    }

    public function all_subcat(Request $request, $category_slug, $subcat_slug)
    {
        $cat = Category::where('category_slug', $category_slug)->firstOrFail();
        $subcat = SubCategory::where('sub_cat_slug', $subcat_slug)->where('cat_id', $cat->id)->firstOrFail();

        $title = config('global.site_name') . ' | ' . $subcat->sub_category . ' - All Brands';

        $ads = (new FeaturedAdPaginator(request()->get('page', 1)))
            ->filters(['sub_category' => $subcat->id])
            ->paginate();

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
                          $q->where('adverts.sold_date', '>=', now()->subDays(7))
                            ->orWhereNull('adverts.sold_date');
                      });
            })
            ->select('brands.id', 'brands.brand', 'brands.brand_slug', DB::raw('COUNT(adverts.id) as advert_count'))
            ->groupBy('brands.id', 'brands.brand', 'brands.brand_slug')
            ->orderBy('advert_count', 'desc')
            ->get();

        return view('frontend.all-subcat', compact('title','ads','user','brands','subcat','count_subcat','subcat_slug'));
    }

    public function brand(Request $request, $category_slug, $subcat_slug, $brand_slug)
    {
        $cat = Category::where('category_slug', $category_slug)->firstOrFail();
        $subcat = SubCategory::where('sub_cat_slug', $subcat_slug)->where('cat_id', $cat->id)->firstOrFail();
        $brand = Brands::where('brand_slug', $brand_slug)->where('subcat_id', $subcat->id)->firstOrFail();

        $title = config('global.site_name') . ' | ' . $brand->brand;

        $ads = (new FeaturedAdPaginator(request()->get('page', 1)))
            ->filters(['brand' => $brand->id])
            ->paginate();

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
                          $q->where('adverts.sold_date', '>=', now()->subDays(7))
                            ->orWhereNull('adverts.sold_date');
                      });
            })
            ->select('brands.id', 'brands.brand', 'brands.brand_slug', DB::raw('COUNT(adverts.id) as advert_count'))
            ->groupBy('brands.id', 'brands.brand', 'brands.brand_slug')
            ->orderBy('advert_count', 'desc')
            ->get();

        return view('frontend.brand', compact('title', 'ads', 'user', 'brand', 'brands', 'subcat', 'count_subcat'));
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
                          $q->where('adverts.sold_date', '>=', now()->subDays(7))
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
            return redirect('/login')->with('error','Sorry, you need to login to Use Buy Directly');
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
            ]);

            $sender_station = State::where('name', $ad->state)->firstOrFail();
            $data['reciever_state'] = $reciever_station = State::findOrFail($request->state);
            $data['reciever_city'] = GigLogistic::findOrFail($request->city);
            $data['shipping_method'] = $request->ship_id;

            //dd($reciever_station);

            $details = [
                'advert_id' => $id,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'phone' => $validated['phone'],
                'reciever_station' => $reciever_station->station_id,
                'sender_station' => $sender_station->station_id,
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

        if ($request->isMethod('GET')) {
            return view('frontend.report-ad', $data);
        }


        if ($request->isMethod('POST')) {
            $request->validate([
                'name' => 'required',
                'subject' => 'required',
                'message' => 'required',
                'g-recaptcha-response' => ['required', new ReCaptcha],
            ]);

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

            Mail::to(config('global.admin_email'))->send(new ReportMail($details));

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
