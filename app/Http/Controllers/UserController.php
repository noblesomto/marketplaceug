<?php

namespace App\Http\Controllers;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
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
use App\Models\Payment;
use App\Models\Wishlist;
use App\Models\Shipping;
use App\Models\Followers;
use Carbon\Carbon;

class UserController extends Controller
{
    public function index(Request $request)
    {   
        $title = "User Dashboard  - " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('users.user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();
        $ads = Advert::with('firstImage')->orderBy('created_at', 'asc')->where('user_id', $user_id)->paginate(20);
        return view('dashboard.index', compact('title','user','ads','count_ads'));
    }

    public function category(Request $request)
    {   
        $title = "User Dashboard  - " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('users.user_id', $user_id)->first();
        $categories = Category::orderBy('category','asc')->get();
        
        return view('dashboard.category', compact('title','user','categories'));
    }


    public function messages(Request $request)
    {
        $title = "User Dashboard  - " . config('global.site_name');
        $userId = $request->session()->get('user_id');
        $user = User::where('users.user_id', $userId)->first();
        $count_ads = Advert::where('user_id', $userId)->count();
        // Fetch distinct conversations for the logged-in user as either sender or receiver
        $conversations = Message::select(
                DB::raw("(CASE WHEN sender_id = {$userId} THEN receiver_id ELSE sender_id END) AS other_user_id"),
                'advert_id'
            )
            ->where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->distinct()
            ->get();

        // Load additional details
        $conversations = $conversations->map(function ($conversation) use ($userId) {
            // Fetch advert and other user details
            $advert = Advert::find($conversation->advert_id);
            $otherUserId = $conversation->other_user_id;
            $otherUser = User::find($otherUserId);

            // Count unread messages in this conversation
            $unreadCount = Message::where('advert_id', $conversation->advert_id)
                ->where('receiver_id', $userId)
                ->where('sender_id', $otherUserId)
                ->where('is_read', false)
                ->count();

            return [
                'advert' => $advert,
                'other_user' => $otherUser,
                'unread_count' => $unreadCount
            ];
        });

        return view('dashboard.messages', compact('title','user', 'conversations','count_ads'));
    }



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



    public function my_ads(Request $request)
    {   
        $title = "My Orders | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('users.user_id', $user_id)->first();
        $ads = Advert::with('firstImage')->orderBy('created_at', 'asc')->where('user_id', $user_id)->paginate(20);
        $count_ads = Advert::where('user_id', $user_id)->count();
        return view('dashboard.my-ads', compact('title','user', 'ads','count_ads'));
    }

    public function ad_status($status , $id)
    {   
        DB::table('adverts')
            ->where('id', $id)
            ->update([
                'ad_status'=> $status,
            ]);
 
        return redirect("user/my-ads")->with('success', 'Advert Status Updated');
    
    }

    public function add_wishlist(Request $request, $id)
    {   
        $user_id = $request->session()->get('user_id');
        $user = User::where('users.user_id', $user_id)->first();
        $advert = Advert::where('id', $id)->first();

        // Check if post exists
        $post = Wishlist::where('advert_id',$id)->first();
        
        //dd($post);
        if ($post) {
            // Post exists - return view with message
            return redirect()->back()->with('error','Already Added to Wishlist');
        }
        
        // Post doesn't exist - create new one
        $newpost = new Wishlist([
                'user_id'=> $user_id,
            ]);
            $newpost->advert()->associate($advert);
            $newpost->save();
        
        return redirect()->back()->with('success','Successfully Added to Wishlist');
        
    }

    public function favourites(Request $request)
    {
        $title = "Favourite Adverts | " . config('global.site_name');
        
        $userId = $request->session()->get('user_id');
        
        $user = User::find($userId);
        if (!$user) {
            abort(404, 'User not found');
        }
        
        $favoriteAds = Advert::with('firstImage')
            ->whereHas('wishlists', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->orderBy('created_at', 'asc')
            ->paginate(20);
        
        $countAds = Advert::where('user_id', $userId)->count();
        
        return view('dashboard.favourites', compact(
            'title',
            'user',
            'favoriteAds',
            'countAds'
        ));
    }

  public function payments(Request $request)
    {
        $userId = $request->session()->get('user_id');
        $user = User::findOrFail($userId);

        // Eager load advert and its firstImage
        $buyAds = Payment::with(['advert.firstImage','shipping'])
            ->where('user_id', $userId)
            ->paginate(10);

        return view('dashboard.payments', [
            'title' => "Buy Direct Adverts | " . config('global.site_name'),
            'user' => $user,
            'buyAds' => $buyAds,
        ]);
    }

    public function ad_shipping(Request $request, $id)
    {
        $userId = $request->session()->get('user_id');
        $user = User::findOrFail($userId);

        // Eager load advert and its firstImage
        $ad = Payment::with(['advert.firstImage','shipping'])
            ->where('advert_id', $id)
            ->first();
        //dd($ad);
        return view('dashboard.ad-shipping', [
            'title' => "Ad Shipping | " . config('global.site_name'),
            'user' => $user,
            'ad' => $ad,
        ]);
    }

    public function update_shipping(Request $request, $id)
    {   
        $request->validate([
            'shipping_status' => 'required'
        ]);

        DB::table('payments')
            ->where('id', $id)
            ->update([
                'shipping_status'=> $request->input('shipping_status'),
                'shipping_status_date'=> now(),
            ]);
 
        return redirect()->back()->with('success', 'Shipping Status Updated');
    
    }

    public function advert_sold($id)
    {   
        DB::table('adverts')
            ->where('id', $id)
            ->update([
                'sold'=> "Yes",
                'sold_date'=> Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
 
        return redirect()->back()->with('success', 'Advert Marked Sold');
    
    }



    public function profile(Request $request)
    {   
        $title = "My Profile | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();

        return view('dashboard.settings.profile', compact('title','user','count_ads'));

    }

    public function settings(Request $request)
    {   
        $title = "Seetings | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();

        return view('dashboard.settings.settings', compact('title','user','count_ads'));

    }


    public function profile_address(Request $request)
    {   
        $title = "My Profile | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();
        if ($request->isMethod('GET')) {
            return view('dashboard.settings.profile-address', compact('title','user','count_ads'));
        }

         if ($request->isMethod('PUT')) {

            $request->validate([
                'name' => 'required',
                'address' => 'required',
                'city' => 'required',
                'state' => 'required',
                'profile_image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:12048',

            ]);

            if ($request->hasFile('profile_image')) {
                $image = $request->file('profile_image');
                $imageName = time().'.'.$image->extension();
                $imageName = str_replace(' ', '-', $imageName);
                $request->file('profile_image')->move('uploads/profile', $imageName);
     
                $user = DB::table('users')
                    ->where('user_id', $user_id)
                    ->update([
                        'name'=> $request->input('name'),
                        'address'=> $request->input('address'),
                        'city'=> $request->input('city'),
                        'state'=> $request->input('state'),
                        'profile_picture'=> $imageName,
                    ]);
            }else{
                $user = DB::table('users')
                    ->where('user_id', $user_id)
                    ->update([
                        'name'=> $request->input('name'),
                        'address'=> $request->input('address'),
                        'city'=> $request->input('city'),
                        'state'=> $request->input('state'),
                    ]);
            }
            
        

             return redirect()->back()->with('success', 'Profile Information updated successfully!');
        }
    }

    public function profile_info(Request $request)
    {   
        $title = "My Profile | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();
        
        return view('dashboard.settings.profile-info', compact('title','user','count_ads'));
    }

    public function profile_phone(Request $request)
    {   
        $title = "My Profile | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();
    
        $request->validate([
            'phone' => 'required|numeric',
        ]);


        $user = DB::table('users')
            ->where('user_id', $user_id)
            ->update([
                'phone'=> $request->input('phone'),
            ]);
        

        return redirect()->back()->with('success', 'Profile Information updated successfully!');

    }

    public function payment_info(Request $request)
    {   
        $title = "Payment Information | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();
        if ($request->isMethod('GET')) {
            return view('dashboard.settings.payment-info', compact('title','user','count_ads'));
        }

         if ($request->isMethod('PUT')) {

            $request->validate([
                'bank_name' => 'required',
                'account_number' => 'required',
                'account_name' => 'required',
            ]);

  
            $user = DB::table('users')
                ->where('user_id', $user_id)
                ->update([
                    'bank_name'=> $request->input('bank_name'),
                    'account_name'=> $request->input('account_name'),
                    'account_number'=> $request->input('account_number'),
                ]);
        

             return redirect()->back()->with('success', 'Payment Information updated successfully!');
        }
    }

    public function change_password(Request $request)
    {   
        $title = "My Profile | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();
    
        $request->validate([
            'old_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $password = $request->old_password;

        if (Hash::check($password, $user->password)) {

            $user = DB::table('users')
                ->where('user_id', $user_id)
                ->update([
                    'password'=> Hash::make($request->input('password')),
                ]);
        
            return redirect()->back()->with('success', 'Password Changed successfully!');
        }else{

            return redirect()->back()->with('error', 'The Current Password Does not match!');
        }

    }

    public function disable_account(Request $request)
    {   
        $title = "My Profile | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();
    
    
        $user = DB::table('users')
            ->where('user_id', $user_id)
            ->update([
                'acc_status'=> 0,
                'disable_account'=> "yes",
                'disable_account_date'=> Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

        $request->session()->forget('user_id');
        return redirect("login")->with('success', 'Account Disabled successfully!');

    }

    public function profile_notification(Request $request)
    {   
        $title = "My Profile | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();
        
        return view('dashboard.settings.profile-notification', compact('title','user','count_ads'));
    }

    public function updateNotifications(Request $request)
    {
        $validated = $request->validate([
            'notifications' => 'required'
        ]);

        //dd($validated['notifications']);

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $user->notification = $validated['notifications'];
        $user->save();

        return response()->json([
            'success' => true,
         ]);
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


    public function checkFollowing(Request $request, $userId)
    {
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();

        $isFollowing = Followers::where([
            'user_id' => $user_id,
            'follow' => $userId
        ])->exists();

        return response()->json(['isFollowing' => $isFollowing]);
    }

    public function toggleFollow(Request $request)
    {
        $user_id = $request->session()->get('user_id');

        $request->validate([
            'followee_id' => 'required|exists:users,user_id'
        ]);

        $follow = Followers::where([
            'user_id' => $user_id,
            'follow' => $request->followee_id
        ])->first();

        if ($follow) {
            $follow->delete();
            return response()->json(['isFollowing' => false, 'message' => 'Unfollowed successfully']);
        } else {
            Followers::create([
                'user_id' => $user_id,
                'follow' => $request->followee_id
            ]);
            return response()->json(['isFollowing' => true, 'message' => 'Followed successfully']);
        }
    }

    public function logout(Request $request)
    {   
        $request->session()->forget('user_id');
        return redirect("login")->with('success', 'Logged Out successfully!');
    }
}
