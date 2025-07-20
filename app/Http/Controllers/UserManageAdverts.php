<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Category;
use App\Models\Message;
use App\Models\SubCategory;
use App\Models\Brands;
use App\Models\Advert;
use App\Models\Models;
use App\Models\State;
use App\Models\AdvertImage;
use App\Models\CarDetail;
use App\Models\PhoneDetail;
use App\Models\Shipping;

class UserManageAdverts extends Controller
{
      public function fetch_subcat($cat_id)
    {
        $subcat = SubCategory::where('cat_id', $cat_id)->get();
        return response()->json($subcat);
    }

    public function fetch_brand($cat_id)
    {
        $brand = Brands::where('subcat_id', $cat_id)->get();
        return response()->json($brand);
    }

    public function post_ad(Request $request)
    {
        $title = "Post New Advert - " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('users.user_id', $user_id)->first();
        $categories = Category::orderBy('category','asc')->get();
        $states = State::all();
        $shippings = Shipping::where('status','Active')->orderBy('company','asc')->get();
        //dd($categories);
        if ($request->isMethod('POST')) {

            $ad_id = rand(00000,99999);

            $subcat = $request->input('subcategory');

            $rules =[
                'ad_title' => 'required',
                'category' => 'required',
                'subcategory' => 'required',
                'brand' => 'required',
                'price' => 'required|numeric',
                'price_type' => 'required',
                'state' => 'required',
                'lga' => 'required',
                'description' => 'required',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:20000',
            ];


            if($subcat=="31361"){
                $rules['mileage'] = 'required|numeric';
                $rules['condition'] = 'required';
                $rules['month'] = 'required';
                $rules['year'] = 'required|numeric';
                $rules['fuel'] = 'required';
                $rules['transmission'] = 'required';
                $rules['vehicle_type'] = 'required';
                $rules['doors'] = 'required';
                $rules['exterior_color'] = 'required';
                $rules['material_interior'] = 'required';
                $rules['exterior_equipment'] = 'required';
                $rules['interior'] = 'required';
            }

            if($subcat=="84676"){
                $rules['phone_color'] = 'required';
                $rules['phone_condition'] = 'required';
                $rules['device'] = 'required';
            }

            $validatedData = $request->validate($rules);

            if ($request->shipment === 'Ship' && empty($request->input('shipping'))) {
                return back()->withErrors(['shipping' => 'Please select at least one shipping method.'])->withInput();
            }


          $advert = Advert::create([
            'ad_title'=> $request->input('ad_title'),
            'ad_type'=> $request->input('ad_type'),
            'category'=> $request->input('category'),
            'sub_category'=> $request->input('subcategory'),
            'brand'=> $request->input('brand'),
            'price'=> $request->input('price'),
            'price_type'=> $request->input('price_type'),
            'buy_direct'=> $request->input('buy_direct'),
            'state'=> $request->input('state'),
            'lga'=> $request->input('lga'),
            'description'=> $request->input('description'),
            'keyword'=> $request->input('keyword'),
            'meta_description'=> $request->input('meta_description'),
            'featured'=> $request->input('featured'),
            'ad_id'=> $ad_id,
            'shipment'=> $request->input('shipment'),
            'show_contact'=> $request->input('show_contact'),
            'quantity'=> $request->input('quantity') ?? 1,
            'views'=> "0",
            'featured'=> "No",
            'ad_status'=> "1",
            'user_id'=> $user_id,
            'ad_image'=> "",
        ]);

        $advert->shippings()->sync($request->input('shipping', []));
        //dd($request->input('shipping', []));

         if ($request->hasFile('images')) {
            $images = $request->file('images');
            $order = explode(',', $request->input('image_order')); // array of index positions

            foreach ($order as $position => $index) {
                if (!isset($images[$index])) continue;

                $image = $images[$index];
                $newFileName = rand(00000, 99999) . '_' . $image->getClientOriginalName();
                $image->move('uploads/images', $newFileName);

                $advert->images()->create([
                    'image' => $newFileName,
                    'position' => $position + 1,
                ]);
            }
        }


        if($subcat=="2"){

            $car = new CarDetail([
                'car_id'=> rand(00000,99999),
                'cat_id'=> $request->input('category'),
                'brand_id'=> $request->input('brand'),
                'model'=> $request->input('model'),
                'mileage'=> $request->input('mileage'),
                'condition'=> $request->input('condition'),
                'registration_month'=> $request->input('month'),
                'registration_year'=> $request->input('year'),
                'fuel'=> $request->input('fuel'),
                'transmission'=> $request->input('transmission'),
                'vehicle_type'=> $request->input('vehicle_type'),
                'doors'=> $request->input('doors'),
                'exterior_color'=> $request->input('exterior_color'),
                'material_interior'=> $request->input('material_interior'),
                'exterior_equipment'=> json_encode($request->input('exterior_equipment')),
                'interior'=> json_encode($request->input('interior')),
                'security'=> json_encode($request->input('security')),
            ]);
            $car->advert()->associate($advert);
            $car->save();
        }

        if($subcat=="6"){
            $phone = new PhoneDetail([
                'phone_id'=> rand(00000,99999),
                'cat_id'=> $request->input('category'),
                'brand_id'=> $request->input('brand'),
                'model'=> $request->input('model'),
                'color'=> $request->input('phone_color'),
                'device'=> $request->input('device'),
                'condition'=> $request->input('phone_condition'),
            ]);
            $phone->advert()->associate($advert);
            $phone->save();
        }

        if ($request->has('promotion')) {
            $request->session()->put('promotion', $request->promotion);
            return redirect('/user/post-boost-ad/' . $advert->id);
        }
        return redirect('/user/post-ad')->with('success', 'Your Advert Has successfully been Posted');

        }

        if ($request->isMethod('GET')) {
            return view('dashboard.post-ad', compact('title','categories','user','shippings','states'));
        }
    }


