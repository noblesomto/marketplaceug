<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SearchFilter;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserProfile;
use App\Http\Controllers\BlockUser;
use App\Http\Controllers\UserManageAdverts;
use App\Http\Controllers\UserManageBoost;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdvertController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PaystackController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ShipperController;
use App\Http\Controllers\Admin\AdminAccount;
use App\Http\Controllers\Admin\ManageAdverts;
use App\Http\Controllers\Admin\ManageBoost;
use App\Http\Controllers\Admin\ManageCategories;
use App\Http\Controllers\Admin\ManageShipping;
use App\Http\Controllers\Admin\ManageUsers;
use App\Http\Controllers\Admin\ManageAdvertising;
use App\Http\Controllers\Admin\ManagePayments;
use App\Http\Controllers\Admin\ManageAdminUsers;
use App\Http\Controllers\Admin\ManageBlog;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\AdminBoostTypeController;
use App\Http\Controllers\Admin\AdminBoostDurationController;
use App\Http\Controllers\Admin\CategoryUIAdminController;
use Illuminate\Support\Facades\Broadcast;



Route::get('/', [AdvertController::class, 'index'])->name('home');
Route::get('/page', [PageController::class, 'page'])->name('page');
Route::get('/about-us', [PageController::class, 'about'])->name('about');
Route::get('/career', [PageController::class, 'career'])->name('career');
Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('privacy.policy');
Route::get('/cookie-policy', [PageController::class, 'cookie'])->name('cookie.policy');
Route::get('/billing-policy', [PageController::class, 'billing'])->name('billing.policy');
Route::get('/copyright-policy', [PageController::class, 'copyright'])->name('copyright.policy');
Route::get('/dmca-policy', [PageController::class, 'dmca'])->name('dmca.policy');
Route::get('/safety-tips', [PageController::class, 'safety'])->name('safety.tips');
Route::get('/sell-online', [PageController::class, 'sell_online'])->name('sell.online');
Route::get('/our-terms', [PageController::class, 'terms'])->name('terms');
Route::get('/payments-refunds', [PageController::class, 'payments_refunds'])->name('payments.refunds');
Route::get('/how-it-works', [PageController::class, 'how_it_works'])->name('how.it.works');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');
Route::get('/blog', [PageController::class, 'blog'])->name('blog');
Route::get('/blog/{slug}', [PageController::class, 'blog_details'])->name('blog.details');
Route::get('/advertise-with-us', [PageController::class, 'advertise'])->name('advertise');
Route::match(['GET', 'POST'], '/contact-us', [PageController::class, 'contact'])->name('contact');
Route::get('/shipping', [PageController::class, 'shipping'])->name('shipping');
Route::get('/email', [PageController::class, 'email'])->name('email');

Route::get('/robots.txt', [RobotsController::class, 'index']);


// Social Login
Route::get('auth/{provider}', [AccountController::class, 'redirectToProvider']);
Route::get('auth/{provider}/callback', [AccountController::class, 'handleProviderCallback']);


//Account Section
// ✅ SECURITY: Rate limit login attempts (5 per minute to prevent brute force)
Route::match(['GET', 'POST'], '/login', [AccountController::class, 'login'])
    ->name('login')
    ->middleware('throttle:5,1');

Route::match(['GET', 'POST'], '/register', [AccountController::class, 'register'])->name('register');
Route::get('/verifyaccount/{id}/{token}', [AccountController::class, 'verifyaccount'])->name('verify.account');
Route::get('/resend-email', [AccountController::class, 'resend_email'])->name('activation.resend');

// ✅ SECURITY: Rate limit OTP authentication (10 per minute)
Route::match(['GET', 'POST'], '/authenticate', [AccountController::class, 'authenticate'])
    ->name('authenticate')
    ->middleware('throttle:10,1');

Route::post('/resend-otp', [AccountController::class, 'resend_otp'])->name('resend.otp');
Route::match(['GET', 'POST'], '/forgot-password', [AccountController::class, 'forgot_password'])->name('forgot.password');
Route::match(['GET', 'POST'], '/reset-password/{id}/{token}', [AccountController::class, 'reset_password'])->name('reset.password');
Route::match(['GET', 'POST'], '/shipper', [AccountController::class, 'shipper'])->name('shipper.login');


