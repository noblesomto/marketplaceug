<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AdvertController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\PaystackController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserManageAdverts;
use App\Http\Controllers\Api\UserProfile;


Route::post('/register', [Api\AccountController::class, 'register']);
Route::post('/login', [Api\AccountController::class, 'login']);
Route::post('/verify-otp', [Api\AccountController::class, 'verifyOTP']);
Route::post('/resend-otp', [Api\AccountController::class, 'resendOTP']);
Route::get('/verify/{email}/{token}', [Api\AccountController::class, 'verifyAccount']);
Route::post('/resend-verification', [Api\AccountController::class, 'resendVerification']);
Route::post('/forgot-password', [Api\AccountController::class, 'forgotPassword']);
Route::post('/reset-password/{user_id}/{token}', [Api\AccountController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [Api\AccountController::class, 'user']);
    Route::post('/logout', [Api\AccountController::class, 'logout']);
    Route::delete('/trusted-device/{device_id}', [Api\AccountController::class, 'removeTrustedDevice']);
});



Route::get('/adverts', [AdvertController::class, 'index']);
Route::get('/adverts/featured', [AdvertController::class, 'featuredAdverts']);
Route::get('/adverts/{id}', [AdvertController::class, 'show']);
Route::get('/adverts/seller/{seller_id}', [AdvertController::class, 'sellerAdverts']);
Route::get('/categories', [AdvertController::class, 'categories']);
Route::get('/categories/{category_slug}', [AdvertController::class, 'categoryAdverts']);
Route::get('/categories/{category_slug}/{subcat_slug}', [AdvertController::class, 'subcategoryAdverts']);
Route::get('/brands/{category_slug}/{subcat_slug}/{brand_slug}', [AdvertController::class, 'brandAdverts']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/adverts/{id}/report', [AdvertController::class, 'reportAdvert']);
    Route::post('/adverts/{id}/apply', [AdvertController::class, 'applyJob']);
});


// Location routes
Route::get('/locations/states', [LocationController::class, 'getStates']);
Route::get('/locations/states/{state_id}/cities', [LocationController::class, 'getCitiesByState']);
Route::get('/locations/states/{state_id}/details', [LocationController::class, 'getStateWithCities']);
Route::get('/locations/cities', [LocationController::class, 'searchCities']);
Route::get('/locations/cities/{city_id}', [LocationController::class, 'getCity']);

// Shipping routes
Route::post('/shipping/calculate', [LocationController::class, 'calculateShippingCost']);


Route::middleware('auth:sanctum')->group(function () {
    // Messages
    Route::post('/messages', [MessageController::class, 'sendMessage']);
    Route::get('/messages/conversation/{advertId}/{receiverId}', [MessageController::class, 'getConversation']);
    Route::get('/messages/advert/{advertId}', [MessageController::class, 'getAdvertMessages']);
    Route::get('/messages/conversations', [MessageController::class, 'getUserConversations']);
    Route::get('/messages/unread/count', [MessageController::class, 'getUnreadCount']);
    Route::put('/messages/{messageId}/read', [MessageController::class, 'markAsRead']);
    Route::put('/messages/conversation/{advertId}/{userId}/read', [MessageController::class, 'markConversationAsRead']);

    // Payments
    Route::post('/payments/{paymentId}/mark-delivered', [MessageController::class, 'markAsDelivered']);
});


Route::middleware('auth:sanctum')->group(function () {
    // Payment routes
    Route::post('/payments/initialize', [PaystackController::class, 'initializePayment']);
    Route::post('/payments/initialize-boost', [PaystackController::class, 'initializeBoost']);
    Route::get('/payments/{paymentId}', [PaystackController::class, 'getPayment']);
    Route::get('/payments/user/{userId}', [PaystackController::class, 'getUserPayments']);
    Route::get('/boosts/user/{userId}', [PaystackController::class, 'getUserBoosts']);
});

// Public callback route (no auth required)
Route::post('/payments/callback', [PaystackController::class, 'handleCallback']);



// Search routes
Route::get('/search', [SearchController::class, 'search']);
Route::get('/search/location/{location}/{slug}', [SearchController::class, 'locationSearch']);
Route::get('/search/filters', [SearchController::class, 'getFilters']);
Route::get('/search/suggestions', [SearchController::class, 'getSuggestions']);

