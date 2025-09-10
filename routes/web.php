<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SearchFilter;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserProfile;
use App\Http\Controllers\UserManageAdverts;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdvertController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PaystackController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ShipperController;
use App\Http\Controllers\Admin\ManageAdverts;
use App\Http\Controllers\Admin\ManageBoost;
use App\Http\Controllers\Admin\ManageCategories;
use App\Http\Controllers\Admin\ManageShipping;
use App\Http\Controllers\Admin\ManageUsers;
use App\Http\Controllers\Admin\ManageAdvertising;
use App\Http\Controllers\Admin\ManagePayments;
use App\Http\Controllers\Admin\ManageAdminUsers;
use App\Http\Controllers\Admin\RolePermissionController;
use Illuminate\Support\Facades\Broadcast;

Route::get('/', [AdvertController::class, 'index']);
Route::any('/page', [PageController::class, 'page']);
Route::any('/about-us', [PageController::class, 'about']);
Route::any('/career', [PageController::class, 'career']);
Route::any('/privacy-policy', [PageController::class, 'privacy']);
Route::any('/cookie-policy', [PageController::class, 'cookie']);
Route::any('/billing-policy', [PageController::class, 'billing']);
Route::any('/copyright-policy', [PageController::class, 'copyright']);
Route::any('/safety-tips', [PageController::class, 'safety']);
Route::any('/our-terms', [PageController::class, 'terms']);
Route::any('/payments-refunds', [PageController::class, 'payments_refunds']);
Route::any('/how-it-works', [PageController::class, 'how_it_works']);
Route::any('/faq', [PageController::class, 'faq']);
Route::any('/advertise-with-us', [PageController::class, 'advertise']);
Route::any('/contact-us', [PageController::class, 'contact']);
Route::any('/shipping', [PageController::class, 'shipping']);
Route::any('/email', [PageController::class, 'email']);


//Account Section
Route::any('/login', [AccountController::class, 'login']);
Route::any('/register', [AccountController::class, 'register']);
Route::get('/verifyaccount/{id}/{token}', [AccountController::class, 'verifyaccount']);
Route::any('/resend-email', [AccountController::class, 'resend_email'])->name('activation.resend');
Route::any('/authenticate', [AccountController::class, 'authenticate']);
Route::any('/resend-otp', [AccountController::class, 'resend_otp']);
Route::any('/forgot-password', [AccountController::class, 'forgot_password']);
Route::any('/reset-password/{id}/{token}', [AccountController::class, 'reset_password']);
Route::any('/admin', [AccountController::class, 'adminlogin'])->name('admin.login');
Route::any('/shipper', [AccountController::class, 'shipper']);

//Adverts
Route::get('/listings', [AdvertController::class, 'adverts']);
Route::get('/listings/fetchDesktop', [AdvertController::class, 'loadMoreAds'])->name('ads.loadMore');
Route::get('/listings/fetchMobile', [AdvertController::class, 'loadMoreAdsMobile'])->name('ads.loadMore');
Route::get('/all-categories', [AdvertController::class, 'all_categories']);
Route::get('/category/{category_slug}', [AdvertController::class, 'category']);
Route::get('/category/all-{slug}', [AdvertController::class, 'all_category']);
Route::get('/category/{category_slug}/{subcat_slug}', [AdvertController::class, 'sub_category']);
Route::get('/category/{category_slug}/{subcat_slug}/all-{slug}', [AdvertController::class, 'all_subcat']);
Route::get('/category/{category_slug}/{subcat_slug}/{brand_slug}', [AdvertController::class, 'brand']);


Route::get('/buy-direct/{id}', [AdvertController::class, 'buy_direct']);
Route::post('/calculate-shipping/{id}', [AdvertController::class, 'calculate_shipping']);
Route::get('/buy-direct-payment/{id}', [AdvertController::class, 'buy_direct_payment'])->name('buy.direct.payment');
Route::get('/seller/{id}', [AdvertController::class, 'seller']);
Route::any('/report-ad/{id}', [AdvertController::class, 'report_advert'])->middleware('usersession');
Route::any('/apply/{id}', [AdvertController::class, 'apply_job'])->middleware('usersession');

