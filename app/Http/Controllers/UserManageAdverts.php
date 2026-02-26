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
use App\Models\AdvertBoost;
use App\Models\BoostType;
use App\Models\BoostDuration;
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
use App\Services\AdvertValidationService;
use App\Services\ImageQualityService;
use App\Traits\ManagesImages;
use App\Traits\HasUserSession;

class UserManageAdverts extends Controller
{
    use HasUserSession;
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

        // Check if user session exists
        if (!$user_id) {
            return redirect('/login')->with('error', 'Please login to post an ad.');
        }

        $user = User::where('user_id', $user_id)->first();

        // Check if user exists
        if (!$user) {
            $request->session()->forget('user_id');
            return redirect('/login')->with('error', 'User not found. Please login again.');
        }

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

        //dd($followers);

        if ($request->isMethod('POST')) {
            // ✅ HANDLE IMAGE UPLOADS EARLY - Store temporarily in case of validation errors
            $tempImages = [];
            if ($request->hasFile('images')) {
                $tempImages = $this->storeTemporaryImages($request->file('images'));
            }

            // ✅ TIER 1: IMAGE QUALITY VALIDATION
            if ($request->hasFile('images')) {
                $imageQualityService = new ImageQualityService();
                $imageQualityErrors = [];
                $imageQualityWarnings = [];
                $totalQualityScore = 0;
                $imageCount = 0;

                foreach ($request->file('images') as $image) {
                    $result = $imageQualityService->validateImage($image);
                    $imageCount++;

                    // Collect errors
                    if (!$result['valid']) {
                        $imageQualityErrors = array_merge($imageQualityErrors, $result['errors']);
                    }

                    // Collect warnings (but don't block upload)
                    if (!empty($result['warnings'])) {
                        $imageQualityWarnings = array_merge($imageQualityWarnings, $result['warnings']);
                    }

                    $totalQualityScore += $result['score'];
                }

                // If there are quality errors, reject the upload
                if (!empty($imageQualityErrors)) {
                    if (!empty($tempImages)) {
                        $request->session()->flash('temp_images', $tempImages);
                    }

                    // Get recommendations
                    $recommendations = $imageQualityService->getRecommendations([
                        'valid' => false,
                        'details' => [
                            'width' => 0,
                            'sharpness' => 0,
                            'brightness' => 0
                        ]
                    ]);

                    return back()->withErrors([
                        'images' => array_merge(
                            ['Image quality validation failed:'],
                            array_slice($imageQualityErrors, 0, 3), // Show first 3 errors
                            [''],
                            ['Recommendations:'],
                            $recommendations
                        )
                    ])->withInput();
                }

                // Log quality metrics
                $avgScore = $imageCount > 0 ? round($totalQualityScore / $imageCount) : 0;
                \Log::info('Image quality validation passed', [
                    'image_count' => $imageCount,
                    'average_score' => $avgScore,
                    'quality_rating' => $imageQualityService->getQualityRating($avgScore),
                    'warnings_count' => count($imageQualityWarnings)
                ]);
            }

            // ✅ EARLY VALIDATION: Validate only CRITICAL required fields
            // Only validate fields that are ALWAYS required, not conditional fields
            // This prevents 500 errors and avoids validating hidden fields
            try {
                $request->validate([
                    'ad_title' => 'required|max:75',
                    'description' => 'required|max:3500',
                ], [
                    'ad_title.required' => 'Ad title is required.',
                    'description.required' => 'Description is required.',
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                // Validation failed - flash temp images to session
                if (!empty($tempImages)) {
                    $request->session()->flash('temp_images', $tempImages);
                }
                throw $e; // Re-throw to show validation errors
            }

            $ad_id = rand(10000, 99999);
            $subcat = (int) $request->input('subcategory');
            $category = (int) $request->input('category');
            $description = $this->removeEmojis($request->input('description'));
            $request->merge(['description' => $description]);

            // ✅ Check if temp images exist (from previous validation error)
            $hasTempImages = !empty($request->input('temp_image_paths', []));

            // Use dynamic validation service based on Category UI Config
            $validationService = new AdvertValidationService();
            $rules = $validationService->getRules($category, $subcat, false, $hasTempImages);

            // Custom validation messages
            $messages = [
                'images.required' => 'Please upload at least 3 images.',
                'images.min'      => 'Please upload at least 3 images.',
                'images.*.image' => 'All files must be images.',
                'images.*.mimes' => 'Images must be jpeg, png, jpg, or gif format.',
                'images.*.max' => 'Each image must not exceed 20MB.',
            ];

            try {
                $validatedData = $request->validate($rules, $messages);
            } catch (\Illuminate\Validation\ValidationException $e) {
                // Validation failed - flash temp images to session
                if (!empty($tempImages)) {
                    $request->session()->flash('temp_images', $tempImages);
                }
                throw $e; // Re-throw to show validation errors
            }

            // Validate shipping requirements
            $shippingError = $validationService->validateShipping($request);
            if ($shippingError) {
                // Flash temp images to session before redirecting
                if (!empty($tempImages)) {
                    $request->session()->flash('temp_images', $tempImages);
                }
                return back()->withErrors($shippingError)->withInput();
            }

            $adTitle = ContentHelper::sanitizeTitle($request->ad_title);
            $adDescription = ContentHelper::sanitizeDescription($request->input('description'));
            $metaDescription = Str::limit($adDescription, 150, '');
            $rawWords = explode(' ', Str::slug($adTitle . ' ' . $metaDescription, ' '));
            $filteredWords = array_filter($rawWords, function ($word) {
            return strlen($word) > 3;
            });

            $uniqueWords = array_unique($filteredWords);

            $keywords = implode(', ', array_slice($uniqueWords, 0, 10));


            $lga     = $request->lga;

            // Duplicate check: same user + same title + same category/subcategory
            // Only blocks active ads OR ads posted within the last 24 hours (anti-abuse window).
            // This allows legitimate re-listing after a sale/deletion without permanently blocking sellers.
            $exists = Advert::where('user_id', $user_id)
                ->where('ad_title', $adTitle)
                ->where('category', $request->input('category'))
                ->where('sub_category', $request->input('subcategory'))
                ->where(function ($q) {
                    $q->where('ad_status', 'active')
                      ->orWhere('created_at', '>=', now()->subHours(24));
                })
                ->exists();

            if ($exists) {
                return redirect('/user/my-ads')->with('error', "You already have an active listing with this title in the same category. Please edit the existing ad or wait before re-listing.");
            }

            $advert = Advert::create([
                'ad_title'         => $adTitle,
                'ad_type'          => $request->input('ad_type', 'Private'), // ✅ Default to 'Private' if not provided
                'category'         => $request->input('category'),
                'sub_category'     => $request->input('subcategory'),
                'brand'            => $request->input('brand'),
                'price'            => $request->input('price'),
                'contact_price'    => $request->input('contact_price'),
                'salary'           => $request->input('salary'),
                'expected_salary'  => $request->input('expected_salary'),
                'item_condition'   => $request->input('item_condition'),
                'price_type'       => $request->input('price_type'),
                'buy_direct'       => $request->input('buy_direct', 'No'), // ✅ Default to 'No' if not provided
                'state'            => $request->input('state'),
                'lga'              => $request->input('lga'),
                'state_slug'       => Str::slug($request->input('lga')),
                'description'      => $adDescription,
                'keyword'          => $keywords,
                'meta_description' => $metaDescription,
                'featured'         => "No",
                'ad_id'            => $ad_id,
                'shipment'         => $request->input('shipment', 'Pickup'), // ✅ Default to 'Pickup' if not provided
                'show_contact'     => $request->input('show_contact', 'No'), // ✅ Default to 'No' if not provided
                'quantity'         => $request->input('quantity') ?? 1,
                'views'            => "0",
                'ad_status'        => "active",
                'user_id'          => $user_id,
            ]);

            $advert->shippings()->sync($request->input('shipping', []));

            // ✅ Handle image uploads - Priority: temp images > new uploads > default
            $tempImagePaths = $request->input('temp_image_paths', []);
            $hasNewImages = $request->hasFile('images');
            $hasTempImages = !empty($tempImagePaths);

            \Log::info("Image Processing Debug", [
                'temp_paths' => $tempImagePaths,
                'has_new_images' => $hasNewImages,
                'has_temp_images' => $hasTempImages,
            ]);

            if ($hasTempImages || $hasNewImages) {
                $imagesToProcess = [];

                // First, collect temp images
                if ($hasTempImages) {
                    foreach ($tempImagePaths as $tempPath) {
                        \Log::info("Checking temp image", ['path' => $tempPath, 'exists' => \Storage::disk('public')->exists($tempPath)]);
                        if (\Storage::disk('public')->exists($tempPath)) {
                            $imagesToProcess[] = [
                                'type' => 'temp',
                                'path' => $tempPath,
                            ];
                        }
                    }
                }

                // Then, add new uploads
                if ($hasNewImages) {
                    foreach ($request->file('images') as $image) {
                        if ($image && $image->isValid()) {
                            $imagesToProcess[] = [
                                'type' => 'new',
                                'file' => $image,
                            ];
                        }
                    }
                }

                \Log::info("Images to process", ['count' => count($imagesToProcess), 'data' => $imagesToProcess]);

                // Process all images
                $this->processAdvertImages($imagesToProcess, $advert);
            } elseif (in_array($request->input('category'), [3, 18])) {
                // Add default image for jobs/CV categories
                $advert->addDefaultImage('jobs.png');
            }


            // Store car-specific info
            if (in_array($subcat, [2, 21, 23])) {
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

            // Notify followers after response is sent (no queue worker required)
            if ($seller) {
                PostAdvertJob::dispatchAfterResponse(
                    $advert,
                    $seller,
                    'New Ad',
                    $seller->name . ' has placed the ad "' . $advert->ad_title . '"',
                );
            }


            // Handle promotion
            if ($request->has('promotion')) {
                $request->session()->put('promotion', $request->promotion);
                return redirect('/user/post-boost-ad/' . $advert->id);
            }

            return redirect('/user/my-ads')->with('success', 'Your Advert Has successfully been Posted');
        }

        if ($request->isMethod('GET')) {
            // Get active boost types and durations for optional boost during post
            $boostTypes = BoostType::active()->ordered()->get();
            $boostDurations = BoostDuration::active()->ordered()->get();

            return view('dashboard.post-ad', compact('title', 'categories', 'user', 'shippings', 'states', 'boostTypes', 'boostDurations'));
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

        $description = $this->removeEmojis($request->input('description'));
        $request->merge(['description' => $description]);

        // Use dynamic validation service based on Category UI Config
        $validationService = new AdvertValidationService();
        $rules = $validationService->getRules($category, $subcat, true); // true = isUpdate

        $validatedData = $request->validate($rules);
        $adTitle = ContentHelper::sanitizeTitle($request->input('ad_title'));
        $adDescrition = ContentHelper::sanitizeDescription($request->input('description'));
        $metaDescription = Str::limit($adDescrition, 150, '');
        $rawWords = explode(' ', Str::slug($adTitle . ' ' . $adDescrition, ' '));
        $filteredWords = array_filter($rawWords, function ($word) {
        return strlen($word) > 3;
        });

        $uniqueWords = array_unique($filteredWords);

        $keywords = implode(', ', array_slice($uniqueWords, 0, 10));

        //dd($keywords);
        // Update main advert
        $advert->update([
            'ad_title'        => $adTitle,
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
            'description'       => $adDescrition,
            'featured'         => $advert->featured,
            'keyword'         => $keywords,
            'meta_description'=> $metaDescription,
            'shipment'        => $request->input('shipment'),
            'show_contact'    => $request->input('show_contact'),
            'quantity'        => $request->input('quantity') ?? 1,
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
                // Get current image count
                $currentImageCount = $advert->getMedia('images')->count();
                $requestedDeleteCount = count($deletedImages);

                // Validate: must have at least 3 images remaining
                $remainingAfterDelete = $currentImageCount - $requestedDeleteCount;
                if ($remainingAfterDelete < 3) {
                    return redirect()->back()->withErrors([
                        'deleted_images' => 'Cannot delete those images. Adverts must have at least 3 images.'
                    ]);
                } else {
                    // Proceed with deletion
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

        // Validate final image count - ensure at least 3 images exist
        $finalImageCount = $advert->getMedia('images')->count();

        if ($finalImageCount < 3) {
            if (in_array($category, [3, 18])) {
                // Add default image for jobs/CV categories if no images exist
                if ($finalImageCount < 1) {
                    $advert->addDefaultImage('jobs.png');
                    $messages[] = "Default job image added";
                }
            } else {
                // For all other categories, at least 3 images are required
                return redirect()->back()->withErrors([
                    'images' => 'Advert must have at least 3 images. Please upload more images.'
                ])->withInput();
            }
        }

        // Update Car details (upsert: update if exists, create if missing)
        if (in_array($subcat, [2, 21, 23])) {
            $carData = [
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
            ];
            if ($advert->car) {
                $advert->car->update($carData);
            } else {
                $car = new CarDetail(array_merge($carData, ['car_id' => rand(10000, 99999)]));
                $car->advert()->associate($advert);
                $car->save();
            }
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


        // Notify followers of price change after response is sent (no queue worker required)
        if ($advert->price != $oldPrice) {
            PostAdvertJob::dispatchAfterResponse(
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

    private function removeEmojis($text)
    {
        // Return empty string if text is null or empty
        if ($text === null || $text === '') {
            return '';
        }

        // Remove emojis using regex
        return preg_replace('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F1E0}-\x{1F1FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{1F004}\x{1F0CF}\x{1F18E}\x{1F191}-\x{1F19A}\x{1F201}\x{1F21A}\x{1F22F}\x{1F232}-\x{1F236}\x{1F238}-\x{1F23A}\x{1F250}\x{1F251}]/u', '', $text);
    }

    /**
     * Store uploaded images temporarily in case of validation errors
     * This allows images to be retained when form validation fails
     *
     * @param array $images Array of uploaded files
     * @return array Array of temp image data
     */
    private function storeTemporaryImages($images)
    {
        $tempImages = [];
        $tempDir = 'temp/post-ad-images';

        // Create temp directory if it doesn't exist
        if (!\Storage::disk('public')->exists($tempDir)) {
            \Storage::disk('public')->makeDirectory($tempDir);
        }

        foreach ($images as $index => $image) {
            if ($image && $image->isValid()) {
                // Generate unique filename
                $filename = uniqid('temp_') . '_' . time() . '_' . $index . '.' . $image->getClientOriginalExtension();

                // Store in temp directory
                $path = $image->storeAs($tempDir, $filename, 'public');

                if ($path) {
                    $tempImages[] = [
                        'path' => $path,
                        'url' => \Storage::disk('public')->url($path),
                        'original_name' => $image->getClientOriginalName(),
                        'size' => $image->getSize(),
                    ];
                }
            }
        }

        return $tempImages;
    }

    /**
     * Move temporary images to permanent advert images
     *
     * @param array $tempImages Array of temp image data
     * @param Advert $advert The advert model
     * @return void
     */
    /**
     * Process both temp and new images for an advert using Spatie Media Library
     *
     * @param array $images Array of images to process (temp and new)
     * @param Advert $advert The advert model
     * @return void
     */
    private function processAdvertImages($images, $advert)
    {
        \Log::info("processAdvertImages called", [
            'advert_id' => $advert->ad_id,
            'images_count' => count($images)
        ]);

        foreach ($images as $index => $imageData) {
            try {
                $position = $index + 1;
                \Log::info("Processing image {$index}", ['type' => $imageData['type']]);

                if ($imageData['type'] === 'temp') {
                    // Handle temp image - get full path from public disk root
                    $tempPath = $imageData['path'];
                    // ✅ Use Storage disk root, not hardcoded path
                    $fullPath = \Storage::disk('public')->path($tempPath);

                    \Log::info("Temp image processing", [
                        'temp_path' => $tempPath,
                        'full_path' => $fullPath,
                        'file_exists' => file_exists($fullPath)
                    ]);

                    if (file_exists($fullPath)) {
                        \Log::info("Adding temp media to Spatie", ['path' => $fullPath]);

                        // Add media from temp file path using Spatie
                        $media = $advert
                            ->addMedia($fullPath)
                            ->withCustomProperties([
                                'position' => $position,
                                'source' => 'temp'
                            ])
                            ->usingFileName(uniqid() . '.webp')
                            ->toMediaCollection('images');

                        $media->order_column = $position;
                        $media->save();

                        \Log::info("Temp media added successfully", [
                            'media_id' => $media->id,
                            'file_name' => $media->file_name
                        ]);

                        // Delete temp file after adding to media library
                        @unlink($fullPath);
                        \Log::info("Temp file deleted", ['path' => $fullPath]);
                    } else {
                        \Log::warning("Temp file not found", ['path' => $fullPath]);
                    }
                } elseif ($imageData['type'] === 'new') {
                    // Handle new upload using Spatie
                    $file = $imageData['file'];

                    \Log::info("Adding new upload to Spatie", [
                        'original_name' => $file->getClientOriginalName()
                    ]);

                    $media = $advert
                        ->addMedia($file)
                        ->withCustomProperties([
                            'position' => $position,
                            'original_name' => $file->getClientOriginalName()
                        ])
                        ->usingFileName(uniqid() . '.webp')
                        ->toMediaCollection('images');

                    $media->order_column = $position;
                    $media->save();

                    \Log::info("New media added successfully", [
                        'media_id' => $media->id,
                        'file_name' => $media->file_name
                    ]);
                }

            } catch (\Exception $e) {
                \Log::error("Failed to process image {$index} for advert {$advert->ad_id}: " . $e->getMessage(), [
                    'exception' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]);
                continue;
            }
        }

        // ✅ Spatie handles all images - no need to set ad_image column
        // The firstImage() relationship will automatically get the first media
        $mediaCount = $advert->getMedia('images')->count();
        \Log::info("Images processed successfully", [
            'advert_id' => $advert->ad_id,
            'media_count' => $mediaCount
        ]);
    }



}