    public function edit_ad(Request $request, $ad_id)
{
    $title = "Edit Advert - " . config('global.site_name');
    $user_id = $request->session()->get('user_id');
    $user = User::where('user_id', $user_id)->first();
    $categories = Category::orderBy('category','asc')->get();

    $advert = Advert::with(['images', 'car', 'phone'])
        ->where('id', $ad_id)
        ->where('user_id', $user_id)
        ->firstOrFail();

    // Load dependent data
    $subcategories = SubCategory::where('cat_id', $advert->category)->get();
    $brands = Brands::where('subcat_id', $advert->sub_category)->get();

    $models = collect();
    if ($advert->brand) {
        $models = Models::where('brand_id', $advert->brand)->get();
    }
    //dd($models);

    // Parse car registration if exists
    $registration = [];
    if ($advert->car_details) {
        $regParts = explode(' ', $advert->car_details->registration);
        $registration = [
            'month' => $regParts[0] ?? '',
            'year' => $regParts[1] ?? ''
        ];
    }

    if ($request->isMethod('POST')) {
        return $this->update_ad($request, $advert);
    }

    return view('dashboard.edit-ad', compact(
        'title',
        'categories',
        'user',
        'advert',
        'subcategories',
        'brands',
        'models',
        'registration'
    ));
}