//Search and Filter
Route::any('/search', [SearchFilter::class, 'search']);
Route::post('/filter/adverts', [SearchFilter::class, 'filter']);
Route::post('/filter/sellers', [SearchFilter::class, 'filterBySeller'])->name('filter.sellers');
Route::post('/filter/buydirect', [SearchFilter::class, 'filterByBuydirect'])->name('filter.buydirect');


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
Route::post('/user/confirm-delivery/{id}', [UserController::class, 'confirmDelivery'])->middleware('usersession');
Route::get('/user/ad-shipping/{id}', [UserController::class, 'ad_shipping'])->middleware('usersession');
Route::post('/user/update-shipping/{id}', [UserController::class, 'update_shipping'])->middleware('usersession');
Route::any('/user/ad-status/{status}/{id}', [UserController::class, 'ad_status'])->middleware('usersession');
Route::any('/user/category', [UserController::class, 'category'])->middleware('usersession');
Route::any('/user/orders', [UserController::class, 'orders'])->middleware('usersession');
Route::any('/user/messages', [UserController::class, 'messages'])->middleware('usersession');
Route::get('/user/feedbacks', [UserController::class, 'feedbacks'])->middleware('usersession');
Route::any('/user/add-wishlist/{id}', [UserController::class, 'add_wishlist'])->middleware('usersession');
Route::any('/user/favourites', [UserController::class, 'favourites'])->middleware('usersession');
Route::any('/user/mark-sold/{id}', [UserController::class, 'advert_sold'])->middleware('usersession');
Route::any('/reviews/feedbacks/{id}', [UserController::class, 'submit_feedback'])->middleware('usersession');
Route::any('/reviews/seller/{id}', [UserController::class, 'reviews_seller']);
Route::any('/user/notifications', [UserController::class, 'notifications'])->middleware('usersession');

//User Manage Ads
Route::any('/user/post-ad', [UserManageAdverts::class, 'post_ad'])->middleware('usersession');
Route::any('/user/post-boost-ad/{id}', [UserManageAdverts::class, 'post_boost_ad'])->middleware('usersession');
Route::get('/user/edit-ad/{id}', [UserManageAdverts::class, 'edit_ad'])->name('edit.ad')->middleware('usersession');
Route::post('/user/edit-ad/{id}', [UserManageAdverts::class, 'edit_ad'])->name('update.ad')->middleware('usersession');
Route::any('/user/boost-ad/{id}', [UserManageAdverts::class, 'boost_ad'])->middleware('usersession');
Route::any('/user/boosted-ad/{id}', [UserManageAdverts::class, 'boosted_ad'])->middleware('usersession');
Route::get('/user/delete-ad/{id}', [UserManageAdverts::class, 'delete_ad'])->name('edit.ad')->middleware('usersession');

//User Profile
Route::any('/user/profile', [UserProfile::class, 'profile'])->middleware('usersession');
Route::any('/user/settings', [UserProfile::class, 'settings'])->middleware('usersession');
Route::any('/user/profile-address', [UserProfile::class, 'profile_address'])->middleware('usersession');
Route::any('/user/profile-info', [UserProfile::class, 'profile_info'])->middleware('usersession');
Route::get('/user/get-verified', [UserProfile::class, 'get_verified'])->middleware('usersession');
Route::post('/user/submit-verification', [UserProfile::class, 'submit_verification'])->middleware('usersession');
Route::any('/user/payments', [UserProfile::class, 'payment_info'])->middleware('usersession');
Route::any('/user/profile-phone', [UserProfile::class, 'profile_phone'])->middleware('usersession');
Route::any('/user/change-password', [UserProfile::class, 'change_password'])->middleware('usersession');
Route::any('/user/disable-account', [UserProfile::class, 'disable_account'])->middleware('usersession');
Route::get('/user/logout', [UserProfile::class, 'logout'])->middleware('usersession');
Route::any('/user/profile-notification', [UserProfile::class, 'profile_notification'])->middleware('usersession');
Route::post('/update-notifications', [UserProfile::class, 'updateNotifications'])
    ->middleware('usersession')
    ->name('user.update-notifications');

//Chat Section
Route::any('/user/chat-buyer/{user}/{id}', [UserController::class, 'chat_buyer'])->middleware('usersession');
Route::get('/chat-seller/{user}/{id}', [UserController::class, 'chat_seller']);