//Admin Login
Route::match(['GET', 'POST'], '/admin', [AdminAccount::class, 'adminlogin'])->name('admin.login');
Route::match(['GET', 'POST'], '/admin/forgot-password', [AdminAccount::class, 'forgotPassword'])->name('admin.forgot.password');
Route::match(['GET', 'POST'], '/admin/reset-password/{admin_id}/{token}', [AdminAccount::class, 'resetPassword'])->name('admin.reset.password');

//Adverts
Route::get('/listings', [AdvertController::class, 'adverts'])->name('listings');
Route::get('/load-more-ads-desktop', [AdvertController::class, 'loadMoreAds'])->name('load.more.ads.desktop');
Route::get('/load-more-ads-mobile', [AdvertController::class, 'loadMoreAdsMobile'])->name('load.more.ads.mobile');
Route::get('/all-categories', [AdvertController::class, 'all_categories'])->name('all.categories');
Route::get('/category/{category_slug}', [AdvertController::class, 'category'])->name('category');
Route::get('/category/all-{slug}', [AdvertController::class, 'all_category'])->name('category.all');
Route::get('/category/{category_slug}/{subcat_slug}', [AdvertController::class, 'sub_category'])->name('subcategory');
Route::get('/category/{category_slug}/{subcat_slug}/all-{slug}', [AdvertController::class, 'all_subcat'])->name('subcategory.all');
Route::get('/category/{category_slug}/{subcat_slug}/{brand_slug}', [AdvertController::class, 'brand'])->name('brand');



Route::get('/related/{ad_id}', [AdvertController::class, 'related'])->name('related.ads');
Route::get('/related/{ad_id}/load-more', [AdvertController::class, 'relatedLoadMore'])->name('related.ads.loadMore');
Route::get('/buy-direct/{id}', [AdvertController::class, 'buy_direct'])->name('buy.direct');
Route::post('/calculate-shipping/{id}', [AdvertController::class, 'calculate_shipping'])->name('calculate.shipping');
Route::get('/buy-direct-payment/{id}', [AdvertController::class, 'buy_direct_payment'])->name('buy.direct.payment');
Route::get('/seller/{id}', function() {  return redirect('/'); })->name('seller.redirect');

Route::get('/seller/{name}/{id}', [AdvertController::class, 'seller'])->name('seller');
Route::get('/seller/{name}/{id}/load-more', [AdvertController::class, 'loadMoreSellerAds'])->name('seller.ads.loadMore');
Route::match(['GET', 'POST'], '/report-ad/{id}', [AdvertController::class, 'report_advert'])->middleware('usersession')->name('report.ad');
Route::match(['GET', 'POST'], '/apply/{id}', [AdvertController::class, 'apply_job'])->middleware('usersession')->name('apply.job');

//Search and Filter
Route::match(['GET', 'POST'], '/search', [SearchFilter::class, 'search'])->name('search');
Route::match(['GET', 'POST'], '/filter/adverts', [SearchFilter::class, 'filter'])->name('filter.adverts');
Route::match(['GET', 'POST'], '/filter/sellers', [SearchFilter::class, 'filterBySeller'])->name('filter.sellers');
Route::match(['GET', 'POST'], '/filter/buydirect', [SearchFilter::class, 'filterByBuydirect'])->name('filter.buydirect');
Route::post('/filter/car-details', [SearchFilter::class, 'filterByCarDetails'])->name('filter.car.details');
Route::post('/filter/phone-details', [SearchFilter::class, 'filterByPhoneDetails'])->name('filter.phone.details');


// Get State and Locations
Route::get('/states', [LocationController::class, 'index']);
Route::get('/get-gig/{state_id}', [LocationController::class, 'getGIG']);
Route::post('/shipping-cost', [LocationController::class, 'getAgilityShippingCost']);

//Buy Direct Paystack
Route::post('/pay', [PaystackController::class, 'initialize'])->name('paystack.pay')->middleware('usersession');
Route::get('/payment/callback', [PaystackController::class, 'callback'])->name('paystack.callback')->middleware('usersession');


