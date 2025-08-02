<?php

use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Brands;
use App\Models\Advertising;
use App\Models\Advert;
use Illuminate\Support\Facades\DB;
use App\Models\Message;
use App\Models\User;
use App\Models\Feedback;


if (!function_exists('getCategories')) {
    function getCategories()
    {
        return Category::all();
    }
}

if (!function_exists('getAdverts')) {
    function getAdverts()
    {
        return Advertising::inRandomOrder()->where('type', 'banner')->take(1)->get();
    }
}

if (!function_exists('getSideAdverts')) {
    function getSideAdverts()
    {
        return Advertising::inRandomOrder()->where('type', 'sidebar')->take(1)->get();
    }
}

if (!function_exists('countUserFollowers')) {
    /**
     * Count the number of followers for a specific user
     * 
     * @param int $userId The ID of the user to count followers for
     * @return int The number of followers
     */
    function countUserFollowers($userId)
    {
        return \DB::table('followers')
                 ->where('follow', $userId)
                 ->count();
    }
}


if (!function_exists('getTotalUnreadMessages')) {
    function getTotalUnreadMessages()
    {
        $userId = Session::get('user_id');

        if (!$userId) {
            return 0;
        }

        return Message::where('receiver_id', $userId)
            ->where('is_read', false)
            ->count();
    }
}

if (!function_exists('getAdvertsGroupedByState')) {
    function getAdvertsGroupedByState(array $filters = [])
    {
        $query = Advert::select('state', DB::raw('count(*) as total'))
            ->where('ad_status', 1)
            ->where('sold', 'No')
            ->groupBy('state')
            ->orderByDesc('total');

        // ✅ Optional filters
        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['sub_category'])) {
            $query->where('sub_category', $filters['sub_category']);
        }

        if (!empty($filters['brand'])) {
            $query->where('brand', $filters['brand']);
        }


        return $query->get();
    }
}


if (!function_exists('getAdvertCount')) {
    function getAdvertCount(array $filters = [])
    {
        $query = Advert::query()
            ->where('ad_status', 1)
            ->where('sold', 'No');

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['sub_category'])) {
            $query->where('sub_category', $filters['sub_category']);
        }

        if (!empty($filters['brand'])) {
            $query->where('brand', $filters['brand']);
        }

        if (!empty($filters['state'])) {
            $query->where('state', $filters['state']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        return $query->count();
    }
}


if (!function_exists('advert_count_by_filter')) {
    /**
     * Get the total count of adverts filtered by verified users, category, sub_category, and brand.
     *
     * @param string $verified ('yes' or 'no')
     * @param int|string|null $category    Category ID or slug
     * @param int|string|null $subCategory SubCategory ID or slug
     * @param int|string|null $brand       Brand ID or slug
     * @return int
     */
    function advert_count_by_filter($verified = 'yes', $category = null, $subCategory = null, $brand = null)
    {
        $query = Advert::whereHas('owner', function ($q) use ($verified) {
                $q->where('verified', $verified);
            })
            ->where('ad_status', 1)
            ->where('sold', 'No');

        // Category filter
        if (!empty($category)) {
            $categoryId = is_numeric($category)
                ? $category
                : Category::where('category_slug', $category)->value('id');
            if ($categoryId) {
                $query->where('category', $categoryId);
            }
        }

        // SubCategory filter
        if (!empty($subCategory)) {
            $subCategoryId = is_numeric($subCategory)
                ? $subCategory
                : SubCategory::where('sub_cat_slug', $subCategory)->value('id');
            if ($subCategoryId) {
                $query->where('sub_category', $subCategoryId);
            }
        }

        // Brand filter
        if (!empty($brand)) {
            $brandId = is_numeric($brand)
                ? $brand
                : Brand::where('brand_slug', $brand)->value('id');
            if ($brandId) {
                $query->where('brand', $brandId);
            }
        }

        return $query->count();
    }
}

if (!function_exists('setViews')) {
    /**
     * Set views count to 1000 or a specified value
     *
     * @param int|null $count Optional custom view count
     * @return int
     */
    function setViews($count = null)
    {
        return $count ?? 1000;
    }
}

if (!function_exists('get_user_feedback_averages')) {
    function get_user_feedback_averages($user_id)
    {
        $feedbacks = Feedback::where('seller_id', $user_id)->get();

        if ($feedbacks->isEmpty()) {
            return [
                'rating' => 0,
                'satisfaction' => 0,
                'reliable' => 0,
                'friendly' => 0,
                'count' => 0
            ];
        }

        return [
            'rating' => round($feedbacks->avg('rating'), 1),
            'satisfaction' => round($feedbacks->avg('satisfaction'), 1),
            'reliable' => round($feedbacks->avg('reliable'), 1),
            'friendly' => round($feedbacks->avg('friendly'), 1),
            'count' => $feedbacks->count()
        ];
    }
}

if (!function_exists('rating_label_class')) {
    function rating_label_class($average)
    {
        if ($average >= 4.0) {
            return ['label' => 'Very', 'color' => 'bg-green-200 text-green-800']; // Very Reliable
        } elseif ($average >= 2.0) {
            return ['label' => 'Fairly', 'color' => 'bg-yellow-200 text-yellow-800']; // Fairly Friendly
        } elseif ($average > 0) {
            return ['label' => 'Barely', 'color' => 'bg-red-200 text-red-800']; // Barely Satisfied
        } else {
            return ['label' => 'Unrated', 'color' => 'bg-gray-200 text-gray-600'];
        }
    }
}


if (!function_exists('feedback_rating_labels')) {
    function feedback_rating_labels($user_id)
    {
        $averages = get_user_feedback_averages($user_id);
        return [
            'satisfaction' => rating_label_class($averages['satisfaction']),
            'friendly' => rating_label_class($averages['friendly']),
            'reliable' => rating_label_class($averages['reliable']),
        ];

    }
}

if (!function_exists('get_brands_with_advert_count')) {
    function get_brands_with_advert_count($categoryId = null, $subCategoryId = null)
    {
        $brandsQuery = Brands::query();

        if ($subCategoryId) {
            $brandsQuery->where('subcat_id', $subCategoryId);
        } elseif ($categoryId) {
            // Get subcategory IDs that belong to this category
            $subcatIds = SubCategory::where('cat_id', $categoryId)->pluck('id')->toArray();
            $brandsQuery->whereIn('subcat_id', $subcatIds);
        }

        //dd($categoryId);
        return $brandsQuery->with([
            'subCategory.category'  // ✅ Eager load category via subcategory
        ])->withCount(['adverts' => function ($query) use ($subCategoryId, $categoryId) {
            $query->where('ad_status', 1)
                  ->where('sold', 'No');

            if ($subCategoryId) {
                $query->where('sub_category', $subCategoryId);
            } elseif ($categoryId) {
                $subcatIds = SubCategory::where('cat_id', $categoryId)->pluck('id')->toArray();
                $query->whereIn('sub_category', $subcatIds);
            }
        }])->having('adverts_count', '>', 0)->get();

    }
}
