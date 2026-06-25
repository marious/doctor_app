<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Appointments\Models\Appointment;
use Modules\Chat\Models\Conversation;
use Modules\Chat\Models\Message;
use Modules\Users\Models\User;

class DoctorDashboardController extends Controller
{
    /**
     * GET /doctor/dashboard
     * Snapshot stats + today's schedule for the doctor home page.
     */
    public function __invoke(): JsonResponse
    {
        $today = today();
        $weekStart = now()->startOfWeek();

        $todaysAppointments = Appointment::whereDate('appointment_date', $today)->count();
        $todaysPending = Appointment::whereDate('appointment_date', $today)
            ->whereIn('status', ['pending', 'under_review'])
            ->count();

        $highRiskPatients = User::where('role_id', 2)->where('risk_status', 'high_risk')->count();

        $totalPatients = User::where('role_id', 2)->count();
        $newPatientsThisWeek = User::where('role_id', 2)->where('created_at', '>=', $weekStart)->count();

        $conversationIds = Conversation::where('staff_id', Auth::id())->pluck('id');

        $newMessagesToday = Message::whereIn('conversation_id', $conversationIds)
            ->whereNot('sender_id', Auth::id())
            ->whereDate('created_at', $today)
            ->count();

        $unreadMessages = Message::whereIn('conversation_id', $conversationIds)
            ->whereNot('sender_id', Auth::id())
            ->whereNull('read_at')
            ->count();

        $schedule = Appointment::with('patient')
            ->whereDate('appointment_date', $today)
            ->whereNotIn('status', ['cancelled', 'not_approved'])
            ->orderBy('appointment_time')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'todays_appointments'         => $todaysAppointments,
                    'todays_appointments_pending' => $todaysPending,
                    'high_risk_patients'          => $highRiskPatients,
                    'new_messages_today'          => $newMessagesToday,
                    'unread_messages'             => $unreadMessages,
                    'total_patients'              => $totalPatients,
                    'new_patients_this_week'      => $newPatientsThisWeek,
                ],
                'todays_schedule' => $schedule->map(fn (Appointment $a) => [
                    'id'         => $a->id,
                    'patient'    => [
                        'id'     => $a->patient?->id,
                        'name'   => $a->patient?->name,
                        'avatar' => $a->patient?->getFirstMediaUrl('avatar') ?: null,
                    ],
                    'time'       => Carbon::createFromFormat('H:i:s', $a->appointment_time)->format('h:i A'),
                    'visit_type' => $a->service_type === 'pregnant' ? __('Prenatal Check-up') : __('Gynecology Check-up'),
                    'status'     => $a->status === 'confirmed' ? 'approved' : $a->status,
                ])->values(),
            ],
        ]);
    }
}
