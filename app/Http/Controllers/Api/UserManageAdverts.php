<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Advert;
use App\Models\AdvertImage;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Brands;
use App\Models\Models;
use App\Models\State;
use App\Models\Shipping;
use App\Models\CarDetail;
use App\Models\PhoneDetail;
use App\Models\AdvertBoost;
use App\Helpers\ContentHelper;
use App\Helpers\FileUploadHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * @group Advert Management
 *
 * APIs for creating, updating, and managing user adverts
 */
class UserManageAdverts extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/adverts/categories/{categoryId}/subcategories",
     *     summary="Get subcategories by category",
     *     tags={"Advert Management"},
     *     @OA\Parameter(
     *         name="categoryId",
     *         in="path",
     *         required=true,
     *         description="Category ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subcategories list",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/SubCategory"))
     *         )
     *     )
     * )
     */
    public function fetchSubcategories($categoryId)
    {
        $subcategories = SubCategory::where('cat_id', $categoryId)->get();

        return response()->json([
            'success' => true,
            'data' => $subcategories
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/adverts/subcategories/{subcategoryId}/brands",
     *     summary="Get brands by subcategory",
     *     tags={"Advert Management"},
     *     @OA\Parameter(
     *         name="subcategoryId",
     *         in="path",
     *         required=true,
     *         description="Subcategory ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Brands list",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Brand"))
     *         )
     *     )
     * )
     */
    public function fetchBrands($subcategoryId)
    {
        $brands = Brands::where('subcat_id', $subcategoryId)->get();

        return response()->json([
            'success' => true,
            'data' => $brands
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/adverts/brands/{brandId}/models",
     *     summary="Get models by brand",
     *     tags={"Advert Management"},
     *     @OA\Parameter(
     *         name="brandId",
     *         in="path",
     *         required=true,
     *         description="Brand ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Models list",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Model"))
     *         )
     *     )
     * )
     */
    public function fetchModels($brandId)
    {
        $models = Models::where('brand_id', $brandId)->get();

        return response()->json([
            'success' => true,
            'data' => $models
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/adverts/create/data",
     *     summary="Get data needed for creating adverts",
     *     tags={"Advert Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Create advert data",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="categories", type="array", @OA\Items(ref="#/components/schemas/Category")),
     *                 @OA\Property(property="states", type="array", @OA\Items(ref="#/components/schemas/State")),
     *                 @OA\Property(property="shippings", type="array", @OA\Items(ref="#/components/schemas/Shipping"))
     *             )
     *         )
     *     )
     * )
     */
    public function getCreateData()
    {
        $categories = Category::orderBy('category', 'asc')->get();
        $states = State::all();
        $shippings = Shipping::where('status', 'Active')->orderBy('company', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories,
                'states' => $states,
                'shippings' => $shippings
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/adverts",
     *     summary="Create a new advert",
     *     tags={"Advert Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"ad_title", "category", "subcategory", "brand", "state", "lga", "description"},
     *                 @OA\Property(property="ad_title", type="string", maxLength=75),
     *                 @OA\Property(property="ad_type", type="string"),
     *                 @OA\Property(property="category", type="integer"),
     *                 @OA\Property(property="subcategory", type="integer"),
     *                 @OA\Property(property="brand", type="integer"),
     *                 @OA\Property(property="price", type="number"),
     *                 @OA\Property(property="contact_price", type="string"),
     *                 @OA\Property(property="salary", type="number"),
     *                 @OA\Property(property="expected_salary", type="number"),
     *                 @OA\Property(property="item_condition", type="string"),
     *                 @OA\Property(property="price_type", type="string"),
     *                 @OA\Property(property="buy_direct", type="string"),
     *                 @OA\Property(property="state", type="string"),
     *                 @OA\Property(property="lga", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="shipment", type="string"),
     *                 @OA\Property(property="show_contact", type="string"),
     *                 @OA\Property(property="quantity", type="integer"),
     *                 @OA\Property(property="shipping", type="array", @OA\Items(type="integer")),
     *                 @OA\Property(property="images[]", type="array", @OA\Items(type="string", format="binary")),
     *                 @OA\Property(property="image_order", type="string"),
     *                 @OA\Property(property="promotion", type="string"),
     *                 // Car specific fields
     *                 @OA\Property(property="model", type="integer"),
     *                 @OA\Property(property="registration", type="string"),
     *                 @OA\Property(property="mileage", type="number"),
     *                 @OA\Property(property="condition", type="string"),
     *                 @OA\Property(property="fuel", type="string"),
     *                 @OA\Property(property="transmission", type="string"),
     *                 @OA\Property(property="vehicle_type", type="string"),
     *                 @OA\Property(property="doors", type="integer"),
     *                 @OA\Property(property="exterior_color", type="string"),
     *                 @OA\Property(property="material_interior", type="string"),
     *                 @OA\Property(property="exterior_equipment", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="interior", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="security", type="array", @OA\Items(type="string")),
     *                 // Phone specific fields
     *                 @OA\Property(property="phone_color", type="string"),
     *                 @OA\Property(property="phone_condition", type="string"),
     *                 @OA\Property(property="device", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Advert created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/Advert")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function createAdvert(Request $request)
    {
        $user = auth()->user();
        $subcat = (int) $request->input('subcategory');
        $category = (int) $request->input('category');

        $rules = [
            'ad_title' => 'required|max:75',
            'category' => 'required',
            'subcategory' => 'required',
            'brand' => 'required',
            'state' => 'required',
            'lga' => 'required',
            'description' => 'required',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:21000',
        ];

        if ($category != 3) {
            $rules['images'] = 'required|array';
        }

        // Category-specific rules
        if ($category == 3) {
            $rules['salary'] = 'required';
        } elseif ($category == 18) {
            $rules['expected_salary'] = 'required';
        } elseif ($category == 11) {
            $rules['price'] = 'nullable|numeric';
        } else {
            $rules['price'] = 'required|numeric';
            $rules['price_type'] = 'required';
        }

        // Subcategory-specific rules
        switch ($subcat) {
            case 2: // Cars
                $rules += [
                    'model' => 'required',
                    'registration' => 'required',
                    'mileage' => 'required|numeric',
                    'condition' => 'required',
                    'fuel' => 'required',
                    'transmission' => 'required',
                    'vehicle_type' => 'required',
                    'doors' => 'required',
                ];
                break;

            case 6: // Phones
                $rules += [
                    'phone_color' => 'required',
                    'phone_condition' => 'required',
                    'device' => 'required',
                ];
                break;
        }

        // Item condition rule
        if (!in_array($category, [3, 11, 18]) && !in_array($subcat, [2, 6])) {
            $rules['item_condition'] = 'required';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->shipment === 'Ship' && empty($request->input('shipping'))) {
            return response()->json([
                'success' => false,
                'errors' => ['shipping' => ['Please select at least one shipping method.']]
            ], 422);
        }

        try {
            DB::beginTransaction();

            $adId = rand(10000, 99999);
            $metaDescription = Str::limit(strip_tags($request->input('description')), 150, '');
            $rawWords = explode(' ', Str::slug($request->input('ad_title') . ' ' . $request->input('description'), ' '));
            $filteredWords = array_filter($rawWords, function ($word) {
                return strlen($word) > 3;
            });
            $uniqueWords = array_unique($filteredWords);
            $keywords = implode(', ', array_slice($uniqueWords, 0, 10));

            $advert = Advert::create([
                'ad_title' => $request->input('ad_title'),
                'ad_type' => $request->input('ad_type'),
                'category' => $request->input('category'),
                'sub_category' => $request->input('subcategory'),
                'brand' => $request->input('brand'),
                'price' => $request->input('price'),
                'contact_price' => $request->input('contact_price'),
                'salary' => $request->input('salary'),
                'expected_salary' => $request->input('expected_salary'),
                'item_condition' => $request->input('item_condition'),
                'price_type' => $request->input('price_type'),
                'buy_direct' => $request->input('buy_direct'),
                'state' => $request->input('state'),
                'lga' => $request->input('lga'),
                'state_slug' => Str::slug($request->input('lga')),
                'description' => ContentHelper::sanitizeContent($request->input('description')),
                'keyword' => $keywords,
                'meta_description' => $metaDescription,
                'featured' => "No",
                'ad_id' => $adId,
                'shipment' => $request->input('shipment'),
                'show_contact' => $request->input('show_contact'),
                'quantity' => $request->input('quantity') ?? 1,
                'views' => "0",
                'ad_status' => "1",
                'user_id' => $user->user_id,
                'ad_image' => "",
            ]);

            $advert->shippings()->sync($request->input('shipping', []));

            // Handle uploaded images
            if ($request->hasFile('images')) {
                $images = $request->file('images');
                $order = explode(',', $request->input('image_order', ''));

                foreach ($order as $position => $index) {
                    if (!isset($images[$index]) || !$images[$index]->isValid()) continue;

                    $uploadedFileName = FileUploadHelper::upload($images[$index], 'images');

                    $advert->images()->create([
                        'image' => $uploadedFileName,
                        'position' => $position + 1,
                    ]);
                }
            } elseif ($category == 3) {
                // Save default image for jobs
                $advert->images()->create([
                    'image' => 'jobs.png',
                    'position' => 1,
                ]);
            }

            // Store car-specific info
            if ($subcat === 2) {
                $car = new CarDetail([
                    'car_id' => rand(10000, 99999),
                    'cat_id' => $request->input('category'),
                    'brand_id' => $request->input('brand'),
                    'model' => $request->input('model'),
                    'mileage' => $request->input('mileage'),
                    'condition' => $request->input('condition'),
                    'registration' => $request->input('registration'),
                    'fuel' => $request->input('fuel'),
                    'transmission' => $request->input('transmission'),
                    'vehicle_type' => $request->input('vehicle_type'),
                    'doors' => $request->input('doors'),
                    'exterior_color' => $request->input('exterior_color'),
                    'material_interior' => $request->input('material_interior'),
                    'exterior_equipment' => json_encode($request->input('exterior_equipment', [])),
                    'interior' => json_encode($request->input('interior', [])),
                    'security' => json_encode($request->input('security', [])),
                ]);
                $car->advert()->associate($advert);
                $car->save();
            }

            // Store phone-specific info
            if ($subcat === 6) {
                $phone = new PhoneDetail([
                    'phone_id' => rand(10000, 99999),
                    'cat_id' => $request->input('category'),
                    'brand_id' => $request->input('brand'),
                    'model' => $request->input('model'),
                    'color' => $request->input('phone_color'),
                    'device' => $request->input('device'),
                    'condition' => $request->input('phone_condition'),
                ]);
                $phone->advert()->associate($advert);
                $phone->save();
            }

            DB::commit();

            $advert->load(['images', 'car', 'phone', 'shippings']);

            return response()->json([
                'success' => true,
                'message' => 'Advert created successfully',
                'data' => $advert
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Advert creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create advert',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/adverts/{advertId}/edit",
     *     summary="Get advert data for editing",
     *     tags={"Advert Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="advertId",
     *         in="path",
     *         required=true,
     *         description="Advert ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Advert data",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="advert", ref="#/components/schemas/Advert"),
     *                 @OA\Property(property="subcategories", type="array", @OA\Items(ref="#/components/schemas/SubCategory")),
     *                 @OA\Property(property="brands", type="array", @OA\Items(ref="#/components/schemas/Brand")),
     *                 @OA\Property(property="models", type="array", @OA\Items(ref="#/components/schemas/Model"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Advert not found"
     *     )
     * )
     */
    public function getAdvertForEdit($advertId)
    {
        $user = auth()->user();

        $advert = Advert::with(['images', 'car', 'phone', 'shippings'])
            ->where('id', $advertId)
            ->where('user_id', $user->user_id)
            ->first();

        if (!$advert) {
            return response()->json([
                'success' => false,
                'message' => 'Advert not found'
            ], 404);
        }

        $subcategories = SubCategory::where('cat_id', $advert->category)->get();
        $brands = Brands::where('subcat_id', $advert->sub_category)->get();
        $models = Models::where('brand_id', $advert->brand)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'advert' => $advert,
                'subcategories' => $subcategories,
                'brands' => $brands,
                'models' => $models
            ]
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/adverts/{advertId}",
     *     summary="Update an advert",
     *     tags={"Advert Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="advertId",
     *         in="path",
     *         required=true,
     *         description="Advert ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"ad_title", "category", "subcategory", "brand", "state", "lga", "description"},
     *                 @OA\Property(property="ad_title", type="string", maxLength=75),
     *                 @OA\Property(property="ad_type", type="string"),
     *                 @OA\Property(property="category", type="integer"),
     *                 @OA\Property(property="subcategory", type="integer"),
     *                 @OA\Property(property="brand", type="integer"),
     *                 @OA\Property(property="price", type="number"),
     *                 @OA\Property(property="contact_price", type="string"),
     *                 @OA\Property(property="salary", type="number"),
     *                 @OA\Property(property="expected_salary", type="number"),
     *                 @OA\Property(property="item_condition", type="string"),
     *                 @OA\Property(property="price_type", type="string"),
     *                 @OA\Property(property="buy_direct", type="string"),
     *                 @OA\Property(property="state", type="string"),
     *                 @OA\Property(property="lga", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="shipment", type="string"),
     *                 @OA\Property(property="show_contact", type="string"),
     *                 @OA\Property(property="quantity", type="integer"),
     *                 @OA\Property(property="shipping", type="array", @OA\Items(type="integer")),
     *                 @OA\Property(property="images[]", type="array", @OA\Items(type="string", format="binary")),
     *                 @OA\Property(property="image_order", type="string"),
     *                 @OA\Property(property="deleted_images", type="array", @OA\Items(type="integer")),
     *                 @OA\Property(property="existing_image_order", type="string"),
     *                 // Car specific fields
     *                 @OA\Property(property="model", type="integer"),
     *                 @OA\Property(property="registration", type="string"),
     *                 @OA\Property(property="mileage", type="number"),
     *                 @OA\Property(property="condition", type="string"),
     *                 @OA\Property(property="fuel", type="string"),
     *                 @OA\Property(property="transmission", type="string"),
     *                 @OA\Property(property="vehicle_type", type="string"),
     *                 @OA\Property(property="doors", type="integer"),
     *                 @OA\Property(property="exterior_color", type="string"),
     *                 @OA\Property(property="material_interior", type="string"),
     *                 @OA\Property(property="exterior_equipment", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="interior", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="security", type="array", @OA\Items(type="string")),
     *                 // Phone specific fields
     *                 @OA\Property(property="phone_color", type="string"),
     *                 @OA\Property(property="phone_condition", type="string"),
     *                 @OA\Property(property="device", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Advert updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/Advert")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function updateAdvert(Request $request, $advertId)
    {
        $user = auth()->user();
        $subcat = (int) $request->input('subcategory');
        $category = (int) $request->input('category');

        $advert = Advert::where('id', $advertId)
            ->where('user_id', $user->user_id)
            ->first();

        if (!$advert) {
            return response()->json([
                'success' => false,
                'message' => 'Advert not found'
            ], 404);
        }

        $rules = [
            'ad_title' => 'required|max:75',
            'category' => 'required',
            'subcategory' => 'required',
            'brand' => 'required',
            'state' => 'required',
            'lga' => 'required',
            'description' => 'required',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:21000',
        ];

        // Category-specific rules
        if ($category == 3) {
            $rules['salary'] = 'required';
        } elseif ($category == 18) {
            $rules['expected_salary'] = 'required';
        } elseif ($category == 11) {
            $rules['price'] = 'nullable|numeric';
        } else {
            $rules['price'] = 'required|numeric';
            $rules['price_type'] = 'required';
        }

        // Subcategory-specific rules
        switch ($subcat) {
            case 2: // Cars
                $rules += [
                    'model' => 'required',
                    'registration' => 'required',
                    'mileage' => 'required|numeric',
                    'condition' => 'required',
                    'fuel' => 'required',
                    'transmission' => 'required',
                    'vehicle_type' => 'required',
                    'doors' => 'required',
                ];
                break;

            case 6: // Phones
                $rules += [
                    'phone_color' => 'required',
                    'phone_condition' => 'required',
                    'device' => 'required',
                ];
                break;
        }

        // Item condition rule
        if (!in_array($category, [3, 11, 18]) && !in_array($subcat, [2, 6])) {
            $rules['item_condition'] = 'required';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $metaDescription = Str::limit(strip_tags($request->input('description')), 150, '');
            $rawWords = explode(' ', Str::slug($request->input('ad_title') . ' ' . $request->input('description'), ' '));
            $filteredWords = array_filter($rawWords, function ($word) {
                return strlen($word) > 3;
            });
            $uniqueWords = array_unique($filteredWords);
            $keywords = implode(', ', array_slice($uniqueWords, 0, 10));

            // Update main advert
            $advert->update([
                'ad_title' => $request->input('ad_title'),
                'ad_type' => $request->input('ad_type'),
                'category' => $request->input('category'),
                'sub_category' => $request->input('subcategory'),
                'brand' => $request->input('brand'),
                'price' => $request->input('price'),
                'price_type' => $request->input('price_type'),
                'contact_price' => $request->input('contact_price'),
                'salary' => $request->input('salary'),
                'expected_salary' => $request->input('expected_salary'),
                'item_condition' => $request->input('item_condition'),
                'buy_direct' => $request->input('buy_direct'),
                'state' => $request->input('state'),
                'lga' => $request->input('lga'),
                'state_slug' => Str::slug($request->input('lga')),
                'description' => ContentHelper::sanitizeContent($request->input('description')),
                'featured' => $advert->featured,
                'keyword' => $keywords,
                'meta_description' => $metaDescription,
                'shipment' => $request->input('shipment'),
                'show_contact' => $request->input('show_contact'),
                'quantity' => $request->input('quantity') ?? 1,
                'ad_image' => "",
            ]);

            $advert->shippings()->sync($request->input('shipping', []));

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

            // Upload new images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    if ($image->isValid()) {
                        $uploadedFileName = FileUploadHelper::upload($image, 'images');
                        $advert->images()->create([
                            'image' => $uploadedFileName,
                            'position' => $newPositionStart + $index,
                        ]);
                    }
                }
            }

            // Update Car details
            if ($subcat === 2) {
                if ($advert->car) {
                    $advert->car->update([
                        'cat_id' => $request->input('category'),
                        'brand_id' => $request->input('brand'),
                        'model' => $request->input('model'),
                        'mileage' => $request->input('mileage'),
                        'condition' => $request->input('condition'),
                        'registration' => $request->input('registration'),
                        'fuel' => $request->input('fuel'),
                        'transmission' => $request->input('transmission'),
                        'vehicle_type' => $request->input('vehicle_type'),
                        'doors' => $request->input('doors'),
                        'exterior_color' => $request->input('exterior_color'),
                        'material_interior' => $request->input('material_interior'),
                        'exterior_equipment' => json_encode($request->input('exterior_equipment', [])),
                        'interior' => json_encode($request->input('interior', [])),
                        'security' => json_encode($request->input('security', [])),
                    ]);
                }
            }

            // Update Phone details
            if ($subcat === 6) {
                if ($advert->phone) {
                    $advert->phone->update([
                        'cat_id' => $request->input('category'),
                        'brand_id' => $request->input('brand'),
                        'model' => $request->input('model'),
                        'color' => $request->input('phone_color'),
                        'device' => $request->input('device'),
                        'condition' => $request->input('phone_condition'),
                    ]);
                }
            }

            DB::commit();

            $advert->load(['images', 'car', 'phone', 'shippings']);

            return response()->json([
                'success' => true,
                'message' => 'Advert updated successfully',
                'data' => $advert
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Advert update failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update advert',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/adverts/{advertId}",
     *     summary="Delete an advert",
     *     tags={"Advert Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="advertId",
     *         in="path",
     *         required=true,
     *         description="Advert ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Advert deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Advert not found"
     *     )
     * )
     */
    public function deleteAdvert($advertId)
    {
        $user = auth()->user();

        try {
            DB::beginTransaction();

            $advert = Advert::with('images')->where('id', $advertId)
                ->where('user_id', $user->user_id)
                ->first();

            if (!$advert) {
                return response()->json([
                    'success' => false,
                    'message' => 'Advert not found'
                ], 404);
            }

            // Delete all associated images safely (except for category Job which uses default image)
            if ($advert->category != 3) {
                foreach ($advert->images ?? [] as $image) {
                    if ($image && !empty($image->image)) {
                        try {
                            FileUploadHelper::delete('images', $image->image);
                            $image->delete();
                        } catch (\Exception $imgEx) {
                            throw new \Exception("Unable to delete image file");
                        }
                    }
                }
            }

            // Delete the advert itself
            $advert->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Advert deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Advert deletion failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete advert',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/adverts/{advertId}/boost",
     *     summary="Get boost information for an advert",
     *     tags={"Advert Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="advertId",
     *         in="path",
     *         required=true,
     *         description="Advert ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Boost information",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="advert", ref="#/components/schemas/Advert"),
     *                 @OA\Property(property="price", type="number")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Advert not found"
     *     )
     * )
     */
    public function getBoostInfo($advertId)
    {
        $user = auth()->user();

        $advert = Advert::with(['images', 'car', 'phone'])
            ->where('id', $advertId)
            ->where('user_id', $user->user_id)
            ->first();

        if (!$advert) {
            return response()->json([
                'success' => false,
                'message' => 'Advert not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'advert' => $advert,
                'price' => 500 // Fixed boost price
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/adverts/{advertId}/boosted",
     *     summary="Get boosted advert information",
     *     tags={"Advert Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="advertId",
     *         in="path",
     *         required=true,
     *         description="Advert ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Boosted advert information",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="advert", ref="#/components/schemas/Advert"),
     *                 @OA\Property(property="price", type="number")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Advert not found"
     *     )
     * )
     */
    public function getBoostedAdvert($advertId)
    {
        $user = auth()->user();

        $advert = Advert::with(['images', 'car', 'phone', 'boost'])
            ->where('id', $advertId)
            ->where('user_id', $user->user_id)
            ->first();

        if (!$advert) {
            return response()->json([
                'success' => false,
                'message' => 'Advert not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'advert' => $advert,
                'price' => 500
            ]
        ]);
    }
}
