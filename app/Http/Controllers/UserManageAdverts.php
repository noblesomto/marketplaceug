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
use App\Helpers\ContentHelper;
use App\Helpers\FileUploadHelper;

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
        $categories = Category::orderBy('category', 'asc')->get();
        $states = State::all();
        $shippings = Shipping::where('status', 'Active')->orderBy('company', 'asc')->get();

        if ($request->isMethod('POST')) {
            $ad_id = rand(10000, 99999);
            $subcat = (int) $request->input('subcategory');
            $category = (int) $request->input('category');
            //dd($category);
                $rules = [
                'ad_title'    => 'required',
                'category'    => 'required',
                'subcategory' => 'required',
                'brand'       => 'required',
                'state'       => 'required',
                'lga'         => 'required',
                'description' => 'required',
                'images.*'    => 'image|mimes:jpeg,png,jpg,gif|max:21000',
            ];

            if ($category != 3) {
                $rules['images'] = 'required|array';
            }

            // Category-specific rules
            if ($category == 3) {
                $rules += [
                    'salary' => 'required',
                ];
            } elseif ($category == 18) {
                $rules += [
                    'expected_salary' => 'required',
                ];
            }elseif ($category == 11) {
                $rules += [
                    'price'      => 'nullable|numeric',
                ];
            } else {
                $rules += [
                    'price'      => 'required|numeric',
                    'price_type' => 'required',
                ];
            }

            // Subcategory-specific rules
            switch ($subcat) {
                case 2:
                    $rules += [
                        'model'        => 'required',
                        'registration' => 'required',
                        'mileage'      => 'required|numeric',
                        'condition'    => 'required',
                        'fuel'         => 'required',
                        'transmission' => 'required',
                        'vehicle_type' => 'required',
                        'doors'        => 'required',
                    ];
                    break;

                case 6:
                    $rules += [
                        'phone_color'     => 'required',
                        'phone_condition' => 'required',
                        'device'          => 'required',
                    ];
                    break;
            }

            // Item condition rule (skip if category is 3 or 18, OR subcat is 2 or 6)
            if (!in_array($category, [3, 11, 18]) && !in_array($subcat, [2, 6])) {
                $rules['item_condition'] = 'required';
            }

            $validatedData = $request->validate($rules);


            if ($request->shipment === 'Ship' && empty($request->input('shipping'))) {
                return back()->withErrors(['shipping' => 'Please select at least one shipping method.'])->withInput();
            }

            $metaDescription = Str::limit(strip_tags($request->input('description')), 150, '');
            $rawWords = explode(' ', Str::slug($request->input('ad_title') . ' ' . $request->input('description'), ' '));
            $filteredWords = array_filter($rawWords, function ($word) {
            return strlen($word) > 3;
            });

            $uniqueWords = array_unique($filteredWords);

            $keywords = implode(', ', array_slice($uniqueWords, 0, 10));

            $advert = Advert::create([
                'ad_title'         => $request->input('ad_title'),
                'ad_type'          => $request->input('ad_type'),
                'category'         => $request->input('category'),
                'sub_category'     => $request->input('subcategory'),
                'brand'            => $request->input('brand'),
                'price'            => $request->input('price'),
                'contact_price'    => $request->input('contact_price'),
                'salary'           => $request->input('salary'),
                'expected_salary'  => $request->input('expected_salary'),
                'item_condition'   => $request->input('item_condition'),
                'price_type'       => $request->input('price_type'),
                'buy_direct'       => $request->input('buy_direct'),
                'state'            => $request->input('state'),
                'lga'              => $request->input('lga'),
                'state_slug'       => Str::slug($request->input('lga')),
                'description' => ContentHelper::sanitizeContent($request->input('description')),
                'keyword'          => $keywords,
                'meta_description' => $metaDescription,
                'featured'         => "No",
                'ad_id'            => $ad_id,
                'shipment'         => $request->input('shipment'),
                'show_contact'     => $request->input('show_contact'),
                'quantity'         => $request->input('quantity') ?? 1,
                'views'            => "0",
                'ad_status'        => "1",
                'user_id'          => $user_id,
                'ad_image'         => "",
            ]);

            $advert->shippings()->sync($request->input('shipping', []));

            // Handle uploaded images using FileUploadHelper
            if ($request->hasFile('images')) {
                $images = $request->file('images');
                $order = explode(',', $request->input('image_order')); // e.g. "1,0,2"

                foreach ($order as $position => $index) {
                    if (!isset($images[$index]) || !$images[$index]->isValid()) continue;

                    $uploadedFileName = FileUploadHelper::upload($images[$index], 'images');

                    $advert->images()->create([
                        'image'    => $uploadedFileName,
                        'position' => $position + 1,
                    ]);
                }
            }elseif ($category == 3) {
                // Save default image when no upload
                $advert->images()->create([
                    'image'    => 'jobs.png',
                    'position' => 1,
                ]);
            }

            // Store car-specific info
            if ($subcat === 2) {
                $car = new CarDetail([
                    'car_id'            => rand(10000, 99999),
                    'cat_id'            => $request->input('category'),
                    'brand_id'          => $request->input('brand'),
                    'model'             => $request->input('model'),
                    'mileage'           => $request->input('mileage'),
                    'condition'         => $request->input('condition'),
                    'registration'      => $request->input('registration'),
                    'fuel'              => $request->input('fuel'),
                    'transmission'      => $request->input('transmission'),
                    'vehicle_type'      => $request->input('vehicle_type'),
                    'doors'             => $request->input('doors'),
                    'exterior_color'    => $request->input('exterior_color'),
                    'material_interior' => $request->input('material_interior'),
                    'exterior_equipment'=> json_encode($request->input('exterior_equipment')),
                    'interior'          => json_encode($request->input('interior')),
                    'security'          => json_encode($request->input('security')),
                ]);
                $car->advert()->associate($advert);
                $car->save();
            }

            // Store phone-specific info
            if ($subcat === 6) {
                $phone = new PhoneDetail([
                    'phone_id'  => rand(10000, 99999),
                    'cat_id'    => $request->input('category'),
                    'brand_id'  => $request->input('brand'),
                    'model'     => $request->input('model'),
                    'color'     => $request->input('phone_color'),
                    'device'    => $request->input('device'),
                    'condition' => $request->input('phone_condition'),
                ]);
                $phone->advert()->associate($advert);
                $phone->save();
            }

            // Handle promotion
            if ($request->has('promotion')) {
                $request->session()->put('promotion', $request->promotion);
                return redirect('/user/post-boost-ad/' . $advert->id);
            }

            return redirect('/user/my-ads')->with('success', 'Your Advert Has successfully been Posted');
        }

        if ($request->isMethod('GET')) {
            return view('dashboard.post-ad', compact('title', 'categories', 'user', 'shippings', 'states'));
        }
    }


    public function edit_ad(Request $request, $ad_id)
    {
        $title = "Edit Advert - " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $categories = Category::orderBy('category','asc')->get();
        $states = State::all();
        $shippings = Shipping::where('status', 'Active')->orderBy('company', 'asc')->get();
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
            'registration',
            'states',
            'shippings'
        ));
    }

    protected function update_ad(Request $request, $advert)
    {
        $subcat = (int) $request->input('subcategory');
        $category = (int) $request->input('category');

        $rules = [
            'ad_title'    => 'required',
            'category'    => 'required',
            'subcategory' => 'required',
            'brand'       => 'required',
            'state'       => 'required',
            'lga'         => 'required',
            'description' => 'required',
            //'images'      => 'required|array',
            'images.*'    => 'image|mimes:jpeg,png,jpg,gif|max:21000',
        ];

        // Category-specific rules
        if ($category == 3) {
            $rules += [
                'salary' => 'required',
            ];
        } elseif ($category == 18) {
            $rules += [
                'expected_salary' => 'required',
            ];
        }elseif ($category == 11) {
            $rules += [
                'price'      => 'nullable|numeric',
            ];
        } else {
            $rules += [
                'price'      => 'required|numeric',
                'price_type' => 'required',
            ];
        }

        // Subcategory-specific rules
        switch ($subcat) {
            case 2:
                $rules += [
                    'model'        => 'required',
                    'registration' => 'required',
                    'mileage'      => 'required|numeric',
                    'condition'    => 'required',
                    'fuel'         => 'required',
                    'transmission' => 'required',
                    'vehicle_type' => 'required',
                    'doors'        => 'required',
                ];
                break;

            case 6:
                $rules += [
                    'phone_color'     => 'required',
                    'phone_condition' => 'required',
                    'device'          => 'required',
                ];
                break;
        }

        // Item condition rule (skip if category is 3 or 18, OR subcat is 2 or 6)
        if (!in_array($category, [3, 11, 18]) && !in_array($subcat, [2, 6])) {
            $rules['item_condition'] = 'required';
        }

        $validatedData = $request->validate($rules);

        $metaDescription = Str::limit(strip_tags($request->input('description')), 150, '');
        $rawWords = explode(' ', Str::slug($request->input('ad_title') . ' ' . $request->input('description'), ' '));
        $filteredWords = array_filter($rawWords, function ($word) {
        return strlen($word) > 3;
        });

        $uniqueWords = array_unique($filteredWords);

        $keywords = implode(', ', array_slice($uniqueWords, 0, 10));

        //dd($keywords);
        // Update main advert
        $advert->update([
            'ad_title'        => $request->input('ad_title'),
            'ad_type'         => $request->input('ad_type'),
            'category'        => $request->input('category'),
            'sub_category'    => $request->input('subcategory'),
            'brand'           => $request->input('brand'),
            'price'           => $request->input('price'),
            'price_type'      => $request->input('price_type'),
            'contact_price'    => $request->input('contact_price'),
            'salary'           => $request->input('salary'),
            'expected_salary'  => $request->input('expected_salary'),
            'item_condition'  => $request->input('item_condition'),
            'buy_direct'      => $request->input('buy_direct'),
            'state'           => $request->input('state'),
            'lga'             => $request->input('lga'),
            'state_slug'      => Str::slug($request->input('lga')),
            'description'       => ContentHelper::sanitizeContent($request->input('description')),
            'featured'         => $advert->featured,
            'keyword'         => $keywords,
            'meta_description'=> $metaDescription,
            'shipment'        => $request->input('shipment'),
            'show_contact'    => $request->input('show_contact'),
            'quantity'        => $request->input('quantity') ?? 1,
            'ad_image'        => "",
        ]);

        // Handle deleted images
        if ($request->has('deleted_images')) {
            foreach ($request->input('deleted_images') as $imageId) {
                $image = $advert->images()->find($imageId);
                if ($image) {
                    FileUploadHelper::delete('images', $image->image);
                    $image->delete();
                }
            }
        }

        // Handle image reordering
        $orderedIds = [];
        if ($request->filled('existing_image_order')) {
            $orderedIds = explode(',', $request->input('existing_image_order'));
            foreach ($orderedIds as $index => $imageId) {
                DB::table('advert_images')
                    ->where('id', $imageId)
                    ->where('advert_id', $advert->id)
                    ->update(['position' => $index + 1]);
            }
        }

        $newPositionStart = count($orderedIds) > 0
            ? count($orderedIds) + 1
            : ($advert->images()->count() + 1);

        // Upload new images using helper
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                if ($image->isValid()) {
                    $uploadedFileName = FileUploadHelper::upload($image, 'images');
                    $advert->images()->create([
                        'image'    => $uploadedFileName,
                        'position' => $newPositionStart + $index,
                    ]);
                }
            }
        }

        // Update Car details
        if ($subcat === 2 && $advert->car) {
            $advert->car->update([
                'cat_id'            => $request->input('category'),
                'brand_id'          => $request->input('brand'),
                'model'             => $request->input('model'),
                'mileage'           => $request->input('mileage'),
                'condition'         => $request->input('condition'),
                'registration'      => $request->input('registration'),
                'fuel'              => $request->input('fuel'),
                'transmission'      => $request->input('transmission'),
                'vehicle_type'      => $request->input('vehicle_type'),
                'doors'             => $request->input('doors'),
                'exterior_color'    => $request->input('exterior_color'),
                'material_interior' => $request->input('material_interior'),
                'exterior_equipment'=> json_encode($request->input('exterior_equipment')),
                'interior'          => json_encode($request->input('interior')),
                'security'          => json_encode($request->input('security')),
            ]);
        }

        // Update Phone details
        if ($subcat === 6 && $advert->phone) {
            $advert->phone->update([
                'phone_id'  => rand(10000, 99999),
                'cat_id'    => $request->input('category'),
                'brand_id'  => $request->input('brand'),
                'model'     => $request->input('model'),
                'color'     => $request->input('phone_color'),
                'device'    => $request->input('device'),
                'condition' => $request->input('phone_condition'),
            ]);
        }

        return redirect('/user/my-ads')->with('success', 'Advert updated successfully');
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


       public function delete_ad($id)
    {
        try {
            \DB::beginTransaction();

            $advert = Advert::with('images')->find($id);
            if (!$advert) {
                return redirect()->back()->with('error', 'Advert not found');
            }

            // Delete all associated images safely (except for category Job which uses default image)
            if ($advert->category != 3) {
                foreach ($advert->images ?? [] as $image) {
                    if ($image && !empty($image->image)) {
                        try {
                            // Use your helper to delete the file
                            FileUploadHelper::delete('images', $image->image);
                            // Delete the image record from database
                            $image->delete();
                        } catch (\Exception $imgEx) {
                            throw new \Exception("Unable to delete image file");
                        }
                    }
                }
            }

            // Delete the advert itself
            $advert->delete();

            \DB::commit();

            return redirect()->back()->with('success', 'Advert deleted successfully');

        } catch (\Exception $e) {
            \DB::rollBack();

            // Log the actual error for debugging
            \Log::error('Failed to delete advert', [
                'advert_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Failed to delete advert');
        }
    }

}
