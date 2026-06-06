<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Notifications\Models\PatientNotification;

class PatientNotificationController extends Controller
{
    /**
     * GET /notifications
     * Paginated notification list for the authenticated patient, newest first.
     */
    public function index(): JsonResponse
    {
        $notifications = PatientNotification::where('patient_id', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $notifications->map(fn($n) => $this->format($n)),
            'meta'    => [
                'unread_count' => PatientNotification::where('patient_id', Auth::id())
                    ->whereNull('read_at')
                    ->count(),
                'current_page' => $notifications->currentPage(),
                'last_page'    => $notifications->lastPage(),
                'per_page'     => $notifications->perPage(),
                'total'        => $notifications->total(),
            ],
        ]);
    }

    /**
     * POST /notifications/{notification}/read
     * Mark a single notification as read.
     */
    public function markRead(PatientNotification $notification): JsonResponse
    {
        abort_if($notification->getAttribute('patient_id') !== Auth::id(), 403);

        $notification->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * POST /notifications/read-all
     * Mark all unread notifications as read.
     */
    public function markAllRead(): JsonResponse
    {
        PatientNotification::where('patient_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    // ─── Private ─────────────────────────────────────────────────────────────

    private function format(PatientNotification $n): array
    {
        return [
            'id'         => $n->getAttribute('id'),
            'type'       => $n->getAttribute('type'),
            'title'      => $n->getAttribute('title'),
            'body'       => $n->getAttribute('body'),
            'data'       => $n->getAttribute('data'),
            'is_read'    => $n->isRead(),
            'created_at' => $n->created_at->toIso8601String(),
            'time_ago'   => $n->created_at->diffForHumans(),
        ];
    }
}
