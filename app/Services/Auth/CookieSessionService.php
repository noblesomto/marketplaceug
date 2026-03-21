<?php

namespace App\Services\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

/**
 * CookieSessionService
 *
 * Centralises the two-cookie "remember me" flow that was duplicated across
 * AccountController::loginUser() and AccountController::authenticate().
 *
 * Both methods called storeTrustedDevice() then queued identical trusted_device
 * and remember_login cookies (~30 lines each). This service owns those cookies.
 */
class CookieSessionService
{
    /**
     * Queue the trusted_device and remember_login cookies and store the
     * hashed remember token on the user record.
     *
     * Call after storeTrustedDevice() has already been called.
     *
     * @param Request $request
     * @param mixed   $user  — Eloquent User instance (needs user_id)
     */
    public function issueRememberCookies(Request $request, $user): void
    {
        $isSecure   = $request->secure();
        $deviceHash = $this->generateDeviceHash($request);

        // Trusted device cookie — allows OTP skip for 90 days
        cookie()->queue(cookie(
            'trusted_device',
            $deviceHash,
            60 * 24 * 90,
            '/',
            null,
            $isSecure,
            true,   // httpOnly
            false,  // raw
            'Lax'
        ));

        // Persistent login cookie — keeps the user logged in for 90 days
        $token = Str::random(60);
        DB::table('users')
            ->where('user_id', $user->user_id)
            ->update(['remember_token' => hash('sha256', $token)]);

        cookie()->queue(cookie(
            'remember_login',
            $token,
            60 * 24 * 90,
            '/',
            null,
            $isSecure,
            true,   // httpOnly
            false,  // raw
            'Lax'
        ));
    }

    /**
     * Generate a stable device fingerprint from the user agent string.
     * Intentionally excludes IP so the hash stays consistent on mobile.
     */
    public function generateDeviceHash(Request $request): string
    {
        return sha1($request->userAgent());
    }
}
