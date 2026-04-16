<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdatePasswordRequest;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Requests\Api\UpdateSettingsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Modules\Users\Resources\UserResource;

class ProfileController extends Controller
{
    /**
     * Get the authenticated user's profile.
     */
    public function show(): JsonResponse
    {
        $user = auth()->user();

        return response()->json([
            'status' => true,
            'message' => __('Profile retrieved successfully'),
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = auth()->user();
        
        $user->update($request->validated());

        return response()->json([
            'status' => true,
            'message' => __('Profile updated successfully'),
            'data' => new UserResource($user->refresh()),
        ]);
    }

    /**
     * Update the authenticated user's password.
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = auth()->user();

        $user->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return response()->json([
            'status' => true,
            'message' => __('Password updated successfully'),
        ]);
    }

    /**
     * Update the authenticated user's settings.
     */
    public function updateSettings(UpdateSettingsRequest $request): JsonResponse
    {
        $user = auth()->user();
        
        $validated = $request->validated();
        
        // Merge the incoming JSON settings with existing one
        if (isset($validated['settings'])) {
            $validated['settings'] = array_merge($user->settings ?? [], $validated['settings']);
        }
        
        $user->update($validated);

        return response()->json([
            'status' => true,
            'message' => __('Settings updated successfully'),
            'data' => new UserResource($user->refresh()),
        ]);
    }

    /**
     * Delete the authenticated user's account.
     */
    public function destroy(): JsonResponse
    {
        $user = auth()->user();

        // Revoke all tokens to log them out
        $user->tokens()->delete();

        // Soft delete the user
        $user->delete();

        return response()->json([
            'status' => true,
            'message' => __('Account deleted successfully'),
        ]);
    }

    /**
     * Get all active devices (tokens) for the authenticated user.
     */
    public function devices(): JsonResponse
    {
        $devices = auth()->user()->tokens()
            ->select('id', 'name', 'last_used_at', 'created_at')
            ->orderBy('last_used_at', 'desc')
            ->get()
            ->map(function ($token) {
                return [
                    'id' => $token->id,
                    'name' => $token->name,
                    'last_used_at' => $token->last_used_at ? $token->last_used_at->format('Y-m-d H:i:s') : null,
                    'created_at' => $token->created_at ? $token->created_at->format('Y-m-d H:i:s') : null,
                ];
            });

        return response()->json([
            'status' => true,
            'message' => __('Devices retrieved successfully'),
            'data' => $devices,
        ]);
    }

    /**
     * Revoke access for a specific device (token).
     */
    public function revokeDevice($tokenId): JsonResponse
    {
        $deleted = auth()->user()->tokens()->where('id', $tokenId)->delete();

        if (!$deleted) {
            return response()->json([
                'status' => false,
                'message' => __('Device not found or already removed'),
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => __('Device disconnected successfully'),
        ]);
    }
}
