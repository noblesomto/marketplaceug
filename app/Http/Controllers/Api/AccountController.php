<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Mail\NotifyMail;
use App\Mail\RegisterMail;
use App\Mail\PasswordMail;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
    /**
     * API Login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:4',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || $user->disable_account === 'yes' || $user->acc_status == 0) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid account or not verified/disabled.'
            ], 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Incorrect password.'
            ], 401);
        }

        // Generate token (Sanctum)
        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'Login successful',
            'data'    => [
                'user'  => $user,
                'token' => $token,
            ]
        ]);
    }

    /**
     * API Register
     */
    public function register(Request $request)
    {
        $request->validate([
            'acc_type' => 'required',
            'address'  => 'required',
            'state'    => 'required',
            'name'     => 'required',
            'phone'    => 'required|numeric|unique:users',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);

        $token = Str::random(40);

        $user = User::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'acc_type'   => $request->acc_type,
            'address'    => $request->address,
            'city'       => $request->city,
            'state'      => $request->state,
            'token'      => $token,
            'acc_status' => 0,
            'password'   => Hash::make($request->password),
        ]);

        try {
            Mail::to($user->email)->send(new RegisterMail([
                'user_id' => $user->email,
                'token'   => $token,
                'name'    => $user->name,
            ]));
        } catch (\Throwable $e) {
            // Mail failure, but still return success
        }

        return response()->json([
            'status'  => true,
            'message' => 'Registration successful, please verify your email.',
            'data'    => $user
        ], 201);
    }

    /**
     * Verify Account
     */
    public function verifyAccount($email, $token)
    {
        $user = User::where('email', $email)->first();

        if (!$user || $user->token !== $token) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid token or user not found.'
            ], 400);
        }

        $user->update(['acc_status' => 1]);

        return response()->json([
            'status'  => true,
            'message' => 'Email verified successfully.'
        ]);
    }

    /**
     * Forgot Password
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'This email does not exist.'
            ], 404);
        }

        try {
            Mail::to($user->email)->send(new PasswordMail([
                'user_id' => $user->user_id,
                'token'   => $user->token,
                'name'    => $user->name,
            ]));
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Could not send password reset email.'
            ], 500);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Check your email for reset instructions.'
        ]);
    }

    /**
     * Reset Password
     */
    public function resetPassword(Request $request, $user_id, $token)
    {
        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::where('user_id', $user_id)->first();

        if (!$user || $user->token !== $token) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid reset token.'
            ], 400);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json([
            'status'  => true,
            'message' => 'Password reset successfully.'
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Logged out successfully'
        ]);
    }

}