    protected function update_ad(Request $request, $advert)
{
    $subcat = $request->input('subcategory');

    $rules = [
        'ad_title' => 'required',
        'category' => 'required',
        'subcategory' => 'required',
        'brand' => 'required',
        'price' => 'required|numeric',
        'price_type' => 'required',
        'state' => 'required',
        'lga' => 'required',
        'description' => 'required',
        'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:20288', // 12MB max
    ];

    // Add your conditional rules as in post_ad

    $validatedData = $request->validate($rules);

    // Update main advert
    $advert->update([
        'ad_title'=> $request->input('ad_title'),
        'ad_type'=> $request->input('ad_type'),
        'category'=> $request->input('category'),
        'sub_category'=> $request->input('subcategory'),
        'brand'=> $request->input('brand'),
        'price'=> $request->input('price'),
        'price_type'=> $request->input('price_type'),
        'buy_direct'=> $request->input('buy_direct'),
        'state'=> $request->input('state'),
        'lga'=> $request->input('lga'),
        'description'=> $request->input('description'),
        'keyword'=> $request->input('keyword'),
        'meta_description'=> $request->input('meta_description'),
        'featured'=> $request->input('featured'),
        'shipment'=> $request->input('shipment'),
        'show_contact'=> $request->input('show_contact'),
        'quantity'=> $request->input('quantity') ?? 1,
        'ad_image'=> "",
    ]);

    // Handle deleted images
    if ($request->has('deleted_images')) {
        foreach ($request->input('deleted_images') as $imageId) {
            $image = $advert->images()->find($imageId);
            if ($image) {
                // Delete the file from storage
                if (file_exists(public_path('uploads/images/' . $image->image))) {
                    unlink(public_path('uploads/images/' . $image->image));
                }
                $image->delete();
            }
        }
    }

    if ($request->has('existing_image_order')) {
        $orderedIds = explode(',', $request->input('existing_image_order')); // e.g. [3,1,2]
        //dd($orderedIds);
        foreach ($orderedIds as $index => $imageId) {
            DB::table('advert_images')
                ->where('id', $imageId)
                ->where('advert_id', $advert->id)
                ->update(['position' => $index + 1]);
        }
    }

    // Handle new image uploads
    $newPositionStart = count($orderedIds) + 1;

    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $index => $image) {
            $newFileName = rand(00000, 99999) . '_' . $image->getClientOriginalName();
            $image->move('uploads/images', $newFileName);

            $advert->images()->create([
                'image' => $newFileName,
                'position' => $newPositionStart + $index // starts at 1 or after existing
            ]);
        }
    }





    //dd($subcat);
    // Update car or phone details
    if($subcat=="2"){
        $registration = $request->input('month') . " ".$request->input('year');
        $advert->car->update([
            'cat_id'=> $request->input('category'),
            'brand_id'=> $request->input('brand'),
            'model'=> $request->input('model'),
            'mileage'=> $request->input('mileage'),
            'condition'=> $request->input('condition'),
            'registration'=> $registration,
            'fuel'=> $request->input('fuel'),
            'transmission'=> $request->input('transmission'),
            'vehicle_type'=> $request->input('vehicle_type'),
            'doors'=> $request->input('doors'),
            'exterior_color'=> $request->input('exterior_color'),
            'material_interior'=> $request->input('material_interior'),
            'exterior_equipment'=> implode(",",$request->input('exterior_equipment', [])),
            'interior'=> implode(",",$request->input('interior', [])),
            'security'=> implode(",",$request->input('security', [])),
        ]);
    }

    if($subcat=="6"){
        $advert->phone->update([
            'phone_id'=> rand(00000,99999),
            'cat_id'=> $request->input('category'),
            'brand_id'=> $request->input('brand'),
            'model'=> $request->input('model'),
            'color'=> $request->input('phone_color'),
            'device'=> $request->input('device'),
            'condition'=> $request->input('phone_condition'),
        ]);
    }

    return redirect()->back()->with('success', 'Advert updated successfully');
}

public function boost_ad(Request $request, $id)
    {
        $title = "Boost Ad | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();
        $price = 500;
        $advert = Advert::with(['images', 'car', 'phone'])
                        ->where('id', $id)
                        ->where('user_id', $user_id)
                        ->firstOrFail();

        return view('dashboard.boost-ad', compact('title','user','advert','count_ads', 'price'));
    }

    public function post_boost_ad(Request $request, $id)
    {
        $title = "Boost Ad | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $promotion = $request->session()->get('promotion');
        $count_ads = Advert::where('user_id', $user_id)->count();
        $advert = Advert::with(['images', 'car', 'phone'])
                        ->where('id', $id)
                        ->where('user_id', $user_id)
                        ->firstOrFail();

        return view('dashboard.post-boost-ad', compact('title','user','advert','count_ads','promotion'));
    }

    public function boosted_ad(Request $request, $id)
    {
        $title = "Boosted Ad | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();
        $price = 500;
        $advert = Advert::with(['images', 'car', 'phone', 'boost'])
                ->where('id', $id)
                ->where('user_id', $user_id)
                ->firstOrFail();

        return view('dashboard.boosted-ad', compact('title','user','advert','count_ads', 'price'));
    }

}
