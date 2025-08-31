<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckUserSession
{
    public function handle(Request $request, Closure $next): Response
    {
        // ✅ If session already exists, continue
        if ($request->session()->has('user_id')) {
            return $next($request);
        }

        // ✅ If no session, try auto-login via cookie
        if ($request->hasCookie('remember_login')) {
            $token = $request->cookie('remember_login');

            $user = DB::table('users')
                ->where('remember_token', hash('sha256', $token))
                ->first();

            if ($user) {
                // Restore session
                $request->session()->put('user_id', $user->user_id);
                $request->session()->put('name', $user->name);

                // Refresh cookie expiry (rolling 30 days)
                cookie()->queue(cookie(
                    'remember_login',
                    $token,
                    60 * 24 * 30, // 30 days
                    '/',
                    null,
                    false, // set to true if using HTTPS
                    true   // httpOnly
                ));

                return $next($request);
            }
        }

        // ✅ If everything fails → force login
        $request->session()->put('url.intended', $request->fullUrl());
        return redirect('/login');
    }
}
