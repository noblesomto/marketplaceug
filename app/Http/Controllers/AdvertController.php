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
use App\Models\GigLogistic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Rules\ReCaptcha;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;



class AdvertController extends Controller
{
    public function advert(Request $request, $id, $slug)
    {
        $data['ad'] = Advert::with('images','owner')->where('id', $id)->first();
        $data['title'] = $data['ad']->ad_title.' - '.config('global.site_name');
        $title = $data['ad']->ad_title;
        $cat_id = $data['ad']->category;
        $brand_id = $data['ad']->brand;
        //$model_id = $data['ad']->model;
        $ad_owner = $data['ad']->user_id;
        $subcat_id = $data['ad']->sub_category;
        $user_id = $request->session()->get('user_id');
        foreach ($data['ad']->images as $img) {
            $path = public_path('uploads/images/' . $img->image);
            if (File::exists($path)) {
                [$width, $height] = getimagesize($path);
                $img->is_portrait = $height > $width;
            } else {
                $img->is_portrait = false; // default to landscape
            }
        }
        $data['user'] = User::where('user_id', $user_id)->first();
        //dd($data['user']);
        $data['ad_owner'] = User::where('user_id', $ad_owner)->first();
        $data['cat'] = Category::where('id', $cat_id)->first();
        $data['brand'] = Brands::where('id', $brand_id)->first();
        $data['car'] = CarDetail::where('advert_id', $id)->first();
        if ($data['car']) {
            $model_id = $data['car']->model;
            $data['model'] = Models::where('id', $model_id)->first();
        }
        $data['phone'] = PhoneDetail::where('advert_id', $id)->first();
        if ($data['phone']) {
            $model_id = $data['phone']->model;
            $data['model'] = Models::where('id', $model_id)->first();
        }
        $data['count_ads'] = Advert::where('user_id', $ad_owner)->count();
        //dd($data['cat']);
        //Related Adverts
        $query = Advert::with('images')
            ->inRandomOrder()
            ->where('user_id', $ad_owner)
            ->where('ad_status', 1)
            ->where('id', '!=', $id);  // or ->whereNot('id', $id)

        // Get the count
        $data['advertsCount'] = $query->count();

        // Get the limited results
        $data['adverts'] = $query->limit(6)->get();

        //Similar Adverts
        $data['similar_ads'] = Advert::with('images')
            ->inRandomOrder()
            ->where('ad_title', 'LIKE', '%' . $title . '%') // Partial match
            ->orWhere('category', $cat_id) 
            ->where('id', '!=', $id)
            ->where('ad_status', 1)
            ->where('user_id', '!=', $ad_owner)
            ->limit(3)
            ->get();


        //dd($data['adverts']);
        \DB::table('adverts')
            ->where('id', $id)
            ->increment('views', 1);

        return view('frontend.advert', $data);
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
        $ads = Advert::with('firstImage')->orderBy('created_at', 'asc')->limit(10)->get();
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $categories = Category::with('subCategories')->get();

        //dd($categories);
        return view('frontend.adverts', compact('title', 'ads', 'user', 'categories'));
    }

    public function seller(Request $request, $id)
    {
        $title = config('global.site_name').' | '.config('global.site_title');
        $ads = Advert::with('firstImage','owner')->where('user_id', $id)->where('ad_status', 1)->orderBy('created_at', 'asc')->limit(10)->get();
        $user_id = $request->session()->get('user_id');
        $owner = User::where('user_id', $id)->first();
        $user = User::where('user_id', $user_id)->first();
        $categories = Category::with('subCategories')->get();
        $count_ads = Advert::where('user_id', $id)->count();

        //dd($categories);
        return view('frontend.seller-adverts', compact('title', 'ads', 'user', 'owner', 'categories','count_ads'));
    }

   

    public function search(Request $request)
    {
        $title = config('global.site_name').' | '.config('global.site_title');

        $request->validate([
                'product' => 'required|string|min:3',
                'location' => 'required',
                'category' => 'required',
            ]);

        $ads = Advert::with('firstImage')
                    ->where('ad_title', 'LIKE', '%' . $request->product . '%') // Partial match
                    ->where('category', $request->category) 
                    ->where('state', $request->location) 
                    ->where('ad_status', 1)
                    ->orderBy('created_at', 'asc')
                    ->paginate(10);
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $categories = Category::with('subCategories')->get();

        //dd($categories);
        return view('frontend.adverts', compact('title', 'ads', 'user', 'categories'));
    }

    public function all_categories(Request $request)
    {
        $title = config('global.site_name').' | '.config('global.site_title');
        $ads = Advert::with('firstImage')->orderBy('created_at', 'asc')->limit(10)->get();
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $categories = Category::with('subCategories')->get();

        $categoryCounts = Category::leftJoin('adverts', 'categories.id', '=', 'adverts.category')
            ->leftJoin('sub_categories as sc', function ($join) {
                $join->on('adverts.sub_category', '=', 'sc.id')
                    ->orOn('categories.id', '=', 'sc.cat_id'); // Ensure subcategories without adverts are included
            })
            ->selectRaw('categories.category AS category_name, sc.sub_category AS sub_category_name, COUNT(adverts.id) AS advert_count')
            ->groupBy('categories.category', 'sc.sub_category')
            ->get();

        //dd($categoryCounts);
        return view('frontend.all-categories', compact('title', 'ads', 'user', 'categories'));
    }

    public function category(Request $request, $id, $slug)
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
        return view('frontend.category', compact('title', 'ads', 'user', 'categories', 'cat', 'count_cat'));
    }

    public function sub_category(Request $request, $id, $slug)
    {
        $title = config('global.site_name').' | '.config('global.site_title');
        $subcat_id = $id;
        $ads = Advert::with('firstImage')->where('sub_category', $id)->orderBy('created_at', 'asc')->paginate(20);
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $subcat = SubCategory::where('id', $id)->first();
        $count_subcat = Advert::where('sub_category', $id)->count();
        $brands = DB::table('brands')
            ->leftJoin('adverts', 'brands.id', '=', 'adverts.brand')
            ->where('brands.subcat_id', $id)
            ->select('brands.id', 'brands.brand', 'brands.brand_slug', DB::raw('COUNT(adverts.id) as advert_count'))
            ->groupBy('brands.id', 'brands.brand', 'brands.brand_slug')
            ->orderBy('advert_count', 'desc') // Optional: Order by count if needed
            ->get();

        //dd($count_subcat);
        return view('frontend.sub-category', compact('title', 'ads', 'user', 'brands', 'subcat_id', 'subcat', 'count_subcat'));
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
        $data['ad'] = Advert::with('images','shippings')->where('id', $id)->first();
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

            $commission = 0.05 * $ad->price;
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


        return view('frontend.buy-direct-payment', $data);
    }

    public function report_advert(Request $request, $id)
    {
        $data['title'] = 'Report Advert | '.config('global.site_name');
        $data['ad'] = Advert::with('images')->where('id', $id)->first();
        $user_id = $request->session()->get('user_id');
        $data['user'] = $user = User::where('user_id', $user_id)->first();

        if ($request->isMethod('GET')) {
            return view('frontend.report-ad', $data);
        }


        if ($request->isMethod('POST')) {
            $request->validate([
                'name' => 'required',
                'message' => 'required',
                'g-recaptcha-response' => ['required', new ReCaptcha],
            ]);

            $message = Reports::create([
                'advert_id' => $id,
                'user_id' => $user_id,
                'message' => $request->message,
            ]);

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