Route::middleware('auth:sanctum')->group(function () {
    // User dashboard
    Route::get('/user/dashboard', [UserController::class, 'dashboard']);
    Route::get('/user/categories', [UserController::class, 'categories']);

    // Messages
    Route::get('/user/messages/conversations', [UserController::class, 'conversations']);

    // Ads management
    Route::get('/user/ads', [UserController::class, 'myAds']);
    Route::patch('/user/ads/{adId}/status', [UserController::class, 'updateAdStatus']);
    Route::patch('/user/ads/{adId}/mark-sold', [UserController::class, 'markAsSold']);

    // Wishlist
    Route::get('/user/wishlist', [UserController::class, 'wishlist']);
    Route::post('/user/wishlist/{adId}', [UserController::class, 'addToWishlist']);
    Route::delete('/user/wishlist/{adId}', [UserController::class, 'removeFromWishlist']);

    // Payments
    Route::get('/user/payments', [UserController::class, 'payments']);
    Route::post('/user/payments/{paymentId}/confirm-delivery', [UserController::class, 'confirmDelivery']);
    Route::patch('/user/payments/{paymentId}/shipping-status', [UserController::class, 'updateShippingStatus']);

    // Feedbacks
    Route::get('/user/feedbacks', [UserController::class, 'feedbacks']);
    Route::post('/user/feedbacks/{sellerId}', [UserController::class, 'submitFeedback']);

    // Following
    Route::get('/user/following/check/{userId}', [UserController::class, 'checkFollowing']);
    Route::post('/user/following/toggle', [UserController::class, 'toggleFollow']);
});


Route::middleware('auth:sanctum')->group(function () {
    // Advert management routes
    Route::get('/adverts/categories/{categoryId}/subcategories', [UserManageAdverts::class, 'getSubcategories']);
    Route::get('/adverts/subcategories/{subcategoryId}/brands', [UserManageAdverts::class, 'getBrands']);
    Route::get('/adverts/brands/{brandId}/models', [UserManageAdverts::class, 'getModels']);
    Route::get('/adverts/create/data', [UserManageAdverts::class, 'getCreateData']);

    Route::post('/adverts', [UserManageAdverts::class, 'createAdvert']);
    Route::get('/adverts/{advertId}/edit', [UserManageAdverts::class, 'getAdvertForEdit']);
    Route::put('/adverts/{advertId}', [UserManageAdverts::class, 'updateAdvert']);
    Route::delete('/adverts/{advertId}', [UserManageAdverts::class, 'deleteAdvert']);

    Route::get('/adverts/{advertId}/boost', [UserManageAdverts::class, 'getBoostInfo']);
    Route::get('/adverts/{advertId}/boosted', [UserManageAdverts::class, 'getBoostedAdvert']);
});

// Public routes for category/brand/model data
Route::get('/adverts/categories/{categoryId}/subcategories', [UserManageAdverts::class, 'getSubcategories']);
Route::get('/adverts/subcategories/{subcategoryId}/brands', [UserManageAdverts::class, 'getBrands']);
Route::get('/adverts/brands/{brandId}/models', [UserManageAdverts::class, 'getModels']);


Route::middleware('auth:sanctum')->group(function () {
    // User profile routes
    Route::get('/user/profile', [UserProfile::class, 'getProfile']);
    Route::get('/user/settings', [UserProfile::class, 'getSettings']);
    Route::put('/user/profile/address', [UserProfile::class, 'updateAddress']);
    Route::put('/user/profile/phone', [UserProfile::class, 'updatePhone']);

    // Verification routes
    Route::get('/user/verification', [UserProfile::class, 'getVerificationStatus']);
    Route::post('/user/verification', [UserProfile::class, 'submitVerification']);

    // Payment info routes
    Route::get('/user/payment-info', [UserProfile::class, 'getPaymentInfo']);
    Route::put('/user/payment-info', [UserProfile::class, 'updatePaymentInfo']);

    // Security routes
    Route::put('/user/password', [UserProfile::class, 'changePassword']);
    Route::put('/user/notifications', [UserProfile::class, 'updateNotifications']);
    Route::delete('/user/account', [UserProfile::class, 'disableAccount']);
    Route::post('/user/logout', [UserProfile::class, 'logout']);
});



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Broadcast::routes(['middleware' => ['usersession']]);


Route::get('/unread-messages-count', function() {
    if (!Session::has('user_id')) {
        return response()->json(['count' => 0]);
    }

    $count = App\Models\Message::where('receiver_id', Session::get('user_id'))
                ->where('is_read', false)
                ->count();

    return response()->json(['count' => $count]);
})->middleware('web'); // Important: we need session access

