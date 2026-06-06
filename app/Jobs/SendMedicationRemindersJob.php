<?php

namespace App\Jobs;

use App\Services\FcmService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Modules\Prescriptions\Models\PatientPrescription;

class SendMedicationRemindersJob implements ShouldQueue
{
    use Queueable;

    // Frequency → how many times per day reminders fire
    private const FREQUENCY_TIMES = [
        'once_daily'        => 1,
        'twice_daily'       => 2,
        'three_times_daily' => 3,
        'four_times_daily'  => 4,
        'every_8_hours'     => 3,
        'every_12_hours'    => 2,
        'weekly'            => 1,
        'as_needed'         => 0,
    ];

    public function __construct(
        // Which reminder slot this run covers: 1st, 2nd, 3rd, or 4th dose of the day
        private readonly int $slot = 1
    ) {}

    public function handle(FcmService $fcm): void
    {
        $today = Carbon::today()->toDateString();

        PatientPrescription::with('patient')
            ->where('status', 'active')
            ->where('patient_reminders', true)
            ->where('start_date', '<=', $today)
            ->where(fn($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', $today))
            ->get()
            ->each(function (PatientPrescription $prescription) use ($fcm) {
                $patient = $prescription->patient;

                if (!$patient || !$patient->getAttribute('fcm_token')) {
                    return;
                }

                $timesPerDay = self::FREQUENCY_TIMES[$prescription->frequency] ?? 0;

                // Only send if this slot is within how many times the medication is taken
                if ($this->slot > $timesPerDay) {
                    return;
                }

                $fcm->sendMedicationReminder(
                    $patient,
                    $prescription->medication_name . ' ' . $prescription->dosage,
                    now()->format('h:i A')
                );
            });
    }
}
