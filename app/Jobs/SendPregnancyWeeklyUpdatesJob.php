<?php

namespace App\Jobs;

use App\Services\FcmService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Modules\Patient\Models\PatientTracking;

class SendPregnancyWeeklyUpdatesJob implements ShouldQueue
{
    use Queueable;

    public function handle(FcmService $fcm): void
    {
        // Find all active pregnancy trackings
        PatientTracking::with('patient')
            ->where('tracking_type', 'pregnancy')
            ->whereNotNull('lmp_date')
            ->get()
            ->each(function (PatientTracking $tracking) use ($fcm) {
                $patient = $tracking->patient;

                if (!$patient || !$patient->getAttribute('fcm_token')) {
                    return;
                }

                $lmp      = Carbon::parse($tracking->lmp_date);
                $daysIn   = $lmp->diffInDays(Carbon::today());

                // Only notify on exact week boundaries (day 7, 14, 21 … up to 280)
                if ($daysIn % 7 !== 0 || $daysIn === 0) {
                    return;
                }

                $week = (int) ($daysIn / 7);

                // Pregnancy is 40 weeks; ignore beyond that
                if ($week > 40) {
                    return;
                }

                $fcm->sendPregnancyWeeklyUpdate($patient, $week);
            });
    }
}
