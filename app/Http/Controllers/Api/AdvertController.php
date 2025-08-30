<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReportMail;
use App\Services\FeaturedAdPaginator;

class AdvertController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/adverts",
     *     summary="Get all adverts with pagination",
     *     tags={"Adverts"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Advert")),
     *             @OA\Property(property="first_page_url", type="string"),
     *             @OA\Property(property="from", type="integer"),
     *             @OA\Property(property="last_page", type="integer"),
     *             @OA\Property(property="last_page_url", type="string"),
     *             @OA\Property(property="links", type="array", @OA\Items(ref="#/components/schemas/PaginationLink")),
     *             @OA\Property(property="next_page_url", type="string"),
     *             @OA\Property(property="path", type="string"),
     *             @OA\Property(property="per_page", type="integer"),
     *             @OA\Property(property="prev_page_url", type="string"),
     *             @OA\Property(property="to", type="integer"),
     *             @OA\Property(property="total", type="integer")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 20);

        $listings = Advert::with('firstImage', 'owner')
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
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $listings
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/adverts/{id}",
     *     summary="Get advert details",
     *     tags={"Adverts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Advert ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Advert details",
     *         @OA\JsonContent(ref="#/components/schemas/AdvertDetail")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Advert not found"
     *     )
     * )
     */
    public function show($id)
    {
        $ad = Advert::with(['images', 'owner'])
            ->where('id', $id)
            ->first();

        if (!$ad) {
            return response()->json([
                'success' => false,
                'message' => 'Advert not found'
            ], 404);
        }

        // Process images
        if ($ad->images) {
            foreach ($ad->images as $img) {
                $path = public_path('uploads/images/' . $img->image);
                if (File::exists($path)) {
                    [$width, $height] = getimagesize($path);
                    $img->is_portrait = $height > $width;
                } else {
                    $img->is_portrait = false;
                }
            }
        }

        $cat_id = $ad->category;
        $brand_id = $ad->brand;
        $ad_owner = $ad->user_id;
        $subcat_id = $ad->sub_category;

        $data = [
            'ad' => $ad,
            'ad_owner' => User::where('user_id', $ad_owner)->first(),
            'cat' => Category::where('id', $cat_id)->first(),
            'brand' => Brands::where('id', $brand_id)->first(),
            'count_ads' => Advert::where('user_id', $ad_owner)->count()
        ];

        // Car details
        $data['car'] = CarDetail::where('advert_id', $id)->first();
        if ($data['car']) {
            $model_id = $data['car']->model;
            $data['model'] = Models::where('id', $model_id)->first();
        }

        // Phone details
        $data['phone'] = PhoneDetail::where('advert_id', $id)->first();
        if ($data['phone']) {
            $model_id = $data['phone']->model;
            $data['model'] = Models::where('id', $model_id)->first();
        }

        // Related adverts
        $data['adverts'] = Advert::with('images')
            ->inRandomOrder()
            ->where('user_id', $ad_owner)
            ->activeNotRecentlySold()
            ->where('id', '!=', $id)
            ->limit(6)
            ->get();

        // Similar adverts
        $data['similar_ads'] = Advert::with('images')
            ->inRandomOrder()
            ->where(function($q) use ($ad) {
                $q->where('ad_title', 'LIKE', '%' . $ad->ad_title . '%')
                  ->orWhere('category', $ad->category);
            })
            ->where('id', '!=', $id)
            ->activeNotRecentlySold()
            ->where('user_id', '!=', $ad_owner)
            ->limit(3)
            ->get();

        // Increment views
        Advert::where('id', $id)->increment('views', 1);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/adverts/seller/{seller_id}",
     *     summary="Get adverts by seller",
     *     tags={"Adverts"},
     *     @OA\Parameter(
     *         name="seller_id",
     *         in="path",
     *         required=true,
     *         description="Seller ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="ads", ref="#/components/schemas/AdvertList"),
     *             @OA\Property(property="owner", ref="#/components/schemas/User"),
     *             @OA\Property(property="count_ads", type="integer")
     *         )
     *     )
     * )
     */
    public function sellerAdverts($seller_id, Request $request)
    {
        $ads = Advert::with('firstImage', 'owner')
            ->where('user_id', $seller_id)
            ->activeNotRecentlySold()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $owner = User::where('user_id', $seller_id)->first();
        $count_ads = Advert::where('user_id', $seller_id)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'ads' => $ads,
                'owner' => $owner,
                'count_ads' => $count_ads
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/categories",
     *     summary="Get all categories with counts",
     *     tags={"Categories"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="categories", type="array", @OA\Items(ref="#/components/schemas/CategoryWithCount"))
     *         )
     *     )
     * )
     */
    public function categories()
    {
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

        return response()->json([
            'success' => true,
            'data' => $categoryCounts
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/categories/{category_slug}",
     *     summary="Get adverts by category",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="category_slug",
     *         in="path",
     *         required=true,
     *         description="Category slug",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="ads", ref="#/components/schemas/AdvertList"),
     *             @OA\Property(property="cat", ref="#/components/schemas/Category"),
     *             @OA\Property(property="count_cat", type="integer"),
     *             @OA\Property(property="subcategories", type="array", @OA\Items(ref="#/components/schemas/SubCategoryWithCount"))
     *         )
     *     )
     * )
     */
    public function categoryAdverts($category_slug, Request $request)
    {
        $cat = Category::where('category_slug', $category_slug)->firstOrFail();

        $ads = (new FeaturedAdPaginator($request->get('page', 1)))
            ->filters(['category' => $cat->id])
            ->paginate();

        $count_cat = Advert::activeNotRecentlySold()
                          ->where('category', $cat->id)
                          ->count();

        $subcategories = DB::table('sub_categories')
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

        return response()->json([
            'success' => true,
            'data' => [
                'ads' => $ads,
                'cat' => $cat,
                'count_cat' => $count_cat,
                'subcategories' => $subcategories
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/categories/{category_slug}/{subcat_slug}",
     *     summary="Get adverts by subcategory",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="category_slug",
     *         in="path",
     *         required=true,
     *         description="Category slug",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="subcat_slug",
     *         in="path",
     *         required=true,
     *         description="Subcategory slug",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="ads", ref="#/components/schemas/AdvertList"),
     *             @OA\Property(property="subcat", ref="#/components/schemas/SubCategory"),
     *             @OA\Property(property="count_subcat", type="integer"),
     *             @OA\Property(property="brands", type="array", @OA\Items(ref="#/components/schemas/BrandWithCount"))
     *         )
     *     )
     * )
     */
    public function subcategoryAdverts($category_slug, $subcat_slug, Request $request)
    {
        $cat = Category::where('category_slug', $category_slug)->firstOrFail();
        $subcat = SubCategory::where('sub_cat_slug', $subcat_slug)->where('cat_id', $cat->id)->firstOrFail();

        $ads = (new FeaturedAdPaginator($request->get('page', 1)))
            ->filters(['sub_category' => $subcat->id])
            ->paginate();

        $count_subcat = Advert::activeNotRecentlySold()
                            ->where('sub_category', $subcat->id)
                            ->count();

        $brands = DB::table('brands')
            ->leftJoin('adverts', 'brands.id', '=', 'adverts.brand')
            ->where('brands.subcat_id', $subcat->id)
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

        return response()->json([
            'success' => true,
            'data' => [
                'ads' => $ads,
                'subcat' => $subcat,
                'count_subcat' => $count_subcat,
                'brands' => $brands
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/brands/{category_slug}/{subcat_slug}/{brand_slug}",
     *     summary="Get adverts by brand",
     *     tags={"Brands"},
     *     @OA\Parameter(
     *         name="category_slug",
     *         in="path",
     *         required=true,
     *         description="Category slug",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="subcat_slug",
     *         in="path",
     *         required=true,
     *         description="Subcategory slug",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="brand_slug",
     *         in="path",
     *         required=true,
     *         description="Brand slug",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="ads", ref="#/components/schemas/AdvertList"),
     *             @OA\Property(property="brand", ref="#/components/schemas/Brand"),
     *             @OA\Property(property="subcat", ref="#/components/schemas/SubCategory"),
     *             @OA\Property(property="count_subcat", type="integer")
     *         )
     *     )
     * )
     */
    public function brandAdverts($category_slug, $subcat_slug, $brand_slug, Request $request)
    {
        $cat = Category::where('category_slug', $category_slug)->firstOrFail();
        $subcat = SubCategory::where('sub_cat_slug', $subcat_slug)->where('cat_id', $cat->id)->firstOrFail();
        $brand = Brands::where('brand_slug', $brand_slug)->where('subcat_id', $subcat->id)->firstOrFail();

        $ads = (new FeaturedAdPaginator($request->get('page', 1)))
            ->filters(['brand' => $brand->id])
            ->paginate();

        $count_subcat = Advert::activeNotRecentlySold()
                        ->where('sub_category', $subcat->id)
                        ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'ads' => $ads,
                'brand' => $brand,
                'subcat' => $subcat,
                'count_subcat' => $count_subcat
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/adverts/{id}/report",
     *     summary="Report an advert",
     *     tags={"Adverts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Advert ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"subject", "message"},
     *             @OA\Property(property="subject", type="string"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Report submitted successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function reportAdvert($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required',
            'message' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $advert = Advert::findOrFail($id);
        $user_id = auth()->id();

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

        $user = auth()->user();
        $details = [
            'advert' => $advert->ad_title,
            'name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'subject' => $request->subject,
            'message' => $request->message,
        ];

        Mail::to(config('global.admin_email'))->send(new ReportMail($details));

        return response()->json([
            'success' => true,
            'message' => 'Your report has been received'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/adverts/{id}/apply",
     *     summary="Apply for a job advert",
     *     tags={"Adverts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Advert ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"message"},
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Application submitted successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function applyJob($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $advert = Advert::findOrFail($id);
        $user_id = auth()->id();
        $receiver_id = $advert->user_id;

        $message = Message::create([
            'advert_id' => $id,
            'sender_id' => $user_id,
            'receiver_id' => $receiver_id,
            'message_content' => $request->message,
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'You have successfully applied for the job'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/adverts/featured",
     *     summary="Get featured adverts",
     *     tags={"Adverts"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="featured", type="array", @OA\Items(ref="#/components/schemas/Advert"))
     *         )
     *     )
     * )
     */
    public function featuredAdverts()
    {
        $featured = Advert::inRandomOrder()
            ->where('ad_status', 1)
            ->where(function($query) {
                $query->where('sold', '!=', 'Yes')
                      ->orWhere(function($query) {
                          $query->where('sold', 'Yes')
                                ->whereNotNull('sold_date')
                                ->where('sold_date', '>=', now()->subDays(7));
                      });
            })
            ->orderBy('views', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $featured
        ]);
    }
}
