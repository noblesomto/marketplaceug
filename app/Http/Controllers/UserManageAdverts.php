<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\NewAdMail;
use App\Models\User;
use App\Models\Category;
use App\Models\Message;
use App\Models\SubCategory;
use App\Models\Brands;
use App\Models\Advert;
use App\Models\Models;
use App\Models\State;
use App\Models\Followers;
use App\Models\AdvertImage;
use App\Models\CarDetail;
use App\Models\PhoneDetail;
use App\Models\Shipping;
use App\Models\Notification;
use App\Helpers\ContentHelper;
use App\Helpers\FileUploadHelper;
use App\Jobs\PostAdvertJob;
use App\Traits\ManagesImages;
use Illuminate\Validation\ValidationException;

class UserManageAdverts extends Controller
{
    use ManagesImages;

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
        $seller_id = $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $categories = Category::orderBy('category', 'asc')->get();
        $states = State::all();
        $shippings = Shipping::where('status', 'Active')->orderBy('company', 'asc')->get();
        $followers = Followers::with(['user:user_id,id,email,name'])
            ->where('follow', $user_id)
            ->get();
        if ($user->disable_account=='yes') {
            $request->session()->forget('user_id');
            return redirect("login")->with('success', 'Logged Out successfully!');
        }

        if (empty($user->phone)) {
            return redirect('/user/profile-info')->with('error', 'Please Update your Phone number');
        }

        if (empty($user->address) && empty($user->state)) {
            return redirect('/user/profile-address')->with('error', 'Please update your Address and State');
        }
        //dd($followers);

        if ($request->isMethod('POST')) {
            $ad_id = rand(10000, 99999);
            $subcat = (int) $request->input('subcategory');
            $category = (int) $request->input('category');
            
            $rules = [
                'ad_title' => 'required|max:75|string|regex:/^[A-Za-z0-9\s\-\.,;:()\'"!?\[\]_]+$/',
                'category'    => 'required',
                'subcategory' => 'required',
                'brand'       => 'required',
                'state'       => 'required',
                'lga'         => 'required',
                'description' => 'required|max:3500',
                'images.*'    => 'image|mimes:jpeg,png,jpg,gif,webp|max:21000',
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
            } elseif ($category == 11) {
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

            // Check for duplicate post
            $sanitizedTitle = ContentHelper::sanitizeContent($request->input('ad_title'));
            $sanitizedDescription = ContentHelper::sanitizeContent($request->input('description'));
            
            $existingAd = Advert::where('ad_title', $sanitizedTitle)
                ->where('category', $request->input('category'))
                ->where('sub_category', $request->input('subcategory'))
                ->where('brand', $request->input('brand'))
                ->where('description', $sanitizedDescription)
                ->where('user_id', $user_id) // Optional: only check for the same user
                ->first();

            if ($existingAd) {
                return back()->withErrors(['duplicate' => 'A post with the same title, category, sub-category, brand, and description already exists.'])->withInput();
            }

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
                'ad_title'         => $sanitizedTitle,
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
                'description'      => $sanitizedDescription,
                'keyword'          => $keywords,
                'meta_description' => $metaDescription,
                'featured'         => "No",
                'ad_id'            => $ad_id,
                'shipment'         => $request->input('shipment'),
                'show_contact'     => $request->input('show_contact'),
                'quantity'         => $request->input('quantity') ?? 1,
                'views'            => "0",
                'ad_status'        => "active",
                'user_id'          => $user_id,
                'ad_image'         => "",
            ]);

            $advert->shippings()->sync($request->input('shipping', []));


