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
        
        $user->update($request->validated());

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
}