//Paystack User Boost Add
Route::post('post-boost/pay', [PaystackController::class, 'initialize_post_boost'])->name('post-boost.pay')->middleware('usersession');
Route::post('boost/pay', [PaystackController::class, 'initialize_boost'])->name('boost.pay')->middleware('usersession');
Route::get('/boost/callback', [PaystackController::class, 'callback_boost'])->name('boost.callback')->middleware('usersession');
Route::get('/payment-success', [PaystackController::class, 'success'])->name('payment.success');
Route::get('/payment-failed', [PaystackController::class, 'failed'])->name('payment.failed');

//Admin Index
Route::get('/admin/index', [AdminController::class, 'index'])->middleware('adminsession');
Route::get('/admin/logout', [AdminController::class, 'logout'])->middleware('adminsession');


Route::middleware(['adminsession','adminrole:Finance,super_admin'])->group(function () {
    // Manage Payments
    Route::any('/admin/completed-payments', [ManagePayments::class, 'completed_payments']);
    Route::any('/admin/pending-payments', [ManagePayments::class, 'pending_payments']);
    Route::any('/admin/update-payment/{id}', [ManagePayments::class, 'update_payment']);
    Route::any('/admin/confirm-payment/{id}', [ManagePayments::class, 'confirm_payment']);

    // Manage Settlements
    Route::any('/admin/pending-settlements', [ManagePayments::class, 'pending_settlements']);
    Route::any('/admin/completed-settlements', [ManagePayments::class, 'completed_settlements']);
    Route::any('/admin/confirm-settlement/{id}', [ManagePayments::class, 'confirm_settlement']);
    Route::post('/admin/payout/{id}', [ManagePayments::class, 'sendPayout'])->name('payout.transfer');
});

