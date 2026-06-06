<?php

namespace App\Jobs;

use App\Services\FcmService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Modules\Appointments\Models\Appointment;

class SendAppointmentRemindersJob implements ShouldQueue
{
    use Queueable;

    public function handle(FcmService $fcm): void
    {
        $tomorrow = Carbon::tomorrow()->toDateString();

        Appointment::with('patient')
            ->where('appointment_date', $tomorrow)
            ->whereIn('status', ['confirmed', 'pending', 'under_review'])
            ->get()
            ->each(function (Appointment $appointment) use ($fcm) {
                $patient = $appointment->patient;

                if (!$patient || !$patient->getAttribute('fcm_token')) {
                    return;
                }

                $fcm->sendAppointmentReminder(
                    $patient,
                    $appointment->appointment_date->format('M d, Y'),
                    substr($appointment->appointment_time, 0, 5)
                );
            });
    }
}
