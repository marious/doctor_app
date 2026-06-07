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

    public function sendAppointmentRequested(User $patient, string $date, string $time): void
    {
        $this->send(
            $patient,
            'appointment_reminders',
            'Appointment Request',
            "Your appointment request for {$date} at {$time} is pending approval.",
            ['type' => 'appointment_requested', 'date' => $date, 'time' => $time]
        );
    }

    public function sendAppointmentApproved(User $patient, string $date, string $time): void
    {
        $this->send(
            $patient,
            'appointment_reminders',
            'Appointment Approved',
            "Your appointment on {$date} at {$time} has been approved.",
            ['type' => 'appointment_approved', 'date' => $date, 'time' => $time]
        );
    }

    public function sendAppointmentDeclined(User $patient, string $date, string $time): void
    {
        $this->send(
            $patient,
            'appointment_reminders',
            'Appointment Declined',
            "Your appointment on {$date} at {$time} has been declined.",
            ['type' => 'appointment_declined', 'date' => $date, 'time' => $time]
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
            'Weekly Update',
            "You are now {$week} weeks pregnant!",
            ['type' => 'pregnancy_weekly_update', 'week' => (string) $week]
        );
    }

    public function sendPeriodAlert(User $patient, int $daysUntil): void
    {
        $body = match ($daysUntil) {
            0       => 'Your period is expected today. Take care of yourself!',
            1       => 'Your period starts tomorrow. Prepare your essentials!',
            default => "Your period starts in {$daysUntil} days. Prepare your essentials!",
        };

        $this->send(
            $patient,
            'period_alerts',
            'Cycle Reminder',
            $body,
            ['type' => 'period_alert', 'days_until' => (string) $daysUntil]
        );
    }

    public function sendOvulationAlert(User $patient, int $daysUntil): void
    {
        $body = match ($daysUntil) {
            0       => 'Your ovulation window starts today.',
            1       => 'Your ovulation window starts tomorrow.',
            default => "Your ovulation window starts in {$daysUntil} days.",
        };

        $this->send(
            $patient,
            'period_alerts',
            'Ovulation Reminder',
            $body,
            ['type' => 'ovulation_alert', 'days_until' => (string) $daysUntil],
            'fertility'
        );
    }

    public function sendSymptomLogReminder(User $patient): void
    {
        $this->send(
            $patient,
            'push_notifications',
            'Daily Symptom Log',
            'Reminder to log your symptoms for today.',
            ['type' => 'symptom_log_reminder'],
            'symptoms'
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

    private function send(User $patient, string $settingKey, string $title, string $body, array $data = [], ?string $dbType = null): void
    {
        if (!$this->canNotify($patient, $settingKey)) {
            return;
        }

        // Always persist to DB regardless of whether the device has a token
        PatientNotification::create([
            'patient_id' => $patient->getAttribute('id'),
            'type'       => $dbType ?? self::SETTING_TO_TYPE[$settingKey] ?? 'general',
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
