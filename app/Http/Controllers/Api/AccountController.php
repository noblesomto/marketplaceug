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
use Illuminate\Support\Str;
use App\Mail\RegisterMail;
use App\Mail\OTPMail;
use App\Mail\PasswordMail;
use Illuminate\Validation\Rule;
use App\Rules\NigerianPhoneNumber;
use App\Rules\NotForbiddenName;
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

        // ✅ FIX: Check if OTP exists
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

        // ✅ FIX: Check expiry first, then compare OTP
        if (now()->isAfter($user->otp_expires_at)) {
            return response()->json([
                'status' => false,
                'message' => 'OTP has expired. Please request a new one.'
            ], 401);
        }

        // ✅ FIX: Compare OTP as strings
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
            'name' => ['required', 'min:5', new NotForbiddenName],
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
                'name' => ContentHelper::sanitizeContent($request->name),
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

    // ==================== HELPER METHODS ====================

    protected function sendOTP($user, Request $request)
    {
        $otp = rand(111111, 999999);

        $user->update([
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
}
