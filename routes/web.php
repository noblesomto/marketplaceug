<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdvertController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PaystackController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ShipperController;

Route::get('/', [PageController::class, 'index']);
Route::any('/page', [PageController::class, 'page']);
Route::any('/about-us', [PageController::class, 'about']);
Route::any('/contact-us', [PageController::class, 'contact']);
Route::any('/faq', [PageController::class, 'faq']);
Route::any('/shipping', [PageController::class, 'shipping']);
Route::any('/email', [PageController::class, 'email']);


//Account Section
Route::any('/login', [AccountController::class, 'login']);
Route::any('/register', [AccountController::class, 'register']);
Route::get('/verifyaccount/{id}/{token}', [AccountController::class, 'verifyaccount']);
Route::any('/authenticate', [AccountController::class, 'authenticate']);
Route::any('/resend-otp', [AccountController::class, 'resend_otp']);
Route::any('/forgot-password', [AccountController::class, 'forgot_password']);
Route::any('/reset-password/{id}/{token}', [AccountController::class, 'reset_password']);
Route::any('/admin', [AccountController::class, 'adminlogin']);
Route::any('/shipper', [AccountController::class, 'shipper']);

//Adverts
Route::get('/advert/{id}/{slug}', [AdvertController::class, 'advert']);
Route::get('/adverts', [AdvertController::class, 'adverts']);
Route::get('/all-categories', [AdvertController::class, 'all_categories']);
Route::get('/category/{id}/{slug}', [AdvertController::class, 'category']);
Route::get('/subcat/{id}/{slug}', [AdvertController::class, 'sub_category']);
Route::get('/brand/{id}/{slug}', [AdvertController::class, 'brand']);
Route::any('/search', [AdvertController::class, 'search']);
Route::get('/buy-direct/{id}', [AdvertController::class, 'buy_direct']);
Route::post('/calculate-shipping/{id}', [AdvertController::class, 'calculate_shipping']);
Route::get('/buy-direct-payment/{id}', [AdvertController::class, 'buy_direct_payment'])->name('buy.direct.payment');
Route::get('/seller/{id}', [AdvertController::class, 'seller']);
Route::any('/report-ad/{id}', [AdvertController::class, 'report_advert'])->middleware('usersession');
Route::any('/apply/{id}', [AdvertController::class, 'apply_job'])->middleware('usersession');

// Get State and Locations
Route::get('/states', [LocationController::class, 'index']);
Route::get('/get-gig/{state_id}', [LocationController::class, 'getGIG']);
Route::post('/shipping-cost', [LocationController::class, 'getAgilityShippingCost']);


//Buy Direct Paystack
Route::post('/pay', [PaystackController::class, 'initialize'])->name('paystack.pay')->middleware('usersession');
Route::get('/payment/callback', [PaystackController::class, 'callback'])->name('paystack.callback')->middleware('usersession');
Route::get('/payment-success', [PaystackController::class, 'success'])->name('payment.success');
Route::get('/payment-failed', [PaystackController::class, 'failed'])->name('payment.failed');


//Mobile Category
Route::get('/m-category/{id}/{slug}', [AdvertController::class, 'mobile_category']);

//Messages
Route::get('/messages/{id}/{user}', [MessageController::class, 'fetchMessages'])->middleware('usersession');
Route::post('/messages', [MessageController::class, 'sendMessage'])->middleware('usersession');
Route::get('/my-messages/{id}', [MessageController::class, 'fetchMyMessages'])->middleware('usersession');

Route::get('/chat-buyer/{user_id}/{id}', [MessageController::class, 'chat_buyer']);

Route::get('/chat/{advertId}/{receiverId}', [MessageController::class, 'showMessages'])->name('chat.show')->middleware('usersession');
Route::post('/chat/{advertId}/{receiverId}', [MessageController::class, 'sendMessage'])->name('chat.sendMessage')->middleware('usersession');
Route::get('/unread-messages-count', [MessageController::class, 'countUnreadMessages']);
Route::get('/payment/mark-received/{id}', [MessageController::class, 'mark_received'])->middleware('usersession');