//Mobile Category
Route::get('/m-category/{id}/{slug}', [AdvertController::class, 'mobile_category'])->name('mobile.category');

//Messages
Route::post('/messages', [MessageController::class, 'sendMessage'])->middleware('usersession');
Route::get('/my-messages/{id}', [MessageController::class, 'fetchMyMessages'])->middleware('usersession');

Route::get('/chat-buyer/{user_id}/{id}', [MessageController::class, 'chat_buyer'])->name('chat.buyer');

Route::get('/chat/{advertId}/{receiverId}', [MessageController::class, 'showMessages'])->name('chat.show')->middleware('usersession');
Route::post('/chat/{advertId}/{receiverId}', [MessageController::class, 'sendMessage'])->name('chat.sendMessage')->middleware('usersession');
Route::get('/unread-messages-count', [MessageController::class, 'countUnreadMessages']);
Route::get('/payment/mark-received/{id}', [MessageController::class, 'mark_received'])->middleware('usersession');

//User Dashboard Section
Route::get('/user/index', [UserController::class, 'index'])->name('user.index')->middleware('usersession');
Route::get('/user/my-ads', [UserController::class, 'my_ads'])->name('user.my.ads')->middleware('usersession');
Route::get('/user/boosted', [UserController::class, 'boosted_ads'])->name('user.boosted')->middleware('usersession');
Route::get('/user/payment', [UserController::class, 'payments'])->name('user.payments')->middleware('usersession');
Route::post('/user/confirm-delivery/{id}', [UserController::class, 'confirmDelivery'])->name('user.confirm.delivery')->middleware('usersession');
Route::get('/user/ad-shipping/{id}', [UserController::class, 'ad_shipping'])->name('user.ad.shipping')->middleware('usersession');
Route::post('/user/update-shipping/{id}', [UserController::class, 'update_shipping'])->name('user.update.shipping')->middleware('usersession');
Route::get('/user/ad-status/{status}/{id}', [UserController::class, 'ad_status'])->name('user.ad.status')->middleware('usersession');
Route::get('/user/category', [UserController::class, 'category'])->name('user.category')->middleware('usersession');
Route::get('/user/orders', [UserController::class, 'orders'])->name('user.orders')->middleware('usersession');
Route::get('/user/messages', [UserController::class, 'messages'])->name('user.messages')->middleware('usersession');
Route::get('/user/archived-messages', [UserController::class, 'archivedMessages'])->name('user.archived.messages')->middleware('usersession');
Route::get('/user/feedbacks', [UserController::class, 'feedbacks'])->name('user.feedbacks')->middleware('usersession');
Route::post('/user/add-wishlist/{id}', [UserController::class, 'add_wishlist'])->name('user.add.wishlist')->middleware('usersession');
Route::get('/user/favourites', [UserController::class, 'favourites'])->name('user.favourites')->middleware('usersession');
Route::get('/user/mark-sold/{id}', [UserController::class, 'advert_sold'])->name('user.mark.sold')->middleware('usersession');
Route::post('/reviews/feedbacks/{id}', [UserController::class, 'submit_feedback'])->name('reviews.feedback')->middleware('usersession');
Route::get('/reviews/seller/{id}', [UserController::class, 'reviews_seller'])->name('reviews.seller');
Route::get('/user/notifications', [UserController::class, 'notifications'])->name('user.notifications')->middleware('usersession');
Route::delete('/user/delete-notification/{id}', [UserController::class, 'deleteNotification'])
    ->name('user.delete.notification')->middleware('usersession');
Route::match(['GET', 'POST'], 'report-user/{id}', [UserController::class, 'report_user'])->name('report.user')->middleware('usersession');
Route::get('/user/ads/load-more', [UserController::class, 'loadMoreUserAds'])->name('user.ads.loadMore')->middleware('usersession');


