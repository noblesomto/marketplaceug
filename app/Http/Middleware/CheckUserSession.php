<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserSession
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->session()->has('user_id')) {
            // Use Laravel's intended URL mechanism
            $request->session()->put('url.intended', $request->fullUrl());
            return redirect('/login');
        }

        return $next($request);
    }
}
