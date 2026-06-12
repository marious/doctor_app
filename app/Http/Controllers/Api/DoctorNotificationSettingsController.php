<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorNotificationSettingsController extends Controller
{
    private const KEYS = [
        'new_appointment_alerts',
        'appointment_approval_notifications',
        'new_patient_messages',
        'missed_appointment_alerts',
    ];

    /**
     * GET /v1/doctor/notification-settings
     * GET /v1/assistant/notification-settings
     */
    public function show(): JsonResponse
    {
        $settings = Auth::user()->getAttribute('settings') ?? [];

        return response()->json([
            'success' => true,
            'data'    => $this->format($settings),
        ]);
    }

    /**
     * POST /v1/doctor/notification-settings
     * POST /v1/assistant/notification-settings
     * Body: { new_appointment_alerts: bool, new_patient_messages: bool, ... }
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'new_appointment_alerts'           => ['sometimes', 'boolean'],
            'appointment_approval_notifications' => ['sometimes', 'boolean'],
            'new_patient_messages'             => ['sometimes', 'boolean'],
            'missed_appointment_alerts'        => ['sometimes', 'boolean'],
        ]);

        $current  = Auth::user()->getAttribute('settings') ?? [];
        $incoming = $request->only(self::KEYS);
        $merged   = array_merge($current, $incoming);

        Auth::user()->update(['settings' => $merged]);

        return response()->json([
            'success' => true,
            'data'    => $this->format($merged),
        ]);
    }

    // ─── Private ──────────────────────────────────────────────────────────────

    private function format(array $settings): array
    {
        return [
            'new_appointment_alerts'             => (bool) ($settings['new_appointment_alerts'] ?? true),
            'appointment_approval_notifications' => (bool) ($settings['appointment_approval_notifications'] ?? true),
            'new_patient_messages'               => (bool) ($settings['new_patient_messages'] ?? true),
            'missed_appointment_alerts'          => (bool) ($settings['missed_appointment_alerts'] ?? true),
        ];
    }
}