//Block User
Route::middleware('usersession')->group(function () {
    Route::post('user/block', [BlockUser::class, 'block']);
    Route::delete('user/unblock', [BlockUser::class, 'unblock']);
    Route::get('user/blocked-users', [BlockUser::class, 'blockedList']);

    // Archive routes
    Route::post('/messages/archive', [MessageController::class, 'archive'])->name('messages.archive');
    Route::delete('/messages/unarchive', [MessageController::class, 'unarchive'])->name('messages.unarchive');
    Route::get('/messages/archived', [MessageController::class, 'archivedList'])->name('messages.archived');

});

//User Manage Ads
Route::match(['GET', 'POST'], '/user/post-ad', [UserManageAdverts::class, 'post_ad'])->name('post.ad')->middleware('usersession');
Route::get('/user/edit-ad/{id}', [UserManageAdverts::class, 'edit_ad'])->name('edit.ad')->middleware('usersession');
Route::post('/user/edit-ad/{id}', [UserManageAdverts::class, 'edit_ad'])->name('update.ad')->middleware('usersession');
Route::delete('/user/delete-ad/{id}', [UserManageAdverts::class, 'delete_ad'])->name('delete.ad')->middleware('usersession');
//User Manage Boost
Route::match(['GET', 'POST'], '/user/post-boost-ad/{id}', [UserManageBoost::class, 'post_boost_ad'])->name('post.boost.ad')->middleware('usersession');
Route::match(['GET', 'POST'], '/user/make-payment/{id}', [UserManageBoost::class, 'make_payment'])->name('make.payment')->middleware('usersession');
Route::match(['GET', 'POST'], '/user/boost-ad/{id}', [UserManageBoost::class, 'boost_ad'])->name('boost.ad')->middleware('usersession');
Route::get('/user/boosted-ad/{id}', [UserManageBoost::class, 'boosted_ad'])->name('boosted.ad')->middleware('usersession');
Route::post('/boost/upload-proof', [UserManageBoost::class, 'upload_proof'])->name('boost.upload.proof')->middleware('usersession');

//User Profile
Route::get('/user/about-account', [UserProfile::class, 'about_account'])->name('user.about.account')->middleware('usersession');
Route::match(['GET', 'POST'], '/user/profile', [UserProfile::class, 'profile'])->name('user.profile')->middleware('usersession');
Route::get('/user/settings', [UserProfile::class, 'settings'])->name('user.settings')->middleware('usersession');
Route::match(['GET', 'POST'], '/user/profile-address', [UserProfile::class, 'profile_address'])->name('user.profile.address')->middleware('usersession');
Route::match(['GET', 'POST'], '/user/profile-info', [UserProfile::class, 'profile_info'])->name('user.profile.info')->middleware('usersession');
Route::get('/user/get-verified', [UserProfile::class, 'get_verified'])->name('user.get.verified')->middleware('usersession');
Route::post('/user/submit-verification', [UserProfile::class, 'submit_verification'])->name('user.submit.verification')->middleware('usersession');
Route::get('/user/payments', [UserProfile::class, 'payment_info'])->name('user.payment.info')->middleware('usersession');
Route::match(['GET', 'POST'], '/user/profile-phone', [UserProfile::class, 'profile_phone'])->name('user.profile.phone')->middleware('usersession');
Route::match(['GET', 'POST'], '/user/change-password', [UserProfile::class, 'change_password'])->name('user.change.password')->middleware('usersession');
Route::match(['GET', 'POST'], '/user/disable-account', [UserProfile::class, 'disable_account'])->name('user.disable.account')->middleware('usersession');
Route::get('/user/logout', [UserProfile::class, 'logout'])->name('user.logout')->middleware('usersession');
Route::match(['GET', 'POST'], '/user/profile-notification', [UserProfile::class, 'profile_notification'])->name('user.profile.notification')->middleware('usersession');
Route::post('/update-notifications', [UserProfile::class, 'updateNotifications'])
    ->middleware('usersession')
    ->name('user.update-notifications');
Route::get('/user/myads/load-more', [UserProfile::class, 'loadMoreUserAds'])->name('user.myads.loadMore');

//Chat Section
Route::get('/user/chat-buyer/{user}/{id}', [UserController::class, 'chat_buyer'])->name('user.chat.buyer')->middleware('usersession');
Route::get('/chat-seller/{user}/{id}', [UserController::class, 'chat_seller'])->name('chat.seller');

