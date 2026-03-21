<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdvertController;
use App\Http\Controllers\SearchFilter;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Routes are split into focused files and loaded here in order.
| Wildcard catch-all routes are registered last so they never swallow
| more specific routes defined in the files above.
*/

require __DIR__.'/auth.php';
require __DIR__.'/public.php';
require __DIR__.'/user.php';
require __DIR__.'/admin.php';
require __DIR__.'/shipper.php';

/*
|--------------------------------------------------------------------------
| Wildcard Catch-All Routes (must remain last)
|--------------------------------------------------------------------------
*/

// 3-segment: /{location}/{slug}/{id} → advert detail page
Route::get('/{location}/{slug}/{id}', [AdvertController::class, 'advert'])
    ->where('location', '[A-Za-z0-9\-\s]+')
    ->where('slug', '[A-Za-z0-9\-]+')
    ->where('id', '[0-9]+')
    ->name('advert');

// 2-segment: /{location}/{slug} → location + category filter
Route::get('/{location}/{slug}', [SearchFilter::class, 'location_router'])
    ->where('location', '[A-Za-z0-9\-\s]+')
    ->where('slug', '[A-Za-z0-9\-]+')
    ->name('location.router');

// 1-segment: /{location} → location page
Route::get('/{location}', [AdvertController::class, 'location'])
    ->where('location', '[A-Za-z0-9\-\s]+')
    ->name('location');
