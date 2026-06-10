<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Modules\Notifications\Models\PatientNotification;
use Modules\Users\Models\User;

class DummyNotificationsController extends Controller
{
    /**
     * POST /doctor/patients/{patient}/dummy-notifications
     * Seeds all notification types for the given patient for testing.
     */
    public function __invoke(User $patient): JsonResponse
    {
        abort_if($patient->role_id !== 2, 404);

        $now = Carbon::now();

        $records = [
            [
                'type'       => 'appointment',
                'title'      => 'Appointment Approved',
                'body'       => 'Your appointment on Feb 12 at 10:30 AM has been approved.',
                'data'       => ['type' => 'appointment_approved', 'date' => 'Feb 12, 2026', 'time' => '10:30'],
                'read_at'    => null,
                'created_at' => $now->copy()->subMinutes(10),
            ],
            [
                'type'       => 'appointment',
                'title'      => 'Appointment Request',
                'body'       => 'Your appointment request for Feb 20 at 09:00 AM is pending approval.',
                'data'       => ['type' => 'appointment_requested', 'date' => 'Feb 20, 2026', 'time' => '09:00'],
                'read_at'    => null,
                'created_at' => $now->copy()->subHour(),
            ],
            [
                'type'       => 'appointment',
                'title'      => 'Appointment Declined',
                'body'       => 'Your appointment on Feb 15 at 02:00 PM has been declined.',
                'data'       => ['type' => 'appointment_declined', 'date' => 'Feb 15, 2026', 'time' => '14:00'],
                'read_at'    => $now,
                'created_at' => $now->copy()->subDay()->setTime(10, 0),
            ],
            [
                'type'       => 'appointment',
                'title'      => 'Appointment Rescheduled',
                'body'       => 'Your appointment has been moved to Feb 22 at 11:00 AM.',
                'data'       => ['type' => 'appointment_rescheduled', 'date' => 'Feb 22, 2026', 'time' => '11:00'],
                'read_at'    => $now,
                'created_at' => $now->copy()->subDay()->setTime(8, 0),
            ],
            [
                'type'       => 'appointment',
                'title'      => 'Appointment Reminder',
                'body'       => 'Reminder: You have an appointment tomorrow on Feb 20 at 09:00 AM.',
                'data'       => ['type' => 'appointment_reminder', 'date' => 'Feb 20, 2026', 'time' => '09:00'],
                'read_at'    => $now,
                'created_at' => $now->copy()->subDay()->setTime(9, 0),
            ],
            [
                'type'       => 'period',
                'title'      => 'Cycle Reminder',
                'body'       => 'Your period starts in 2 days. Prepare your essentials!',
                'data'       => ['type' => 'period_alert', 'days_until' => '2'],
                'read_at'    => null,
                'created_at' => $now->copy()->setTime(8, 30),
            ],
            [
                'type'       => 'fertility',
                'title'      => 'Ovulation Reminder',
                'body'       => 'Your ovulation window starts tomorrow.',
                'data'       => ['type' => 'ovulation_alert', 'days_until' => '1'],
                'read_at'    => null,
                'created_at' => $now->copy()->subHours(2),
            ],
            [
                'type'       => 'symptoms',
                'title'      => 'Daily Symptom Log',
                'body'       => 'Reminder to log your symptoms for today.',
                'data'       => ['type' => 'symptom_log_reminder'],
                'read_at'    => $now,
                'created_at' => $now->copy()->subHour(),
            ],
            [
                'type'       => 'pregnancy',
                'title'      => 'Weekly Update',
                'body'       => 'You are now 12 weeks pregnant!',
                'data'       => ['type' => 'pregnancy_weekly_update', 'week' => '12'],
                'read_at'    => $now,
                'created_at' => $now->copy()->subDay()->setTime(14, 0),
            ],
            [
                'type'       => 'medication',
                'title'      => 'Medication Reminder',
                'body'       => 'Time to take Folic Acid 5mg.',
                'data'       => ['type' => 'medication_reminder', 'medication' => 'Folic Acid 5mg', 'time' => '08:00 AM'],
                'read_at'    => $now,
                'created_at' => $now->copy()->subHours(2),
            ],
            [
                'type'       => 'general',
                'title'      => 'Clinic Notice',
                'body'       => 'The clinic will be closed on Friday June 13 for maintenance.',
                'data'       => ['type' => 'general'],
                'read_at'    => null,
                'created_at' => $now->copy()->subHours(3),
            ],
        ];

        foreach ($records as $record) {
            PatientNotification::create([
                'patient_id' => $patient->getAttribute('id'),
                ...$record,
            ]);
        }

        // Return same shape as GET /notifications
        $notifications = PatientNotification::where('patient_id', $patient->getAttribute('id'))
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $notifications->map(fn($n) => [
                'id'         => $n->getAttribute('id'),
                'type'       => $n->getAttribute('type'),
                'title'      => $n->getAttribute('title'),
                'body'       => $n->getAttribute('body'),
                'data'       => $n->getAttribute('data'),
                'is_read'    => $n->getAttribute('read_at') !== null,
                'created_at' => $n->created_at->toIso8601String(),
                'time_ago'   => $n->created_at->diffForHumans(),
            ]),
            'meta'    => [
                'unread_count' => PatientNotification::where('patient_id', $patient->getAttribute('id'))
                    ->whereNull('read_at')
                    ->count(),
                'current_page' => $notifications->currentPage(),
                'last_page'    => $notifications->lastPage(),
                'per_page'     => $notifications->perPage(),
                'total'        => $notifications->total(),
            ],
        ]);
    }
}
