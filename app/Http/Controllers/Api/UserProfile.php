<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Advert;
use App\Models\Bank;
use App\Models\UserVerification;
use App\Helpers\FileUploadHelper;
use App\Mail\VerificationRequestMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
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
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:12048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        $updateData = [
            'name' => $request->name,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state
        ];

        if ($request->hasFile('profile_image')) {
            $imageName = FileUploadHelper::upload(
                $request->file('profile_image'),
                'profile',
                $user->profile_picture
            );
            $updateData['profile_picture'] = $imageName;
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
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
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
            'document_file' => 'required|file|mimes:jpeg,png,jpg,gif,pdf|max:12048',
            'proof_address' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf|max:12048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        $existingVerification = UserVerification::where('user_id', $user->user_id)->first();

        try {
            // Upload document
            $filename = FileUploadHelper::upload(
                $request->file('document_file'),
                'verification',
                $existingVerification?->document_file
            );

            // Upload proof address if provided
            $proof_address = $existingVerification?->proof_address;
            if ($request->hasFile('proof_address')) {
                $proof_address = FileUploadHelper::upload(
                    $request->file('proof_address'),
                    'verification',
                    $existingVerification?->proof_address
                );
            }

            UserVerification::updateOrCreate(
                ['user_id' => $user->user_id],
                [
                    'document_number' => $request->document_number,
                    'document_type' => $request->document_type,
                    'document_file' => $filename,
                    'proof_address' => $proof_address,
                    'status' => 'pending'
                ]
            );

            // Send email notification
            Mail::to(config('global.site_email'))->send(new VerificationRequestMail([
                'user_id' => $user->user_id,
                'name' => $user->name,
                'email' => $user->email,
                'document_number' => $request->document_number,
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Verification information submitted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit verification documents',
                'error' => $e->getMessage()
            ], 500);
        }
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
            'acc_status' => 0,
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
}