//User Dashboard Section
Route::get('/user/index', [UserController::class, 'index'])->name('user.index')->middleware('usersession');
Route::any('/user/my-ads', [UserController::class, 'my_ads'])->middleware('usersession');
Route::any('/user/payment', [UserController::class, 'payments'])->middleware('usersession');
Route::get('/user/ad-shipping/{id}', [UserController::class, 'ad_shipping'])->middleware('usersession');
Route::post('/user/update-shipping/{id}', [UserController::class, 'update_shipping'])->middleware('usersession');
Route::any('/user/ad-status/{status}/{id}', [UserController::class, 'ad_status'])->middleware('usersession');
Route::any('/user/post-ad', [UserController::class, 'post_ad'])->middleware('usersession');
Route::get('/user/edit-ad/{id}', [UserController::class, 'edit_ad'])->name('edit.ad')->middleware('usersession');
Route::post('/user/edit-ad/{id}', [UserController::class, 'edit_ad'])->name('update.ad')->middleware('usersession');
Route::any('/user/boost-ad/{id}', [UserController::class, 'boost_ad'])->middleware('usersession');
Route::any('/user/boosted-ad/{id}', [UserController::class, 'boosted_ad'])->middleware('usersession');
Route::any('/user/category', [UserController::class, 'category'])->middleware('usersession');
Route::any('/user/orders', [UserController::class, 'orders'])->middleware('usersession');
Route::any('/user/messages', [UserController::class, 'messages'])->middleware('usersession');
Route::any('/user/add-wishlist/{id}', [UserController::class, 'add_wishlist'])->middleware('usersession');
Route::any('/user/favourites', [UserController::class, 'favourites'])->middleware('usersession');
Route::any('/user/mark-sold/{id}', [UserController::class, 'advert_sold'])->middleware('usersession');


//Profile
Route::any('/user/profile', [UserController::class, 'profile'])->middleware('usersession');
Route::any('/user/settings', [UserController::class, 'settings'])->middleware('usersession');
Route::any('/user/profile-address', [UserController::class, 'profile_address'])->middleware('usersession');
Route::any('/user/profile-info', [UserController::class, 'profile_info'])->middleware('usersession');
Route::any('/user/payments', [UserController::class, 'payment_info'])->middleware('usersession');
Route::any('/user/profile-phone', [UserController::class, 'profile_phone'])->middleware('usersession');
Route::any('/user/change-password', [UserController::class, 'change_password'])->middleware('usersession');
Route::any('/user/disable-account', [UserController::class, 'disable_account'])->middleware('usersession');
Route::get('/user/logout', [UserController::class, 'logout'])->middleware('usersession');
Route::any('/user/profile-notification', [UserController::class, 'profile_notification'])->middleware('usersession');
Route::post('/update-notifications', [UserController::class, 'updateNotifications'])
    ->middleware('usersession')
    ->name('user.update-notifications');

//Chat Section
Route::any('/user/chat-buyer/{user}/{id}', [UserController::class, 'chat_buyer'])->middleware('usersession');
Route::get('/chat-seller/{user}/{id}', [UserController::class, 'chat_seller']);

//Paystack User Boost Add
Route::post('boost/pay', [PaystackController::class, 'initialize_boost'])->name('boost.pay')->middleware('usersession');
Route::get('/boost/callback', [PaystackController::class, 'callback_boost'])->name('boost.callback')->middleware('usersession');
Route::get('/payment-success', [PaystackController::class, 'success'])->name('payment.success');
Route::get('/payment-failed', [PaystackController::class, 'failed'])->name('payment.failed');

//Admin Index
Route::get('/admin/index', [AdminController::class, 'index'])->middleware('adminsession');
Route::any('/admin/category', [AdminController::class, 'category'])->middleware('adminsession');
Route::any('/admin/delete-category/{id}', [AdminController::class, 'delete_category'])->middleware('adminsession');
Route::any('/admin/sub-category/{id}', [AdminController::class, 'sub_category'])->middleware('adminsession');
Route::any('/admin/delete-subcategory/{id}/{cat}', [AdminController::class, 'delete_subcategory'])->middleware('adminsession');
Route::any('/admin/brand/{id}', [AdminController::class, 'brand'])->middleware('adminsession');
Route::any('/admin/delete-brand/{id}/{cat}', [AdminController::class, 'delete_brand'])->middleware('adminsession');
Route::any('/admin/model/{id}', [AdminController::class, 'model'])->middleware('adminsession');
Route::any('/admin/delete-model/{id}/{cat}', [AdminController::class, 'delete_model'])->middleware('adminsession');
Route::get('/admin/logout', [AdminController::class, 'logout'])->middleware('adminsession');

