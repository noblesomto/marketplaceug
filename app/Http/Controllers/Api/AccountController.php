<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Mail\RegisterMail;
use App\Mail\OTPMail;
use App\Mail\PasswordMail;
use Illuminate\Validation\Rule;
use App\Rules\NigerianPhoneNumber;
use App\Rules\AllowedName;
use App\Helpers\ContentHelper;

class AccountController extends Controller
{
    /**
     * API Login - Step 1: Validate credentials
     * POST /api/login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:4',
        ]);

        $user = User::where('email', $request->email)->first();

        // Validate user status
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Email address does not exist.'
            ], 401);
        }

        if ($user->disable_account === 'yes') {
            return response()->json([
                'status' => false,
                'message' => 'Account has been disabled. Contact admin.'
            ], 403);
        }

        if ($user->acc_status == 0) {
            return response()->json([
                'status' => false,
                'message' => 'Email not verified. Please verify your email.',
                'requires_verification' => true,
                'email' => $user->email
            ], 403);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Incorrect password.'
            ], 401);
        }

        // Check if trusted device
        if ($this->isTrustedDevice($user, $request)) {
            return $this->completeLogin($user, $request);
        }

        // Send OTP
        return $this->sendOTP($user, $request);
    }

    /**
     * API Login - Step 2: Verify OTP
     * POST /api/verify-otp
     */
    public function verifyOTP(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|numeric|digits:6',
            'trust_device' => 'boolean',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found.'
            ], 404);
        }

        // Check if OTP exists
        if (!$user->otp || !$user->otp_expires_at) {
            return response()->json([
                'status' => false,
                'message' => 'No OTP found. Please request a new OTP.'
            ], 400);
        }

        // Rate limiting
        $cacheKey = 'otp_attempts:' . $user->id;
        $attempts = Cache::get($cacheKey, 0);

        if ($attempts >= 5) {
            return response()->json([
                'status' => false,
                'message' => 'Too many failed attempts. Request a new OTP.'
            ], 429);
        }

        // Check expiry first
        if (now()->isAfter($user->otp_expires_at)) {
            return response()->json([
                'status' => false,
                'message' => 'OTP has expired. Please request a new one.'
            ], 401);
        }

        // Compare OTP
        if ($user->otp != $request->otp) {
            Cache::put($cacheKey, $attempts + 1, now()->addHour());
            $remaining = 5 - $attempts - 1;

            return response()->json([
                'status' => false,
                'message' => "Invalid OTP. {$remaining} attempt(s) remaining."
            ], 401);
        }

        // Clear OTP
        $user->update([
            'otp' => null,
            'otp_expires_at' => null,
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        Cache::forget($cacheKey);

        // Store trusted device if requested
        if ($request->trust_device) {
            $this->storeTrustedDevice($user, $request);
        }

        return $this->completeLogin($user, $request, $request->trust_device);
    }

    /**
     * Resend OTP
     * POST /api/resend-otp
     */
    public function resendOTP(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found.'
            ], 404);
        }

        // Rate limit: 3 per hour
        $rateLimitKey = 'resend-otp:' . $user->id;
        if (Cache::has($rateLimitKey) && Cache::get($rateLimitKey) >= 3) {
            return response()->json([
                'status' => false,
                'message' => 'Too many OTP requests. Try again in 1 hour.'
            ], 429);
        }

        $this->sendOTP($user, $request);

        $currentCount = Cache::get($rateLimitKey, 0);
        Cache::put($rateLimitKey, $currentCount + 1, now()->addHour());

        return response()->json([
            'status' => true,
            'message' => 'New OTP sent to your email.'
        ]);
    }

    /**
     * Register
     * POST /api/register
     */
    public function register(Request $request)
    {
        $request->validate([
            'acc_type' => 'required',
            'address' => 'required',
            'state' => 'required',
            'name' => ['required', 'string', 'max:100', new AllowedName],
            'phone' => [
                'required',
                Rule::unique('users', 'phone'),
                new NigerianPhoneNumber(),
            ],
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);

        $token = Str::random(40);

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => ContentHelper::sanitizeName($request->name),
                'email' => $request->email,
                'phone' => $request->phone,
                'acc_type' => $request->acc_type,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'token' => $token,
                'acc_status' => 0,
                'password' => Hash::make($request->password),
            ]);

            Mail::to($user->email)->queue(new RegisterMail([
                'user_id' => $user->email,
                'token' => $token,
                'name' => $user->name,
            ]));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Registration successful. Check your email to verify your account.',
                'data' => [
                    'email' => $user->email,
                    'requires_verification' => true
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API Registration failed', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Registration failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Verify Account
     * GET /api/verify/{email}/{token}
     */
    public function verifyAccount($email, $token)
    {
        $user = User::where('email', $email)->where('token', $token)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid verification link.'
            ], 400);
        }

        $user->update([
            'acc_status' => 1,
            'token' => null
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Email verified successfully. You can now login.'
        ]);
    }

    /**
     * Resend Verification Email
     * POST /api/resend-verification
     */
    public function resendVerification(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->where('acc_status', 0)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or already verified account.'
            ], 400);
        }

        $token = Str::random(40);
        $user->update(['token' => $token]);

        try {
            Mail::to($user->email)->queue(new RegisterMail([
                'user_id' => $user->email,
                'token' => $token,
                'name' => $user->name,
            ]));

            return response()->json([
                'status' => true,
                'message' => 'Verification email resent successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Resend verification failed', ['email' => $request->email]);
            return response()->json([
                'status' => false,
                'message' => 'Failed to send verification email.'
            ], 500);
        }
    }

    /**
     * Forgot Password
     * POST /api/forgot-password
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'This email does not exist.'
            ], 404);
        }

        try {
            Mail::to($user->email)->send(new PasswordMail([
                'user_id' => $user->user_id,
                'token' => $user->token,
                'name' => $user->name,
            ]));

            return response()->json([
                'status' => true,
                'message' => 'Password reset link sent to your email.'
            ]);
        } catch (\Throwable $e) {
            Log::error('Password reset email failed', ['email' => $request->email]);
            return response()->json([
                'status' => false,
                'message' => 'Could not send password reset email.'
            ], 500);
        }
    }

    /**
     * Reset Password
     * POST /api/reset-password/{user_id}/{token}
     */
    public function resetPassword(Request $request, $user_id, $token)
    {
        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::where('user_id', $user_id)->where('token', $token)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid reset token.'
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'token' => null
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Password reset successfully.'
        ]);
    }

    /**
     * Logout
     * POST /api/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully.'
        ]);
    }

    /**
     * Get User Profile
     * GET /api/user
     */
    public function user(Request $request)
    {
        return response()->json([
            'status' => true,
            'data' => $request->user()
        ]);
    }

    /**
     * Remove Trusted Device
     * DELETE /api/trusted-device/{device_id}
     */
    public function removeTrustedDevice(Request $request, $device_id)
    {
        $user = $request->user();
        $device = $user->trustedDevices()->where('id', $device_id)->first();

        if (!$device) {
            return response()->json([
                'status' => false,
                'message' => 'Device not found.'
            ], 404);
        }

        $device->delete();

        return response()->json([
            'status' => true,
            'message' => 'Trusted device removed successfully.'
        ]);
    }

    // ==================== SOCIAL LOGIN METHODS ====================

    /**
     * Social Login - Mobile sends provider token
     * POST /api/auth/social
     *
     * Mobile app gets token from provider (Google/Facebook SDK)
     * then sends it to this endpoint for verification
     */
    public function socialLogin(Request $request)
    {
        $request->validate([
            'provider' => 'required|in:google,facebook',
            'access_token' => 'required|string',
            'device_name' => 'nullable|string',
        ]);

        try {
            // Verify token with provider and get user info
            $socialUser = $this->verifyProviderToken(
                $request->provider,
                $request->access_token
            );

            if (!$socialUser) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid social login token.'
                ], 401);
            }

            // Find or create user
            $user = $this->findOrCreateSocialUser($socialUser, $request->provider);

            // Update login activity
            $user->update([
                'last_login_ip' => $request->ip(),
                'last_login_at' => now(),
            ]);

            // Create API token (no OTP needed for social login)
            $deviceName = $request->device_name ?? 'mobile-device';
            $token = $user->createToken($deviceName, ['*'], now()->addDays(90))->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Social login successful.',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Social login failed', [
                'provider' => $request->provider,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Social login failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Get Social Login URL - For WebView flow
     * GET /api/auth/{provider}/redirect
     */
    public function socialRedirect($provider)
    {
        if (!in_array($provider, ['google', 'facebook'])) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid provider.'
            ], 400);
        }

        try {
            $redirectUrl = \Laravel\Socialite\Facades\Socialite::driver($provider)
                ->stateless()
                ->redirect()
                ->getTargetUrl();

            return response()->json([
                'status' => true,
                'data' => [
                    'redirect_url' => $redirectUrl
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to generate redirect URL.'
            ], 500);
        }
    }

    /**
     * Social Login Callback - For WebView flow
     * GET /api/auth/{provider}/callback
     */
    public function socialCallback($provider)
    {
        if (!in_array($provider, ['google', 'facebook'])) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid provider.'
            ], 400);
        }

        try {
            $socialUser = \Laravel\Socialite\Facades\Socialite::driver($provider)->stateless()->user();

            // Find or create user
            $user = $this->findOrCreateSocialUser($socialUser, $provider);

            // Update login activity
            $user->update([
                'last_login_ip' => request()->ip(),
                'last_login_at' => now(),
            ]);

            // Create token
            $token = $user->createToken('mobile-device', ['*'], now()->addDays(90))->plainTextToken;

            // Return JSON with token (mobile app will extract this)
            return response()->json([
                'status' => true,
                'message' => 'Social login successful.',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Social callback failed', [
                'provider' => $provider,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Social login failed.'
            ], 500);
        }
    }

    // ==================== HELPER METHODS ====================

    protected function sendOTP($user, Request $request)
    {
        $otp = rand(111111, 999999);

        // Use direct DB update to ensure it saves
        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'otp' => $otp,
                'otp_expires_at' => now()->addMinutes(10),
            ]);

        Cache::forget('otp_attempts:' . $user->id);

        try {
            Mail::to($user->email)->send(new OTPMail([
                'user_id' => $user->user_id,
                'otp' => $otp,
                'name' => $user->name,
                'ip' => $request->ip(),
            ]));

            return response()->json([
                'status' => true,
                'message' => 'OTP sent to your email.',
                'requires_otp' => true,
                'email' => $user->email
            ]);
        } catch (\Throwable $e) {
            Log::error('OTP Email Failed', [
                'user_id' => $user->user_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to send OTP. Try again.'
            ], 500);
        }
    }

    protected function completeLogin($user, Request $request, $trustDevice = false)
    {
        // Revoke old tokens (optional: keep only 3 most recent)
        $user->tokens()->where('created_at', '<', now()->subDays(30))->delete();

        $token = $user->createToken('mobile', ['*'], now()->addDays(90))->plainTextToken;

        $response = [
            'status' => true,
            'message' => 'Login successful.',
            'data' => [
                'user' => $user,
                'token' => $token,
            ]
        ];

        if ($trustDevice) {
            $response['message'] = 'Login successful. Device trusted for 90 days.';
        }

        return response()->json($response);
    }

    protected function isTrustedDevice($user, Request $request)
    {
        $deviceHash = $this->generateDeviceHash($request);

        return $user->trustedDevices()
            ->where('device_hash', $deviceHash)
            ->where('expires_at', '>=', now())
            ->exists();
    }

    protected function storeTrustedDevice($user, Request $request)
    {
        $deviceHash = $this->generateDeviceHash($request);

        $user->trustedDevices()->updateOrCreate(
            ['device_hash' => $deviceHash],
            [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'last_used_at' => now(),
                'expires_at' => now()->addDays(90),
            ]
        );
    }

    protected function generateDeviceHash(Request $request)
    {
        return sha1($request->userAgent());
    }

    // ==================== SOCIAL LOGIN HELPER METHODS ====================

    /**
     * Verify provider token and get user info
     */
    protected function verifyProviderToken($provider, $token)
    {
        try {
            if ($provider === 'google') {
                return $this->verifyGoogleToken($token);
            } elseif ($provider === 'facebook') {
                return $this->verifyFacebookToken($token);
            }
            return null;
        } catch (\Exception $e) {
            Log::error('Token verification failed', [
                'provider' => $provider,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Verify Google token
     */
    protected function verifyGoogleToken($token)
    {
        $response = Http::get('https://www.googleapis.com/oauth2/v3/userinfo', [
            'access_token' => $token
        ]);

        if (!$response->successful()) {
            return null;
        }

        $data = $response->json();

        return (object) [
            'id' => $data['sub'] ?? null,
            'email' => $data['email'] ?? null,
            'name' => $data['name'] ?? null,
            'avatar' => $data['picture'] ?? null,
        ];
    }

    /**
     * Verify Facebook token
     */
    protected function verifyFacebookToken($token)
    {
        $response = Http::get('https://graph.facebook.com/me', [
            'fields' => 'id,name,email,picture',
            'access_token' => $token
        ]);

        if (!$response->successful()) {
            return null;
        }

        $data = $response->json();

        return (object) [
            'id' => $data['id'] ?? null,
            'email' => $data['email'] ?? null,
            'name' => $data['name'] ?? null,
            'avatar' => $data['picture']['data']['url'] ?? null,
        ];
    }

    /**
     * Find or create user from social login
     */
    protected function findOrCreateSocialUser($socialUser, $provider)
    {
        // Try to find user by email
        $user = User::where('email', $socialUser->email)->first();

        if (!$user) {
            // Create new user
            $user = User::create([
                'name' => $socialUser->name ?? 'User',
                'email' => $socialUser->email,
                $provider . '_id' => $socialUser->id,
                'acc_status' => 1, // Auto-verified for social login
                'acc_type' => 'Private',
                'password' => Hash::make(Str::random(16)), // Random password
                'avatar' => $socialUser->avatar ?? null,
            ]);
        } else {
            // Update provider ID if missing
            if (!$user->{$provider . '_id'}) {
                $user->update([
                    $provider . '_id' => $socialUser->id,
                ]);
            }
        }

        return $user;
    }
}