            if ($request->hasFile('images')) {
            try {
                $result = $this->handleImageUploads($request, $advert);
                
                // Notify user if some images were rejected
                if ($result['rejected'] > 0) {
                    session()->flash('warning', 
                        "{$result['rejected']} image(s) were rejected due to watermarks. " .
                        "{$result['accepted']} image(s) uploaded successfully."
                    );
                } else {
                    session()->flash('success', 
                        "{$result['accepted']} image(s) uploaded successfully."
                    );
                }
                
            } catch (ValidationException $e) {
                // All images had watermarks
                return back()
                    ->withErrors($e->errors())
                    ->withInput();
            }
            
        } elseif ($request->input('category') == 3) {
            // Add default image for jobs category
            $advert->addDefaultImage('jobs.png');
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


            $user_id = $request->session()->get('user_id');
            $seller  = User::where('user_id', $user_id)->first();

            // Dispatch job
            PostAdvertJob::dispatch(
                $advert,
                $seller,
                'New Ad',
                $seller->name . ' has placed the ad "' . $advert->ad_title . '"',
            );


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
        $oldPrice = $advert->getOriginal('price');

        $rules = [
            'ad_title' => 'required|max:75',
            'category'    => 'required',
            'subcategory' => 'required',
            'brand'       => 'required',
            'state'       => 'required',
            'lga'         => 'required',
            'description' => 'required|max:3500',
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
            'ad_title'        => ContentHelper::sanitizeContent($request->input('ad_title')),
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

        $messages = [];


        // Handle deleted images first
if ($request->has('deleted_images') && !empty($request->input('deleted_images'))) {
    $deletedImages = $request->input('deleted_images');

    // Ensure it's an array
    if (!is_array($deletedImages)) {
        $deletedImages = explode(',', $deletedImages);
    }

    $deletedImages = array_filter(array_map('intval', $deletedImages));

    if (!empty($deletedImages)) {
        \Log::info('Attempting to delete images:', $deletedImages);

        // Step 1: Try normal deletion
        $deleteResults = $this->getImageService()->deleteMultipleImages(
            $advert,
            $deletedImages,
            'images',
            true // Reorder after deletion
        );

        $deletedCount = $deleteResults['deleted'] ?? 0;
        if ($deletedCount > 0) {
            $messages[] = "{$deletedCount} image(s) deleted";
        }

        // Step 2: Force delete ALL requested IDs to guarantee DB + files are gone
        $forceDeletedCount = 0;
        foreach ($deletedImages as $mediaId) {
            if ($this->getImageService()->forceDeleteMedia($mediaId)) {
                $forceDeletedCount++;
            }
        }

        if ($forceDeletedCount > 0) {
            $messages[] = "{$forceDeletedCount} image(s) force deleted (cleanup)";
        }

        // Log any errors from step 1
        if (!empty($deleteResults['errors'])) {
            foreach ($deleteResults['errors'] as $error) {
                \Log::warning("Image deletion error: " . $error);
            }
        }
    }
}


        // Upload new images BEFORE reordering existing ones
        $newImagesUploaded = 0;
        if ($request->hasFile('images')) {
            // Debug: Check what files we're receiving
            \Log::info('Files received for upload:', [
                'count' => count($request->file('images')),
                'files' => array_map(function($file) {
                    return $file ? $file->getClientOriginalName() : 'null';
                }, $request->file('images'))
            ]);

            // Get current image count to set proper positions for new images
            $currentImageCount = $advert->getMedia('images')->count();

            $uploadedMedia = $this->handleImageUploads($request, $advert, 'images', false);
            $newImagesUploaded = count($uploadedMedia);

            if ($newImagesUploaded > 0) {
                $messages[] = "{$newImagesUploaded} new image(s) uploaded";

                // Set positions for new images starting after existing ones
                foreach ($uploadedMedia as $index => $media) {
                    $media->order_column = $currentImageCount + $index + 1;
                    $media->setCustomProperty('position', $currentImageCount + $index + 1);
                    $media->save();
                }
            } else {
                \Log::warning('No images were uploaded despite files being present');
            }
        }

        // Handle image reordering AFTER uploads
        if ($request->filled('existing_image_order')) {
            $orderedIds = explode(',', $request->input('existing_image_order'));
            $orderedIds = array_filter(array_map('intval', $orderedIds));

            if (!empty($orderedIds)) {
                // Get all current media IDs (including newly uploaded ones)
                $allCurrentMediaIds = $advert->getMedia('images')->pluck('id')->toArray();

                // Add any new media IDs that aren't in the existing order
                $newMediaIds = array_diff($allCurrentMediaIds, $orderedIds);
                $finalOrder = array_merge($orderedIds, $newMediaIds);

                $reordered = $this->getImageService()->reorderImages($advert, $finalOrder, 'images');
                if ($reordered) {
                    $messages[] = "Images reordered";
                }
            }
        } else if ($newImagesUploaded > 0) {
            // If no specific order given but new images uploaded, reorder all to ensure consistent numbering
            $allMediaIds = $advert->getMedia('images')->sortBy('order_column')->pluck('id')->toArray();
            $this->getImageService()->reorderImages($advert, $allMediaIds, 'images');
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

        $user_id = $request->session()->get('user_id');
        $seller  = User::where('user_id', $user_id)->first();


        if ($advert->price != $oldPrice) {
            PostAdvertJob::dispatch(
                $advert,
                $seller,
                'Price Update',
                $seller->name . ' updated the price of ' . $advert->ad_title
            );
        }

        $message = 'Advert updated successfully';
        if (!empty($messages)) {
            $message .= ': ' . implode(', ', $messages);
        }
        return redirect('/user/my-ads')->with('success', $message);
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

            $advert = Advert::find($id);
            if (!$advert) {
                \DB::rollBack();
                return redirect()->back()->with('error', 'Advert not found');
            }

            // Delete the advert - the model event will handle image deletion
            // based on category (Job category preserves default images)
            $advert->delete();

            \DB::commit();
            return redirect()->back()->with('success', 'Advert deleted successfully');

        } catch (\Exception $e) {
            \DB::rollBack();

            \Log::error('Failed to delete advert', [
                'advert_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Failed to delete advert');
        }
    }



}