//Manage Adverts
Route::any('/admin/active-adverts', [AdminController::class, 'active_adverts'])->middleware('adminsession');
Route::any('/admin/disabled-adverts', [AdminController::class, 'disabled_adverts'])->middleware('adminsession');
Route::any('/admin/sold-adverts', [AdminController::class, 'sold_adverts'])->middleware('adminsession');
Route::any('/admin/advert-status/{id}/{status}', [AdminController::class, 'advert_status'])->middleware('adminsession');
Route::any('/admin/sold-status/{id}/{status}', [AdminController::class, 'sold_status'])->middleware('adminsession');
Route::any('/admin/active-users', [AdminController::class, 'active_users'])->middleware('adminsession');
Route::any('/admin/user-status/{id}/{status}', [AdminController::class, 'user_status'])->middleware('adminsession');
Route::any('/admin/disabled-users', [AdminController::class, 'disabled_users'])->middleware('adminsession');
//Route::any('/admin/delete-user/{id}', [AdminController::class, 'delete_user'])->middleware('adminsession');
Route::any('/admin/view-user/{id}', [AdminController::class, 'view_user'])->middleware('adminsession');
Route::any('/admin/delete-advert/{id}', [AdminController::class, 'delete_advert'])->middleware('adminsession');


//Manage Payments
Route::any('/admin/completed-payments', [AdminController::class, 'completed_payments'])->middleware('adminsession');
Route::any('/admin/pending-payments', [AdminController::class, 'pending_payments'])->middleware('adminsession');
Route::any('/admin/confirm-delivery/{id}', [AdminController::class, 'confirm_delivery'])->middleware('adminsession');

//Advertising
Route::any('/admin/create-advert', [AdminController::class, 'create_advert'])->middleware('adminsession');

Route::any('/admin/view-reports', [AdminController::class, 'view_reports'])->middleware('adminsession');
Route::any('/admin/report-status/{id}/{status}', [AdminController::class, 'report_status'])->middleware('adminsession');

//Shhipping
Route::any('/settings/setup-shipping', [SettingController::class, 'shipping'])->middleware('adminsession');

//Shipper Dashboard
Route::get('/shipper/index', [ShipperController::class, 'index'])->middleware('shipsession');
Route::any('/shipper/get-shipping', [ShipperController::class, 'get_shipping'])->middleware('shipsession');
Route::get('/shipper/order-details/{id}', [ShipperController::class, 'order_details'])->middleware('shipsession');
Route::any('/shipper/update-shipping/{id}', [ShipperController::class, 'update_shipping'])->middleware('shipsession');


//GIG Logistics
Route::any('/settings/gig-locations', [SettingController::class, 'gig_locations'])->middleware('adminsession');
Route::any('/settings/delete-gig-location/{id}', [SettingController::class, 'delete_gig_location'])->middleware('adminsession');

Route::get('/fetch-subcat/{cat_id}', [AdminController::class, 'fetch_subcat']);
Route::get('/fetch-brand/{cat_id}', [AdminController::class, 'fetch_brand']);
Route::get('/fetch-model/{cat_id}', [AdminController::class, 'fetch_model']);

Route::get('/api/check-following/{userId}', [UserController::class, 'checkFollowing']);
Route::post('/api/toggle-follow', [UserController::class, 'toggleFollow'])->middleware('usersession');

Route::get('/unread-messages-count', function () {
    $userId = session('user_id');
    if (!$userId) {
        return response()->json(['count' => 0]);
    }

    $count = \App\Models\Message::where('receiver_id', $userId)
        ->where('is_read', false)
        ->count();

    return response()->json(['count' => $count]);
});

