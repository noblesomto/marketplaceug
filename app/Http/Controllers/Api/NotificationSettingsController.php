<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationSettingsController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'push_notifications_enabled' => $request->user()->push_notifications_enabled,
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'push_notifications_enabled' => 'required|boolean',
        ]);

        $request->user()->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Notification settings updated successfully',
            'data' => [
                'push_notifications_enabled' => $request->user()->push_notifications_enabled,
            ],
        ]);
    }
}
