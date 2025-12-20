<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Advert;
use App\Models\Bank;
use App\Models\UserVerification;
use App\Helpers\FileUploadHelper;
use App\Mail\VerificationRequestMail;
use App\Rules\NigerianPhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class UserProfile extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/user/profile",
     *     summary="Get user profile",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User profile data",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/User"),
     *                 @OA\Property(property="ads_count", type="integer"),
     *                 @OA\Property(property="recent_ads", ref="#/components/schemas/AdvertList")
     *             )
     *         )
     *     )
     * )
     */
    public function getProfile()
    {
        $user = auth()->user();
        $adsCount = Advert::where('user_id', $user->user_id)->count();
        $recentAds = Advert::with('firstImage')
            ->where('user_id', $user->user_id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'ads_count' => $adsCount,
                'recent_ads' => $recentAds
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/user/about-account",
     *     summary="Get user about account information",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User about account data",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/User"),
     *                 @OA\Property(property="ads_count", type="integer"),
     *                 @OA\Property(property="recent_ads", ref="#/components/schemas/AdvertList")
     *             )
     *         )
     *     )
     * )
     */
    public function aboutAccount()
    {
        $user = auth()->user();
        $adsCount = Advert::where('user_id', $user->user_id)->count();
        $recentAds = Advert::with('firstImage')
            ->where('user_id', $user->user_id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'ads_count' => $adsCount,
                'recent_ads' => $recentAds
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/user/ads",
     *     summary="Load more user ads (paginated)",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User ads list",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="ads", ref="#/components/schemas/AdvertList"),
     *                 @OA\Property(property="has_more", type="boolean")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function loadMoreUserAds()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $ads = Advert::with('firstImage')
            ->orderBy('created_at', 'desc')
            ->where('user_id', $user->user_id)
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => [
                'ads' => $ads->items(),
                'has_more' => $ads->hasMorePages(),
                'current_page' => $ads->currentPage(),
                'total' => $ads->total(),
                'per_page' => $ads->perPage()
            ]
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/user/profile/address",
     *     summary="Update user address and profile image",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "address", "city", "state"},
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="address", type="string"),
     *                 @OA\Property(property="city", type="string"),
     *                 @OA\Property(property="state", type="string"),
     *                 @OA\Property(property="profile_image", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function updateAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:12048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        $updateData = $request->only(['name', 'address', 'city', 'state']);

        try {
            // Handle profile image upload using Media Library
            if ($request->hasFile('profile_image')) {
                $fileName = now()->format('YmdHis') . '_profile.' . $request->file('profile_image')->getClientOriginalExtension();

                $user->addMediaFromRequest('profile_image')
                    ->usingFileName($fileName)
                    ->toMediaCollection('profile_image');

                $media = $user->getFirstMedia('profile_image');

                // After conversions finish, delete original
                if ($media && $media->hasGeneratedConversion('optimized') && $media->hasGeneratedConversion('thumbnail')) {
                    $originalPath = $media->getPath();
                    if (file_exists($originalPath)) {
                        unlink($originalPath);
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Profile image upload failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Profile image upload failed. Please try again.'
            ], 500);
        }

        $user->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Profile information updated successfully',
            'data' => $user
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/user/profile/phone",
     *     summary="Update user phone number",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone"},
     *             @OA\Property(property="phone", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Phone number updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function updatePhone(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'phone' => [
                'required',
                new NigerianPhoneNumber(),
                Rule::unique('users', 'phone')->ignore($user->user_id, 'user_id'),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update(['phone' => $request->phone]);

        return response()->json([
            'success' => true,
            'message' => 'Phone number updated successfully',
            'data' => $user
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/user/verification",
     *     summary="Get user verification status",
     *     tags={"User Verification"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Verification status",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/User"),
     *                 @OA\Property(property="verification", ref="#/components/schemas/UserVerification")
     *             )
     *         )
     *     )
     * )
     */
    public function getVerificationStatus()
    {
        $user = auth()->user()->load('verification');

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'verification' => $user->verification
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/user/verification",
     *     summary="Submit verification documents",
     *     tags={"User Verification"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"document_number", "document_file"},
     *                 @OA\Property(property="document_number", type="string"),
     *                 @OA\Property(property="document_type", type="string"),
     *                 @OA\Property(property="document_file", type="string", format="binary"),
     *                 @OA\Property(property="proof_address", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Verification submitted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function submitVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'document_number' => 'required|string|max:255',
            'document_type' => 'nullable|string|max:255',
            'document_file' => 'required|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:12048',
            'proof_address' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:12048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();

        $verification = UserVerification::updateOrCreate(
            ['user_id' => $user->user_id],
            [
                'document_number' => $request->document_number,
                'document_type' => $request->document_type,
            ]
        );

        try {
            // Handle document file upload using Media Library
            if ($request->hasFile('document_file')) {
                $fileName = now()->format('YmdHis') . '_document.' . $request->file('document_file')->getClientOriginalExtension();

                $verification->addMediaFromRequest('document_file')
                    ->usingFileName($fileName)
                    ->toMediaCollection('verification_documents');
            }

            // Handle proof of address upload (optional)
            if ($request->hasFile('proof_address')) {
                $fileName = now()->format('YmdHis') . '_address.' . $request->file('proof_address')->getClientOriginalExtension();

                $verification->addMediaFromRequest('proof_address')
                    ->usingFileName($fileName)
                    ->toMediaCollection('verification_address');
            }

        } catch (\Exception $e) {
            \Log::error('Verification file upload failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'File upload failed. Please try again.'
            ], 500);
        }

        // Send email notification
        try {
            \Mail::to(config('global.site_email'))
                ->queue(new VerificationRequestMail([
                    'user_id' => $user->user_id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'document_number' => $request->document_number,
                ]));
        } catch (\Exception $e) {
            \Log::error('Verification email failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Verification information submitted successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/user/payment-info",
     *     summary="Get user payment information",
     *     tags={"User Payment"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Payment information",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/User"),
     *                 @OA\Property(property="banks", type="array", @OA\Items(ref="#/components/schemas/Bank"))
     *             )
     *         )
     *     )
     * )
     */
    public function getPaymentInfo()
    {
        $user = auth()->user();
        $banks = Bank::all();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'banks' => $banks
            ]
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/user/payment-info",
     *     summary="Update user payment information",
     *     tags={"User Payment"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"bank_name", "paystack_bank_code", "account_number", "account_name"},
     *             @OA\Property(property="bank_name", type="string"),
     *             @OA\Property(property="paystack_bank_code", type="string"),
     *             @OA\Property(property="account_number", type="string"),
     *             @OA\Property(property="account_name", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment information updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function updatePaymentInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_name' => 'required|string|max:255',
            'paystack_bank_code' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'account_name' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        $user->update([
            'bank_name' => $request->bank_name,
            'bank_code' => $request->paystack_bank_code,
            'account_name' => $request->account_name,
            'account_number' => $request->account_number
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment information updated successfully',
            'data' => $user
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/user/password",
     *     summary="Change user password",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"old_password", "password", "password_confirmation"},
     *             @OA\Property(property="old_password", type="string"),
     *             @OA\Property(property="password", type="string", minLength=6),
     *             @OA\Property(property="password_confirmation", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password changed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'password' => 'required|min:6|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The current password does not match'
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/user/notifications",
     *     summary="Update user notification preferences",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"notifications"},
     *             @OA\Property(property="notifications", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notifications updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function updateNotifications(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notifications' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        $user->update(['notification' => $request->notifications]);

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences updated successfully',
            'data' => $user
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/user/account",
     *     summary="Disable user account",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Account disabled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function disableAccount()
    {
        $user = auth()->user();

        $user->update([
            'disable_account' => "yes",
            'disable_account_date' => Carbon::now()
        ]);

        // Invalidate current token
        $user->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Account disabled successfully'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/user/logout",
     *     summary="Logout user",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logged out successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function logout()
    {
        $user = auth()->user();
        $user->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/user/settings",
     *     summary="Get user settings",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User settings",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/User"),
     *                 @OA\Property(property="ads_count", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function getSettings()
    {
        $user = auth()->user();
        $adsCount = Advert::where('user_id', $user->user_id)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'ads_count' => $adsCount
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/user/profile-info",
     *     summary="Get user profile information",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User profile info",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/User"),
     *                 @OA\Property(property="ads_count", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function getProfileInfo()
    {
        $user = auth()->user();
        $adsCount = Advert::where('user_id', $user->user_id)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'ads_count' => $adsCount
            ]
        ]);
    }
}
