<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Advert;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Category;
use App\Models\Message;
use App\Models\SubCategory;
use App\Models\Brands;
use App\Models\Models;
use App\Models\State;
use App\Models\CarDetail;
use App\Models\PhoneDetail;
use App\Models\Shipping;
use App\Helpers\ContentHelper;
use Illuminate\Support\Str;
use App\Traits\ManagesImages;
use Illuminate\Validation\ValidationException;

class ManageAdverts extends Controller
{
    use ManagesImages;

    private function applyAdvertSearch($query, $searchTerm)
    {
        return $query->where(function($q) use ($searchTerm) {
            $q->where('ad_title', 'LIKE', "%{$searchTerm}%")
              ->orWhere('description', 'LIKE', "%{$searchTerm}%")
              ->orWhere('state', 'LIKE', "%{$searchTerm}%")
              ->orWhere('ad_id', 'LIKE', "%{$searchTerm}%")
              ->orWhere('price', 'LIKE', "%{$searchTerm}%")
              ->orWhere('salary', 'LIKE', "%{$searchTerm}%")
              ->orWhere('expected_salary', 'LIKE', "%{$searchTerm}%")
              ->orWhereHas('user', function($userQuery) use ($searchTerm) {
                  $userQuery->where('name', 'LIKE', "%{$searchTerm}%")
                           ->orWhere('email', 'LIKE', "%{$searchTerm}%");
              });
        });
    }


    public function active_adverts(Request $request)
    {
        $title = "Active Adverts | " . config('global.site_name');
        $page_title = "Active Adverts";

        $query = Advert::with(['user', 'firstImage'])
                ->where("ad_status", 'active')
                ->where("sold", "No");

        // Apply search if present
        if ($request->filled('query')) {
            $query = $this->applyAdvertSearch($query, $request->input('query'));
        }

        $adverts = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('backend.advert.adverts', compact('title', 'page_title','adverts'));
    }

    public function disabled_adverts(Request $request)
    {
        $title = "Disabled Adverts | " . config('global.site_name');
        $page_title = "Disabled Adverts";

        $query = Advert::with(['user', 'firstImage'])
                ->where("ad_status", '<>', 'active')
                ->where("sold", "No");

        // Apply search if present
        if ($request->filled('query')) {
            $query = $this->applyAdvertSearch($query, $request->input('query'));
        }

        $adverts = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('backend.advert.adverts', compact('title', 'page_title', 'adverts'));
    }

    // all adverts method
    public function all_adverts(Request $request)
    {
        $title = "All Adverts | " . config('global.site_name');
        $page_title = "All Adverts";

        $query = Advert::with(['user', 'firstImage']);

        // Apply search if present
        if ($request->filled('query')) {
            $query = $this->applyAdvertSearch($query, $request->input('query'));
        }

        $adverts = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('backend.advert.adverts', compact('title', 'page_title', 'adverts'));
    }

    // sold adverts
    public function sold_adverts(Request $request)
    {
        $title = "Sold Adverts | " . config('global.site_name');
        $page_title = "Sold Adverts";

        $query = Advert::with(['user', 'firstImage'])
                ->where("sold", "Yes");

        // Apply search if present
        if ($request->filled('query')) {
            $query = $this->applyAdvertSearch($query, $request->input('query'));
        }

        $adverts = $query->orderBy('created_at', 'desc')->paginate(20);

         return view('backend.advert.sold-adverts', compact('title', 'page_title','adverts'));
    }

    public function edit_advert(Request $request, $id)
    {
        $title = "Edit Adverts | " . config('global.site_name');
        $page_title = "Edit Adverts";
        $categories = Category::orderBy('category','asc')->get();
        $states = State::all();
        $shippings = Shipping::where('status', 'Active')->orderBy('company', 'asc')->get();
        $advert = Advert::with(['user','images', 'car', 'phone'])
            ->where('id', $id)
            ->firstOrFail();
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


        return view('backend.advert.edit-advert', compact(
            'title',
            'page_title',
            'categories',
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

        // Strip HTML tags from Trix-submitted description before length validation
        $plainDescription = strip_tags($request->input('description', ''));
        $request->merge(['description' => $plainDescription]);

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
                'price'      => 'required_unless:contact_price,yes|nullable|numeric',
                'price_type' => 'required_unless:contact_price,yes',
            ];
        }

        // Subcategory-specific rules
        switch ($subcat) {
            case 2:
            case 21:
            case 23:
                $rules += [
                    'model'          => 'required',
                    'registration'   => 'required',
                    'mileage'        => 'required|numeric',
                    'condition'      => 'required',
                    'fuel'           => 'required',
                    'transmission'   => 'required',
                    'vehicle_type'   => 'required',
                    'doors'          => 'required',
                    'exterior_color' => 'required',
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

        // Item condition rule (skip if category is 3 or 18, OR subcat is a car/phone subcat)
        if (!in_array($category, [3, 11, 18]) && !in_array($subcat, [2, 6, 21, 23])) {
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



        $message = 'Advert updated successfully';
        if (!empty($messages)) {
            $message .= ': ' . implode(', ', $messages);
        }

        return redirect('/admin/active-adverts')->with('status', [
            'type' => 'success', 
            'text' => $message
        ]);
    }



    public function advert_status($id, $status)
    {
        DB::table('adverts')
                ->where('id', $id)
                ->update([
                    'ad_status'=> $status,
                    'updated_at' => Carbon::now(),
                ]);

        return redirect()->back()->with('status', ['text'=>'Advert Status Changed','type'=>'success']);
    }

    public function sold_status($id, $status)
    {
        DB::table('adverts')
                ->where('id', $id)
                ->update([
                    'sold'=> $status,
                    'updated_at' => Carbon::now(),
                ]);

        return redirect()->back()->with('status', ['text'=>'Advert Status Changed','type'=>'success']);
    }

    public function redirect_status($id, $status)
    {
        DB::table('adverts')
                ->where('id', $id)
                ->update([
                    'redirect'=> $status,
                    'updated_at' => Carbon::now(),
                ]);

        return redirect()->back()->with('status', ['text'=>'Advert Status Changed','type'=>'success']);
    }



    public function delete_advert($id)
{
    try {
        \DB::beginTransaction();

        $advert = Advert::with('images')->find($id);

        if (!$advert) {
            return redirect()->back()->with('status', [
                'text' => 'Advert not found',
                'type' => 'error'
            ]);
        }

        // Delete all associated images safely
        foreach ($advert->images ?? [] as $image) {
            if ($image && !empty($image->image)) {
                try {
                    // Use your helper to delete the file
                    FileUploadHelper::delete('images', $image->image);

                    // Delete the image record from database
                    $image->delete();

                } catch (\Exception $imgEx) {
                    throw new \Exception("Failed to delete image {$image->image}: " . $imgEx->getMessage());
                }
            }
        }

        // Delete the advert itself
        $advert->delete();

        \DB::commit();

        return redirect()->back()->with('status', [
            'text' => 'Advert and all images deleted successfully',
            'type' => 'success'
        ]);

    } catch (\Exception $e) {
        \DB::rollBack();

        return redirect()->back()->with('status', [
            'text' => 'Error deleting advert: ' . $e->getMessage(),
            'type' => 'error'
        ]);
    }
}




}