/*
|--------------------------------------------------------------------------
| Advert Manager Role Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['adminsession','adminrole:Advert_manager,super_admin'])->group(function () {
    // Advertising
    Route::any('/admin/create-advert', [ManageAdvertising::class, 'create_advert']);
    Route::any('/admin/delete-advert/{id}', [ManageAdvertising::class, 'delete_advert']);
    Route::put('/admin/update-advert/{id}', [ManageAdvertising::class, 'updateAdvert'])->name('admin.update.advert');

    // Manage Advert Boost
    Route::any('/boost/active', [ManageBoost::class, 'active']);
    Route::any('/boost/completed', [ManageBoost::class, 'completed']);
    Route::any('/boost/unpaid', [ManageBoost::class, 'unpaid']);
    Route::any('/boost/status/{id}/{status}', [ManageBoost::class, 'status']);

    //Manage Adverts
    Route::any('/admin/active-adverts', [ManageAdverts::class, 'active_adverts']);
    Route::any('/admin/disabled-adverts', [ManageAdverts::class, 'disabled_adverts']);
    Route::any('/admin/sold-adverts', [ManageAdverts::class, 'sold_adverts']);
    Route::any('/admin/advert-status/{id}/{status}', [ManageAdverts::class, 'advert_status']);
    Route::any('/admin/sold-status/{id}/{status}', [ManageAdverts::class, 'sold_status']);
    Route::any('/admin/delete-ad/{id}', [ManageAdverts::class, 'delete_advert']);

    //Manage Categories
    Route::any('/admin/category', [ManageCategories::class, 'category']);
    Route::any('/admin/delete-category/{id}', [ManageCategories::class, 'delete_category']);
    Route::any('/admin/sub-category/{id}', [ManageCategories::class, 'sub_category']);
    Route::any('/admin/delete-subcategory/{id}/{cat}', [ManageCategories::class, 'delete_subcategory']);
    Route::any('/admin/brand/{id}', [ManageCategories::class, 'brand']);
    Route::any('/admin/delete-brand/{id}/{cat}', [ManageCategories::class, 'delete_brand']);
    Route::any('/admin/model/{id}', [ManageCategories::class, 'model']);
    Route::any('/admin/delete-model/{id}/{cat}', [ManageCategories::class, 'delete_model']);
});

/*
|--------------------------------------------------------------------------
| Resolution Role Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['adminsession','adminrole:Resolution,super_admin'])->group(function () {
    // Reports
    Route::any('/admin/view-reports', [AdminController::class, 'view_reports']);
    Route::any('/admin/report-status/{id}/{status}', [AdminController::class, 'report_status']);
});

/*
|--------------------------------------------------------------------------
| Customer Care Role Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['adminsession','adminrole:Customer_care,super_admin'])->group(function () {

    //Manage Users
    Route::any('/admin/active-users', [ManageUsers::class, 'active_users']);
    Route::any('/admin/user-status/{id}/{status}', [ManageUsers::class, 'user_status']);
    Route::any('/admin/disable-status/{id}/{status}', [ManageUsers::class, 'disable_status']);
    Route::any('/admin/unverified-users', [ManageUsers::class, 'unverified_users']);
    Route::any('/admin/disabled-users', [ManageUsers::class, 'disabled_users']);
    //Route::any('/admin/delete-user/{id}', [ManageUsers::class, 'delete_user']);
    Route::any('/admin/view-user/{id}', [ManageUsers::class, 'view_user']);
    Route::any('/admin/user-verification', [ManageUsers::class, 'user_verification']);
    Route::any('/admin/verify-status/{id}/{status}/{verify}', [ManageUsers::class, 'verify_status']);

    // Shipping
    Route::any('/settings/setup-shipping', [SettingController::class, 'shipping']);
    Route::put('/settings/update-shipping/{id}', [SettingController::class, 'shipping']);
});

/*
|--------------------------------------------------------------------------
| Super Admin Only Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['adminsession','adminrole:super_admin'])->group(function () {
    // Roles & Permissions
    Route::prefix('settings/roles')->group(function () {
        Route::get('/', [RolePermissionController::class, 'index'])->name('admin.roles.index');
        // Create
        Route::post('/roles', [RolePermissionController::class, 'storeRole'])->name('admin.roles.store');
        Route::post('/permissions', [RolePermissionController::class, 'storePermission'])->name('admin.permissions.store');
        // Assign
        Route::post('/roles/{role}/assign', [RolePermissionController::class, 'assignPermission'])->name('admin.roles.assign');
        Route::post('/admins/{admin}/assign', [RolePermissionController::class, 'assignRoleToAdmin'])->name('admin.admins.assign');
    });

    //GIG Logistics
    Route::any('/settings/gig-locations', [SettingController::class, 'gig_locations']);
    Route::any('/settings/delete-gig-location/{id}', [SettingController::class, 'delete_gig_location']);
    Route::post('/settings/update-gig-location', [SettingController::class, 'updateGigLocation'])->name('update.gig.location');
});

//Shipper Dashboard
Route::get('/shipper/index', [ShipperController::class, 'index'])->middleware('shipsession');
Route::any('/shipper/get-shipping', [ShipperController::class, 'get_shipping'])->middleware('shipsession');
Route::get('/shipper/order-details/{id}', [ShipperController::class, 'order_details'])->middleware('shipsession');
Route::any('/shipper/update-shipping/{id}', [ShipperController::class, 'update_shipping'])->middleware('shipsession');

//Manage Admin USers
Route::prefix('settings/manage-admins')
    ->middleware(['adminsession','adminrole:super_admin'])
    ->group(function () {
    Route::get('/', [ManageAdminUsers::class, 'index']); // Create
    Route::post('/', [ManageAdminUsers::class, 'store']); // Create
    Route::put('/{id}', [ManageAdminUsers::class, 'update']); // Update
    Route::delete('/{id}', [ManageAdminUsers::class, 'destroy']); // Delete
});




Route::get('/fetch-subcat/{cat_id}', [ManageCategories::class, 'fetch_subcat']);
Route::get('/fetch-brand/{cat_id}', [ManageCategories::class, 'fetch_brand']);
Route::get('/fetch-model/{cat_id}', [ManageCategories::class, 'fetch_model']);

Route::get('/api/check-following/{userId}', [UserController::class, 'checkFollowing']);
Route::post('/api/toggle-follow', [UserController::class, 'toggleFollow'])->middleware('usersession');


Route::post('/broadcasting/auth', function (Illuminate\Http\Request $request) {
    return Broadcast::auth($request);
})->middleware('usersession');

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
Route::get('/{location}/{slug}/{id}', [AdvertController::class, 'advert'])
     ->where('location', '[A-Za-z0-9\-]+');

Route::any('/{location}/{slug}', [SearchFilter::class, 'location_router'])
    ->where([
        'location' => '[a-zA-Z0-9\-]+',
        'slug' => '[a-zA-Z0-9\-]+',
    ]);


