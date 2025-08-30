<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Controllers\AccountController; // we’ll call the static helper here

class CheckUserSession
{
    public function handle(Request $request, Closure $next)
    {
        // If no session, try to auto-login from cookie
        if (!$request->session()->has('user_id')) {
            $user = AccountController::autoLoginFromCookie($request);

            if (!$user) {
                // Save intended URL and redirect to login
                $request->session()->put('url.intended', $request->fullUrl());
                return redirect('/login');
            }
        }

        return $next($request);
    }
}