//Paystack User Boost Add
Route::post('post-boost/pay', [PaystackController::class, 'initialize_post_boost'])->name('post-boost.pay')->middleware('usersession');
Route::post('boost/pay', [PaystackController::class, 'initialize_boost'])->name('boost.pay')->middleware('usersession');
Route::post('boost/make-payment', [PaystackController::class, 'retry_boost_payment'])->name('boost.retry-payment')->middleware('usersession');
Route::get('/boost/callback', [PaystackController::class, 'callback_boost'])->name('boost.callback')->middleware('usersession');
Route::get('/payment-success', [PaystackController::class, 'success'])->name('payment.success');
Route::get('/payment-failed', [PaystackController::class, 'failed'])->name('payment.failed');

//Admin Index
Route::get('/admin/index', [AdminController::class, 'index'])->middleware('adminsession')->name('admin.dashboard');
Route::get('/admin/logout', [AdminController::class, 'logout'])->middleware('adminsession')->name('admin.logout');


Route::middleware(['adminsession','adminrole:Finance,super_admin'])->group(function () {
    // Manage Payments
    Route::get('/admin/completed-payments', [ManagePayments::class, 'completed_payments'])->name('admin.completed.payments');
    Route::get('/admin/pending-payments', [ManagePayments::class, 'pending_payments'])->name('admin.pending.payments');
    Route::post('/admin/update-payment/{id}', [ManagePayments::class, 'update_payment'])->name('admin.update.payment');
    Route::post('/admin/confirm-payment/{id}', [ManagePayments::class, 'confirm_payment'])->name('admin.confirm.payment');

    // Manage Settlements
    Route::get('/admin/pending-settlements', [ManagePayments::class, 'pending_settlements'])->name('admin.pending.settlements');
    Route::get('/admin/completed-settlements', [ManagePayments::class, 'completed_settlements'])->name('admin.completed.settlements');
    Route::get('/admin/confirm-settlement/{id}', [ManagePayments::class, 'confirm_settlement'])->name('admin.confirm.settlement');
    Route::get('/admin/payout/{id}', [ManagePayments::class, 'sendPayout'])->name('payout.transfer');
});


/*
|--------------------------------------------------------------------------
| Admin Routes with Spatie Permission System
|--------------------------------------------------------------------------
*/

