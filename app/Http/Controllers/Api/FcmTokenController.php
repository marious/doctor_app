<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FcmTokenController extends Controller
{
    /**
     * POST /profile/fcm-token
     * Called by the Flutter app on login or when the FCM token refreshes.
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'fcm_token' => ['required', 'string'],
        ]);

        Auth::user()->update(['fcm_token' => $request->input('fcm_token')]);

        return response()->json(['success' => true]);
    }

    /**
     * DELETE /profile/fcm-token
     * Called on logout to stop notifications for this device.
     */
    public function destroy(): JsonResponse
    {
        Auth::user()->update(['fcm_token' => null]);

        return response()->json(['success' => true]);
    }
}
