<?php

use App\Models\Category;
use App\Models\Advertising;
use Illuminate\Support\Facades\DB;
use App\Models\Message;

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