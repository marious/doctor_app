<?php

use App\Jobs\SendAppointmentRemindersJob;
use App\Jobs\SendMedicationRemindersJob;
use App\Jobs\SendPeriodAlertsJob;
use App\Jobs\SendPregnancyWeeklyUpdatesJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ─── Push Notification Schedule ───────────────────────────────────────────────

// Appointment reminders — 9 AM daily, covers all appointments the next day
Schedule::job(new SendAppointmentRemindersJob())->dailyAt('09:00');

// Medication reminders — up to 4 slots per day based on prescription frequency
Schedule::job(new SendMedicationRemindersJob(slot: 1))->dailyAt('08:00');  // morning
Schedule::job(new SendMedicationRemindersJob(slot: 2))->dailyAt('13:00');  // afternoon
Schedule::job(new SendMedicationRemindersJob(slot: 3))->dailyAt('18:00');  // evening
Schedule::job(new SendMedicationRemindersJob(slot: 4))->dailyAt('21:00');  // night

// Pregnancy weekly updates — 8 AM daily (only fires for patients on a week boundary)
Schedule::job(new SendPregnancyWeeklyUpdatesJob())->dailyAt('08:00');

// Period alerts — 9 AM daily (fires when period is 1 or 3 days away)
Schedule::job(new SendPeriodAlertsJob())->dailyAt('09:00');
