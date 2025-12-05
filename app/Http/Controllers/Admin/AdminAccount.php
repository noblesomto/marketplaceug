<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\AdminLoginAttempts;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\HtmlString;
use App\Helpers\ContentHelper;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AdminAccount extends Controller
{
    public function adminlogin(Request $request)
{
    $title = "Admin Login - " . config('global.site_name');

    if ($request->isMethod('POST')) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        // Rate limiting
        $key = Str::lower($request->email) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::guard('admin')->attempt($credentials, $request->has('remember'))) {
            $request->session()->regenerate();
            RateLimiter::clear($key);

            // Log successful login
            AdminLoginAttempts::create([
                'email' => $request->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'successful' => true,
            ]);

            return redirect()
                ->route('admin.dashboard')
                ->with('success', 'Welcome back, ' . Auth::guard('admin')->user()->username);
        }

        // Log failed attempt
        AdminLoginAttempts::create([
            'email' => $request->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'successful' => false,
        ]);

        RateLimiter::hit($key, 60);

        return back()
            ->withInput($request->only('email'))
            ->with('error', 'Invalid email or password.');
    }

    return view('frontend.account.admin', compact('title'));
}
}
