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

        // This is required for broadcasting to work
        $user = \App\Models\User::find($request->session()->get('user_id'));
        if ($user) {
            Auth::login($user); // this makes auth()->user() work
        }

        return $next($request);
    }
}
