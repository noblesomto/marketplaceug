<?php

use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Brands;
use App\Models\Advertising;
use App\Models\Advert;
use Illuminate\Support\Facades\DB;
use App\Models\Message;
use App\Models\User;

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
            ->where('ad_status', 1)     // ✅ Only active ads
            ->where('sold', 'No')       // ✅ Only unsold ads
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
            ->where('ad_status', 1)     // Only active
            ->where('sold', 'No');      // Only unsold

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
            ->where('ad_status', 1)     // Only active adverts
            ->where('sold', 'No');      // Not sold

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
