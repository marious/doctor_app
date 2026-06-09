<?php
namespace App\Http\Controllers;

use App\Services\FcmService;
use Modules\Users\Models\User;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function __construct(protected FcmService $fcm) {}

    public function sendTest(User $user): JsonResponse
    {
        if (!$user->fcm_token) {
            return response()->json([
                'success' => false,
                'message' => 'User does not have an FCM token registered.',
            ], 422);
        }

        try {
            $this->fcm->sendCustom(
                $user,
                'Test Notification',
                'Hello ' . $user->name . '! Notifications are working correctly.',
                [
                    'type'      => 'test',
                    'user_id'   => (string) $user->id,
                    'timestamp' => now()->toISOString(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Test notification sent to ' . $user->name,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}