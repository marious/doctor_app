<?php

namespace App\Services;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Modules\Notifications\Models\PatientNotification;
use Modules\Users\Models\User;

class FcmService
{
    public function __construct(private readonly Messaging $messaging) {}

    // ─── Notification types matching the app settings screen ─────────────────

    public function sendAppointmentReminder(User $patient, string $appointmentDate, string $appointmentTime): void
    {
        $this->send(
            $patient,
            'appointment_reminders',
            'Appointment Reminder',
            "Your appointment is on {$appointmentDate} at {$appointmentTime}.",
            ['type' => 'appointment_reminder', 'date' => $appointmentDate, 'time' => $appointmentTime]
        );
    }

    public function sendAppointmentStatusChanged(User $patient, string $status): void
    {
        $labels = [
            'confirmed'    => 'Your appointment has been confirmed.',
            'not_approved' => 'Your appointment was not approved.',
            'cancelled'    => 'Your appointment has been cancelled.',
        ];

        $this->send(
            $patient,
            'appointment_reminders',
            'Appointment Update',
            $labels[$status] ?? 'Your appointment status has changed.',
            ['type' => 'appointment_status', 'status' => $status]
        );
    }

    public function sendAppointmentRescheduled(User $patient, string $newDate, string $newTime): void
    {
        $this->send(
            $patient,
            'appointment_reminders',
            'Appointment Rescheduled',
            "Your appointment has been moved to {$newDate} at {$newTime}.",
            ['type' => 'appointment_rescheduled', 'date' => $newDate, 'time' => $newTime]
        );
    }

    public function sendMedicationReminder(User $patient, string $medicationName, string $time): void
    {
        $this->send(
            $patient,
            'medication_reminders',
            'Medication Reminder',
            "Time to take {$medicationName}.",
            ['type' => 'medication_reminder', 'medication' => $medicationName, 'time' => $time]
        );
    }

    public function sendPregnancyWeeklyUpdate(User $patient, int $week): void
    {
        $this->send(
            $patient,
            'pregnancy_weekly_updates',
            "Week {$week} Update",
            "You are now {$week} weeks pregnant. Check your weekly update!",
            ['type' => 'pregnancy_weekly_update', 'week' => (string) $week]
        );
    }

    public function sendPeriodAlert(User $patient, int $daysUntil): void
    {
        $message = $daysUntil === 0
            ? 'Your period is expected today.'
            : "Your period is expected in {$daysUntil} " . ($daysUntil === 1 ? 'day.' : 'days.');

        $this->send(
            $patient,
            'period_alerts',
            'Period Alert',
            $message,
            ['type' => 'period_alert', 'days_until' => (string) $daysUntil]
        );
    }

    public function sendCustom(User $patient, string $title, string $body, array $data = []): void
    {
        $this->send($patient, 'push_notifications', $title, $body, $data);
    }

    // ─── Core sender ─────────────────────────────────────────────────────────

    private const SETTING_TO_TYPE = [
        'appointment_reminders'   => 'appointment',
        'medication_reminders'    => 'medication',
        'pregnancy_weekly_updates'=> 'pregnancy',
        'period_alerts'           => 'period',
        'push_notifications'      => 'general',
    ];

    private function send(User $patient, string $settingKey, string $title, string $body, array $data = []): void
    {
        if (!$this->canNotify($patient, $settingKey)) {
            return;
        }

        // Always persist to DB regardless of whether the device has a token
        PatientNotification::create([
            'patient_id' => $patient->getAttribute('id'),
            'type'       => self::SETTING_TO_TYPE[$settingKey] ?? 'general',
            'title'      => $title,
            'body'       => $body,
            'data'       => $data ?: null,
        ]);

        $token = $patient->getAttribute('fcm_token');

        if (!$token) {
            return;
        }

        $message = CloudMessage::new()
            ->withToken($token)
            ->withNotification(Notification::create($title, $body))
            ->withData(array_map('strval', $data));

        $this->messaging->send($message);
    }

    private function canNotify(User $patient, string $settingKey): bool
    {
        if (!$patient->getAttribute('notification_enabled')) {
            return false;
        }

        $settings = $patient->getAttribute('settings') ?? [];

        // If the key is not explicitly set, default to enabled
        return (bool) ($settings[$settingKey] ?? true);
    }
}
