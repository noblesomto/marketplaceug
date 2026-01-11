<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeviceTokenController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|string|max:255',
            'platform' => ['required', Rule::in(['android', 'ios'])],
        ]);

        $deviceToken = DeviceToken::updateOrCreate(
            [
                'token' => $validated['token'],
            ],
            [
                'user_id' => $request->user()->id,
                'platform' => $validated['platform'],
                'is_active' => true,
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Device token registered successfully',
            'data' => [
                'id' => $deviceToken->id,
                'platform' => $deviceToken->platform,
            ]
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $tokens = $request->user()
            ->deviceTokens()
            ->select('id', 'platform', 'is_active', 'last_used_at', 'created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tokens,
        ]);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $deleted = DeviceToken::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Device token not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Device token removed successfully',
        ]);
    }
}
