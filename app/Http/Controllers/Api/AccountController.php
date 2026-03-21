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
     * Login (Step 1)
     *
     * Authenticates user credentials and initiates the login process. If the device is trusted,
     * completes login immediately. Otherwise, sends a 6-digit OTP to the user's email for verification.
     *
     * @group Authentication
     *
     * @bodyParam email string required The user's email address. Example: john@example.com
     * @bodyParam password string required The user's password (minimum 4 characters). Example: mypassword123
     *
     * @response 200 scenario="Trusted device - login successful" {
     *   "status": true,
     *   "message": "Login successful.",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "john@example.com",
     *       "acc_type": "Private",
     *       "phone": "08012345678"
     *     },
     *     "token": "1|abcdefghijklmnopqrstuvwxyz1234567890"
     *   }
     * }
     *
     * @response 200 scenario="OTP required" {
     *   "status": true,
     *   "message": "OTP sent to your email.",
     *   "requires_otp": true,
     *   "email": "john@example.com"
     * }
     *
     * @response 401 scenario="Invalid credentials" {
     *   "status": false,
     *   "message": "Incorrect password."
     * }
     *
     * @response 403 scenario="Account disabled" {
     *   "status": false,
     *   "message": "Account has been disabled. Contact admin."
     * }
     *
     * @response 403 scenario="Email not verified" {
     *   "status": false,
     *   "message": "Email not verified. Please verify your email.",
     *   "requires_verification": true,
     *   "email": "john@example.com"
     * }
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

        return $this->completeLogin($user, $request);
    }

    /**
     * Verify OTP (Step 2)
     *
     * Verifies the 6-digit OTP sent to the user's email and completes the login process.
     * Optionally allows the device to be trusted for 90 days to skip OTP verification in future logins.
     *
     * @group Authentication
     *
     * @bodyParam email string required The user's email address. Example: john@example.com
     * @bodyParam otp string required The 6-digit OTP code sent to email. Example: 123456
     * @bodyParam trust_device boolean Whether to trust this device for 90 days (optional). Example: true
     *
     * @response 200 scenario="OTP verified successfully" {
     *   "status": true,
     *   "message": "Login successful.",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "john@example.com",
     *       "acc_type": "Private",
     *       "phone": "08012345678"
     *     },
     *     "token": "1|abcdefghijklmnopqrstuvwxyz1234567890"
     *   }
     * }
     *
     * @response 401 scenario="Invalid OTP" {
     *   "status": false,
     *   "message": "Invalid OTP. 4 attempt(s) remaining."
     * }
     *
     * @response 401 scenario="OTP expired" {
     *   "status": false,
     *   "message": "OTP has expired. Please request a new one."
     * }
     *
     * @response 429 scenario="Too many attempts" {
     *   "status": false,
     *   "message": "Too many failed attempts. Request a new OTP."
     * }
     *
     * @response 404 scenario="User not found" {
     *   "status": false,
     *   "message": "User not found."
     * }
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
     *
     * Sends a new 6-digit OTP to the user's email address. Limited to 3 requests per hour
     * to prevent abuse. Used when the previous OTP has expired or was not received.
     *
     * @group Authentication
     *
     * @bodyParam email string required The user's email address. Example: john@example.com
     *
     * @response 200 scenario="OTP sent successfully" {
     *   "status": true,
     *   "message": "New OTP sent to your email."
     * }
     *
     * @response 429 scenario="Rate limit exceeded" {
     *   "status": false,
     *   "message": "Too many OTP requests. Try again in 1 hour."
     * }
     *
     * @response 404 scenario="User not found" {
     *   "status": false,
     *   "message": "User not found."
     * }
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
     * Register New User
     *
     * Creates a new user account and sends a verification email. The account must be verified
     * via email before the user can log in. Supports both Private and Business account types.
     *
     * @group Authentication
     *
     * @bodyParam acc_type string required Account type (Private or Business). Example: Private
     * @bodyParam name string required User's full name (maximum 100 characters, no special characters). Example: John Doe
     * @bodyParam phone string required Nigerian phone number in format 080XXXXXXXX or 234XXXXXXXXXX. Example: 08012345678
     * @bodyParam email string required User's email address (must be unique). Example: john@example.com
     * @bodyParam password string required Password (minimum 6 characters). Example: mypassword123
     * @bodyParam password_confirmation string required Password confirmation (must match password). Example: mypassword123
     *
     * @response 201 scenario="Registration successful" {
     *   "status": true,
     *   "message": "Registration successful. Check your email to verify your account.",
     *   "data": {
     *     "email": "john@example.com",
     *     "requires_verification": true
     *   }
     * }
     *
     * @response 422 scenario="Validation error" {
     *   "message": "The email has already been taken.",
     *   "errors": {
     *     "email": [
     *       "The email has already been taken."
     *     ]
     *   }
     * }
     *
     * @response 500 scenario="Registration failed" {
     *   "status": false,
     *   "message": "Registration failed. Please try again."
     * }
     */
    public function register(Request $request)
    {
        $request->validate([
            'acc_type' => 'required',
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
     * Verify Email Address
     *
     * Verifies a user's email address using the verification link sent during registration.
     * Once verified, the user can proceed to log in to their account.
     *
     * @group Authentication
     *
     * @urlParam email string required The user's email address. Example: john@example.com
     * @urlParam token string required The verification token sent via email. Example: a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0
     *
     * @response 200 scenario="Verification successful" {
     *   "status": true,
     *   "message": "Email verified successfully. You can now login."
     * }
     *
     * @response 400 scenario="Invalid verification link" {
     *   "status": false,
     *   "message": "Invalid verification link."
     * }
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
     *
     * Resends the email verification link to users who have not yet verified their account.
     * A new verification token is generated and sent to the registered email address.
     *
     * @group Authentication
     *
     * @bodyParam email string required The user's email address. Example: john@example.com
     *
     * @response 200 scenario="Verification email sent" {
     *   "status": true,
     *   "message": "Verification email resent successfully."
     * }
     *
     * @response 400 scenario="Invalid request" {
     *   "status": false,
     *   "message": "Invalid or already verified account."
     * }
     *
     * @response 500 scenario="Failed to send email" {
     *   "status": false,
     *   "message": "Failed to send verification email."
     * }
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
     *
     * Initiates the password reset process by sending a password reset link to the user's email.
     * The link contains a secure token that can be used to reset the password.
     *
     * @group Authentication
     *
     * @bodyParam email string required The user's registered email address. Example: john@example.com
     *
     * @response 200 scenario="Reset link sent" {
     *   "status": true,
     *   "message": "Password reset link sent to your email."
     * }
     *
     * @response 404 scenario="Email not found" {
     *   "status": false,
     *   "message": "This email does not exist."
     * }
     *
     * @response 500 scenario="Failed to send email" {
     *   "status": false,
     *   "message": "Could not send password reset email."
     * }
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
     *
     * Resets the user's password using the token received via email. The token is validated
     * before allowing the password change. After successful reset, the token is invalidated.
     *
     * @group Authentication
     *
     * @urlParam user_id string required The user's unique identifier. Example: USR123456
     * @urlParam token string required The password reset token from email. Example: a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0
     * @bodyParam password string required New password (minimum 6 characters). Example: newpassword123
     * @bodyParam password_confirmation string required Password confirmation (must match password). Example: newpassword123
     *
     * @response 200 scenario="Password reset successful" {
     *   "status": true,
     *   "message": "Password reset successfully."
     * }
     *
     * @response 400 scenario="Invalid token" {
     *   "status": false,
     *   "message": "Invalid reset token."
     * }
     *
     * @response 422 scenario="Validation error" {
     *   "message": "The password confirmation does not match.",
     *   "errors": {
     *     "password": [
     *       "The password confirmation does not match."
     *     ]
     *   }
     * }
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
     *
     * Logs out the authenticated user by revoking the current access token.
     * The user will need to log in again to access protected endpoints.
     *
     * @group Authentication
     * @authenticated
     *
     * @response 200 scenario="Logout successful" {
     *   "status": true,
     *   "message": "Logged out successfully."
     * }
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
     * Get Authenticated User
     *
     * Retrieves the profile information of the currently authenticated user.
     * Returns complete user details including account type, verification status, and contact information.
     *
     * @group Authentication
     * @authenticated
     *
     * @response 200 scenario="User profile retrieved" {
     *   "status": true,
     *   "data": {
     *     "id": 1,
     *     "user_id": "USR123456",
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "phone": "08012345678",
     *     "acc_type": "Private",
     *     "acc_status": 1,
     *     "disable_account": "no",
     *     "last_login_at": "2026-01-20T10:30:00.000000Z",
     *     "created_at": "2026-01-15T08:00:00.000000Z"
     *   }
     * }
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
     *
     * Removes a trusted device from the user's account. After removal, the device will require
     * OTP verification on the next login attempt. Used to revoke trust for lost or compromised devices.
     *
     * @group Authentication
     * @authenticated
     *
     * @urlParam device_id integer required The ID of the trusted device to remove. Example: 5
     *
     * @response 200 scenario="Device removed successfully" {
     *   "status": true,
     *   "message": "Trusted device removed successfully."
     * }
     *
     * @response 404 scenario="Device not found" {
     *   "status": false,
     *   "message": "Device not found."
     * }
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
     * Social Login
     *
     * Authenticates users via social providers (Google or Facebook). The mobile app obtains
     * an access token from the provider's SDK and sends it to this endpoint for verification.
     * If successful, creates or updates the user account and returns an API token. No OTP required.
     *
     * @group Authentication
     *
     * @bodyParam provider string required The social provider (google or facebook). Example: google
     * @bodyParam access_token string required The access token obtained from the provider's SDK. Example: ya29.a0AfH6SMBx...
     * @bodyParam device_name string The name of the device for token identification (optional). Example: iPhone 13
     *
     * @response 200 scenario="Social login successful" {
     *   "status": true,
     *   "message": "Social login successful.",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "john@example.com",
     *       "acc_type": "Private",
     *       "acc_status": 1,
     *       "google_id": "1234567890"
     *     },
     *     "token": "1|abcdefghijklmnopqrstuvwxyz1234567890"
     *   }
     * }
     *
     * @response 401 scenario="Invalid token" {
     *   "status": false,
     *   "message": "Invalid social login token."
     * }
     *
     * @response 422 scenario="Validation error" {
     *   "message": "The provider field is required.",
     *   "errors": {
     *     "provider": [
     *       "The provider field is required."
     *     ]
     *   }
     * }
     *
     * @response 500 scenario="Login failed" {
     *   "status": false,
     *   "message": "Social login failed. Please try again."
     * }
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
     * Get Social Login Redirect URL
     *
     * Generates the OAuth redirect URL for social login via WebView flow. The mobile app
     * can open this URL in a WebView to initiate the social authentication process.
     * Use this for apps that cannot use native social SDKs.
     *
     * @group Authentication
     *
     * @urlParam provider string required The social provider (google or facebook). Example: google
     *
     * @response 200 scenario="Redirect URL generated" {
     *   "status": true,
     *   "data": {
     *     "redirect_url": "https://accounts.google.com/o/oauth2/auth?client_id=..."
     *   }
     * }
     *
     * @response 400 scenario="Invalid provider" {
     *   "status": false,
     *   "message": "Invalid provider."
     * }
     *
     * @response 500 scenario="Failed to generate URL" {
     *   "status": false,
     *   "message": "Failed to generate redirect URL."
     * }
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
     * Social Login Callback
     *
     * Handles the OAuth callback from social providers during WebView flow. After the user
     * authenticates with the provider, they are redirected here. This endpoint verifies the
     * authentication, creates or updates the user, and returns an API token.
     *
     * @group Authentication
     *
     * @urlParam provider string required The social provider (google or facebook). Example: google
     *
     * @response 200 scenario="Social login successful" {
     *   "status": true,
     *   "message": "Social login successful.",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "john@example.com",
     *       "acc_type": "Private",
     *       "acc_status": 1,
     *       "facebook_id": "9876543210"
     *     },
     *     "token": "1|abcdefghijklmnopqrstuvwxyz1234567890"
     *   }
     * }
     *
     * @response 400 scenario="Invalid provider" {
     *   "status": false,
     *   "message": "Invalid provider."
     * }
     *
     * @response 500 scenario="Login failed" {
     *   "status": false,
     *   "message": "Social login failed."
     * }
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
