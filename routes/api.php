<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

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
