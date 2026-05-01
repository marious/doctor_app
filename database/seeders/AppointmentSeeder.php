<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Modules\Appointments\Models\Appointment;
use Modules\Users\Models\User;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $doctor = User::where('role_id', 1)->first();

        if (!$doctor) {
            $this->command->warn('No doctor found. Run DoctorSeeder first.');
            return;
        }

        $patients = User::where('role_id', 2)->pluck('id', 'name');

        if ($patients->isEmpty()) {
            $this->command->warn('No patients found. Run PatientSeeder first.');
            return;
        }

        $today    = Carbon::today()->toDateString();
        $tomorrow = Carbon::tomorrow()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();
        $lastWeek  = Carbon::now()->subDays(7)->toDateString();
        $lastMonth = Carbon::now()->subDays(30)->toDateString();

        $appointments = [
            // ── Today ─────────────────────────────────────────────────────────
            [
                'patient'          => 'Sarah Jenkins',
                'service_type'     => 'gynecology',
                'visit_type'       => 'appointment',
                'appointment_date' => $today,
                'appointment_time' => '09:00:00',
                'status'           => 'confirmed',
                'confirmed_at'     => now()->subHours(2),
                'notes'            => 'Routine gynecology check-up.',
            ],
            [
                'patient'          => 'Elena Rodriguez',
                'service_type'     => 'pregnant',
                'visit_type'       => 'new_visit',
                'appointment_date' => $today,
                'appointment_time' => '10:30:00',
                'status'           => 'pending',
                'notes'            => 'Fertility consultation, first visit.',
            ],
            [
                'patient'          => 'Chloe Miller',
                'service_type'     => 'pregnant',
                'visit_type'       => 'appointment',
                'appointment_date' => $today,
                'appointment_time' => '11:45:00',
                'status'           => 'confirmed',
                'confirmed_at'     => now()->subHours(3),
                'notes'            => 'Ultrasound follow-up — week 20.',
            ],

            // ── Tomorrow ──────────────────────────────────────────────────────
            [
                'patient'          => 'Grace Lee',
                'service_type'     => 'gynecology',
                'visit_type'       => 'appointment',
                'appointment_date' => $tomorrow,
                'appointment_time' => '14:15:00',
                'status'           => 'cancelled',
                'cancelled_at'     => now()->subHours(5),
                'notes'            => 'General follow-up. Patient requested cancellation.',
            ],
            [
                'patient'          => 'Isabella Garcia',
                'service_type'     => 'gynecology',
                'visit_type'       => 'appointment',
                'appointment_date' => $tomorrow,
                'appointment_time' => '16:00:00',
                'status'           => 'pending',
                'notes'            => 'Prescription renewal request.',
            ],
            [
                'patient'          => 'Nour Hassan',
                'service_type'     => 'pregnant',
                'visit_type'       => 'new_visit',
                'appointment_date' => $tomorrow,
                'appointment_time' => '09:30:00',
                'status'           => 'under_review',
                'notes'            => 'First prenatal visit.',
            ],
            [
                'patient'          => 'Layla Ahmed',
                'service_type'     => 'gynecology',
                'visit_type'       => 'appointment',
                'appointment_date' => $tomorrow,
                'appointment_time' => '11:00:00',
                'status'           => 'confirmed',
                'confirmed_at'     => now()->subHour(),
                'notes'            => 'Post-treatment follow-up.',
            ],

            // ── Yesterday ─────────────────────────────────────────────────────
            [
                'patient'          => 'Mona Ibrahim',
                'service_type'     => 'pregnant',
                'visit_type'       => 'appointment',
                'appointment_date' => $yesterday,
                'appointment_time' => '10:00:00',
                'status'           => 'completed',
                'confirmed_at'     => Carbon::yesterday()->subHours(2),
                'diagnosis'        => 'Normal pregnancy progression. Week 18.',
                'notes'            => 'Prenatal check-up.',
            ],
            [
                'patient'          => 'Rania Mostafa',
                'service_type'     => 'gynecology',
                'visit_type'       => 'appointment',
                'appointment_date' => $yesterday,
                'appointment_time' => '14:00:00',
                'status'           => 'not_approved',
                'cancelled_at'     => Carbon::yesterday()->subHour(),
                'notes'            => 'Requested slot unavailable.',
            ],

            // ── Last week ─────────────────────────────────────────────────────
            [
                'patient'          => 'Dina Farouk',
                'service_type'     => 'gynecology',
                'visit_type'       => 'new_visit',
                'appointment_date' => $lastWeek,
                'appointment_time' => '09:00:00',
                'status'           => 'completed',
                'confirmed_at'     => Carbon::now()->subDays(7)->subHours(1),
                'diagnosis'        => 'Routine check — no concerns.',
                'notes'            => 'Annual gynecology exam.',
            ],
            [
                'patient'          => 'Sarah Jenkins',
                'service_type'     => 'gynecology',
                'visit_type'       => 'appointment',
                'appointment_date' => $lastWeek,
                'appointment_time' => '11:30:00',
                'status'           => 'completed',
                'confirmed_at'     => Carbon::now()->subDays(7)->subHours(2),
                'diagnosis'        => 'Mild infection, prescribed antibiotics.',
                'notes'            => 'Follow-up from previous visit.',
            ],

            // ── Last month ────────────────────────────────────────────────────
            [
                'patient'          => 'Elena Rodriguez',
                'service_type'     => 'pregnant',
                'visit_type'       => 'appointment',
                'appointment_date' => $lastMonth,
                'appointment_time' => '10:00:00',
                'status'           => 'completed',
                'confirmed_at'     => Carbon::now()->subDays(30)->subHours(1),
                'diagnosis'        => 'Healthy fetal development. Week 12.',
                'notes'            => 'First trimester check-up.',
            ],
        ];

        foreach ($appointments as $data) {
            $patientName = $data['patient'];
            $patientId   = $patients->get($patientName);

            if (!$patientId) {
                $this->command->warn("Patient not found: {$patientName}");
                continue;
            }

            Appointment::create([
                'patient_id'       => $patientId,
                'doctor_id'        => $doctor->id,
                'service_type'     => $data['service_type'],
                'visit_type'       => $data['visit_type'],
                'appointment_date' => $data['appointment_date'],
                'appointment_time' => $data['appointment_time'],
                'status'           => $data['status'],
                'notes'            => $data['notes'] ?? null,
                'diagnosis'        => $data['diagnosis'] ?? null,
                'confirmed_at'     => $data['confirmed_at'] ?? null,
                'cancelled_at'     => $data['cancelled_at'] ?? null,
            ]);
        }

        $this->command->info('Appointments seeded: ' . count($appointments) . ' records.');
    }
}
