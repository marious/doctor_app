<?php

namespace App\Jobs;

use App\Services\FcmService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Modules\Patient\Models\PatientTracking;

class SendPeriodAlertsJob implements ShouldQueue
{
    use Queueable;

    // Notify when period is this many days away
    private const ALERT_DAYS = [3, 1];

    public function handle(FcmService $fcm): void
    {
        $today = Carbon::today();

        PatientTracking::with('patient')
            ->where('tracking_type', 'menstrual')
            ->whereNotNull('next_period_date')
            ->get()
            ->each(function (PatientTracking $tracking) use ($fcm, $today) {
                $patient = $tracking->patient;

                if (!$patient || !$patient->getAttribute('fcm_token')) {
                    return;
                }

                $nextPeriod = Carbon::parse($tracking->next_period_date);
                $daysUntil  = (int) $today->diffInDays($nextPeriod, false);

                if (!in_array($daysUntil, self::ALERT_DAYS, true)) {
                    return;
                }

                $fcm->sendPeriodAlert($patient, $daysUntil);
            });
    }
}