Route::middleware(['adminsession'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Advert Management Routes
    | Accessible by: Advert_manager, Customer_care, super_admin
    |--------------------------------------------------------------------------
    */
    Route::middleware(['admin.permission:create_advert,update_advert,delete_advert,view_adverts'])->group(function () {
        // Advertising
        Route::match(['GET', 'POST'], '/admin/create-advert', [ManageAdvertising::class, 'create_advert'])->name('admin.create.advert');
        Route::delete('/admin/delete-advert/{id}', [ManageAdvertising::class, 'delete_advert'])->name('admin.delete.advert');
        Route::put('/admin/update-advert/{id}', [ManageAdvertising::class, 'updateAdvert'])->name('admin.update.advert');

        // Manage Adverts
        Route::get('/admin/active-adverts', [ManageAdverts::class, 'active_adverts'])->name('admin.active.adverts');
        Route::get('/admin/disabled-adverts', [ManageAdverts::class, 'disabled_adverts'])->name('admin.disabled.adverts');
        Route::get('/admin/sold-adverts', [ManageAdverts::class, 'sold_adverts'])->name('admin.sold.adverts');
        Route::get('/admin/advert-status/{id}/{status}', [ManageAdverts::class, 'advert_status'])->name('admin.advert.status');
        Route::get('/admin/sold-status/{id}/{status}', [ManageAdverts::class, 'sold_status'])->name('admin.sold.status');
        Route::get('/admin/redirect-status/{id}/{status}', [ManageAdverts::class, 'redirect_status'])->name('admin.redirect.status');
        Route::match(['GET', 'POST'], '/admin/edit-ad/{id}', [ManageAdverts::class, 'edit_advert'])->name('admin.edit.advert');
        Route::delete('/admin/delete-ad/{id}', [ManageAdverts::class, 'delete_advert'])->name('admin.delete.ad');
    });

    /*
    |--------------------------------------------------------------------------
    | Boost Management Routes
    | Accessible by: Advert_manager, Customer_care, super_admin
    |--------------------------------------------------------------------------
    */
    Route::middleware(['admin.permission:view_active_boosts,manage_boost_status,manage_boost_payment'])->group(function () {
        Route::get('/boost/active', [ManageBoost::class, 'active'])->name('admin.boost.active');
        Route::get('/boost/completed', [ManageBoost::class, 'completed'])->name('admin.boost.completed');
        Route::get('/boost/unpaid', [ManageBoost::class, 'unpaid'])->name('admin.boost.unpaid');
        Route::get('/boost/paid', [ManageBoost::class, 'paid'])->name('admin.boost.paid');
        Route::post('/boost/status/{id}/{status}', [ManageBoost::class, 'status'])->name('admin.boost.status');
        Route::post('/boost/payment-status/{id}/{status}', [ManageBoost::class, 'payment'])->name('admin.boost.payment.status');
    });

    /*
    |--------------------------------------------------------------------------
    | Boost Settings Routes
    | Accessible by: super_admin
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin/boost-settings')->middleware(['admin.permission:manage_settings'])->group(function () {
        // Boost Types
        Route::get('/types', [AdminBoostTypeController::class, 'index'])->name('admin.boost-types.index');
        Route::post('/types', [AdminBoostTypeController::class, 'store'])->name('admin.boost-types.store');
        Route::put('/types/{id}', [AdminBoostTypeController::class, 'update'])->name('admin.boost-types.update');
        Route::delete('/types/{id}', [AdminBoostTypeController::class, 'destroy'])->name('admin.boost-types.destroy');
        Route::post('/types/{id}/toggle', [AdminBoostTypeController::class, 'toggleStatus'])->name('admin.boost-types.toggle');

        // Boost Durations
        Route::get('/durations', [AdminBoostDurationController::class, 'index'])->name('admin.boost-durations.index');
        Route::post('/durations', [AdminBoostDurationController::class, 'store'])->name('admin.boost-durations.store');
        Route::put('/durations/{id}', [AdminBoostDurationController::class, 'update'])->name('admin.boost-durations.update');
        Route::delete('/durations/{id}', [AdminBoostDurationController::class, 'destroy'])->name('admin.boost-durations.destroy');
        Route::post('/durations/{id}/toggle', [AdminBoostDurationController::class, 'toggleStatus'])->name('admin.boost-durations.toggle');
    });

    /*
    |--------------------------------------------------------------------------
    | Category Management Routes
    | Accessible by: Advert_manager, Customer_care, super_admin
    |--------------------------------------------------------------------------
    */
    Route::middleware(['admin.permission:manage_categories'])->group(function () {
        Route::match(['GET', 'POST'], '/admin/category', [ManageCategories::class, 'category'])->name('admin.category');
        Route::delete('/admin/delete-category/{id}', [ManageCategories::class, 'delete_category'])->name('admin.delete.category');
        Route::match(['GET', 'POST'], '/admin/sub-category/{id}', [ManageCategories::class, 'sub_category'])->name('admin.sub.category');
        Route::delete('/admin/delete-subcategory/{id}/{cat}', [ManageCategories::class, 'delete_subcategory'])->name('admin.delete.subcategory');
        Route::match(['GET', 'POST'], '/admin/brand/{id}', [ManageCategories::class, 'brand'])->name('admin.brand');
        Route::delete('/admin/delete-brand/{id}/{cat}', [ManageCategories::class, 'delete_brand'])->name('admin.delete.brand');
        Route::match(['GET', 'POST'], '/admin/model/{id}', [ManageCategories::class, 'model'])->name('admin.model');
        Route::delete('/admin/delete-model/{id}/{cat}', [ManageCategories::class, 'delete_model'])->name('admin.delete.model');
    });

    /*
    |--------------------------------------------------------------------------
    | Category UI Configuration Routes
    | Accessible by: Advert_manager, super_admin
    | Database-driven show/hide rules for Post Ad and Edit Ad forms
    |--------------------------------------------------------------------------
    */
    Route::middleware(['admin.permission:manage_categories'])->prefix('admin/category-ui')->name('admin.category-ui.')->group(function () {
        Route::get('/', [CategoryUIAdminController::class, 'index'])->name('index');

        // Category UI Config
        Route::get('/category/{id}/edit', [CategoryUIAdminController::class, 'editCategory'])->name('edit-category');
        Route::post('/category/{id}', [CategoryUIAdminController::class, 'updateCategory'])->name('update-category');
        Route::delete('/category/{id}', [CategoryUIAdminController::class, 'deleteCategory'])->name('delete-category');

        // Subcategory UI Config
        Route::get('/subcategory/{id}/edit', [CategoryUIAdminController::class, 'editSubcategory'])->name('edit-subcategory');
        Route::post('/subcategory/{id}', [CategoryUIAdminController::class, 'updateSubcategory'])->name('update-subcategory');
        Route::delete('/subcategory/{id}', [CategoryUIAdminController::class, 'deleteSubcategory'])->name('delete-subcategory');
    });

    /*
    |--------------------------------------------------------------------------
    | Resolution Routes
    | Accessible by: Resolution, super_admin
    |--------------------------------------------------------------------------
    */
    Route::middleware(['admin.permission:view_reports,manage_report_status'])->group(function () {
        Route::get('/admin/view-reports', [AdminController::class, 'view_reports'])->name('admin.view.reports');
        Route::get('/admin/report-status/{id}/{status}', [AdminController::class, 'report_status'])->name('admin.report.status');
        Route::delete('/admin/delete-complaint/{id}', [AdminController::class, 'deleteComplaint'])->name('admin.delete.complaint');
    });

    /*
    |--------------------------------------------------------------------------
    | User Management Routes
    | Accessible by: Customer_care, super_admin
    |--------------------------------------------------------------------------
    */
    Route::middleware(['admin.permission:view_users,manage_user_status,verify_users'])->group(function () {
        Route::get('/admin/active-users', [ManageUsers::class, 'active_users'])->name('admin.active.users');
        Route::any('/admin/user-status/{id}/{status}', [ManageUsers::class, 'user_status'])->name('admin.user.status');
        Route::any('/admin/disable-status/{id}/{status}', [ManageUsers::class, 'disable_status'])->name('admin.disable.status');
        Route::get('/admin/unverified-users', [ManageUsers::class, 'unverified_users'])->name('admin.unverified.users');
        Route::get('/admin/disabled-users', [ManageUsers::class, 'disabled_users'])->name('admin.disabled.users');
        Route::get('/admin/view-user/{id}', [ManageUsers::class, 'view_user'])->name('admin.view.user');
        Route::get('/admin/user-verification', [ManageUsers::class, 'user_verification'])->name('admin.user.verification');
        Route::get('/admin/verify-status/{id}/{status}/{verify}', [ManageUsers::class, 'verify_status'])->name('admin.verify.status');
        Route::get('/admin/users/search', [ManageUsers::class, 'search'])->name('admin.users.search');
        Route::delete('/admin/delete-user/{id}', [ManageUsers::class, 'deleteUser'])->name('admin.delete.user');
    });

    /*
    |--------------------------------------------------------------------------
    | Shipping Management Routes
    | Accessible by: Customer_care, super_admin
    |--------------------------------------------------------------------------
    */
    Route::middleware(['admin.permission:manage_shipping'])->group(function () {
        Route::get('/settings/setup-shipping', [SettingController::class, 'shipping'])->name('admin.setup.shipping');
        Route::put('/settings/update-shipping/{id}', [SettingController::class, 'shipping'])->name('admin.update.shipping');
    });

    /*
    |--------------------------------------------------------------------------
    | Super Admin Only Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['adminrole:super_admin'])->group(function () {
        // Roles & Permissions Management
        Route::prefix('settings/roles')->group(function () {
            Route::get('/', [RolePermissionController::class, 'index'])->name('admin.roles.index');
            Route::post('/roles', [RolePermissionController::class, 'storeRole'])->name('admin.roles.store');
            Route::post('/permissions', [RolePermissionController::class, 'storePermission'])->name('admin.permissions.store');
            Route::post('/roles/{role}/assign', [RolePermissionController::class, 'assignPermission'])->name('admin.roles.assign');
            Route::post('/admins/{admin}/assign', [RolePermissionController::class, 'assignRoleToAdmin'])->name('admin.admins.assign');
        });

        // GIG Logistics
        Route::match(['GET', 'POST'], '/settings/gig-locations', [SettingController::class, 'gig_locations'])->name('admin.gig.locations');
        Route::delete('/settings/delete-gig-location/{id}', [SettingController::class, 'delete_gig_location'])->name('admin.delete.gig.location');
        Route::post('/settings/update-gig-location', [SettingController::class, 'updateGigLocation'])->name('update.gig.location');

        // Manage Admin Users
        Route::prefix('settings/manage-admins')->group(function () {
            Route::get('/', [ManageAdminUsers::class, 'index']);
            Route::post('/', [ManageAdminUsers::class, 'store']);
            Route::put('/{id}', [ManageAdminUsers::class, 'update']);
            Route::delete('/{id}', [ManageAdminUsers::class, 'destroy']);
        });
    });
});

/*
|--------------------------------------------------------------------------
| Shipper Routes (Separate Authentication)
|--------------------------------------------------------------------------
*/
Route::middleware(['shipsession'])->group(function () {
    Route::get('/shipper/index', [ShipperController::class, 'index'])->name('shipper.index');
    Route::get('/shipper/get-shipping', [ShipperController::class, 'get_shipping'])->name('shipper.get.shipping');
    Route::get('/shipper/order-details/{id}', [ShipperController::class, 'order_details'])->name('shipper.order.details');
    Route::post('/shipper/update-shipping/{id}', [ShipperController::class, 'update_shipping'])->name('shipper.update.shipping');
});

Route::resource('/admin/blogs', ManageBlog::class);
Route::post('/tinymce/upload', [ManageBlog::class, 'upload'])->name('tinymce.upload');
Route::post('/blogs/{blog}/toggle-status', [ManageBlog::class, 'toggleStatus'])->name('blogs.toggleStatus');


Route::get('/fetch-subcat/{cat_id}', [ManageCategories::class, 'fetch_subcat']);
Route::get('/fetch-brand/{cat_id}', [ManageCategories::class, 'fetch_brand']);
Route::get('/fetch-model/{cat_id}', [ManageCategories::class, 'fetch_model']);

Route::get('/api/check-following/{userId}', [UserController::class, 'checkFollowing']);
Route::post('/api/toggle-follow', [UserController::class, 'toggleFollow'])->middleware('usersession');

Route::get('/load-more-ads', [AdvertController::class, 'loadMoreAdverts'])->name('adverts.loadMore');
Route::get('/load-ads-location', [AdvertController::class, 'loadMoreLocation'])->name('location.loadMore');
Route::get('/adverts/load-more', [SearchFilter::class, 'loadMore'])->name('search.loadMore');


Route::post('/broadcasting/auth', function (Illuminate\Http\Request $request) {
    return Broadcast::auth($request);
})->middleware('usersession');


// 3-segment advert page
Route::get('/{location}/{slug}/{id}', [AdvertController::class, 'advert'])
    ->where('location', '[A-Za-z0-9\-\s]+')  // Added \s for spaces
    ->where('slug', '[A-Za-z0-9\-]+')
    ->where('id', '[0-9]+')
    ->name('advert');

// 2-segment location filters
Route::get('/{location}/{slug}', [SearchFilter::class, 'location_router'])
    ->where('location', '[A-Za-z0-9\-\s]+')  // Added \s for spaces
    ->where('slug', '[A-Za-z0-9\-]+')
    ->name('location.router');

// 1-segment = location (LGA)
Route::get('/{location}', [AdvertController::class, 'location'])
    ->where('location', '[A-Za-z0-9\-\s]+')
    ->name('location');  // Added \s for spaces





//dd(adminUser());
