<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckUserSession
{
    public function handle($request, Closure $next)
    {
        if (!$request->session()->has('user_id')) {
            // Save intended destination before redirect
            $request->session()->put('previous_url', $request->fullUrl());

            return redirect('/login');
        }

        return $next($request);
    }
}
