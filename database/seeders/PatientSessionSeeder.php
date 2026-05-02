<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Modules\Sessions\Models\PatientSession;
use Modules\Users\Models\User;

class PatientSessionSeeder extends Seeder
{
    public function run(): void
    {
        $doctor = User::where('role_id', 1)->first();

        if (!$doctor) {
            $this->command->warn('No doctor found. Run DoctorSeeder first.');
            return;
        }

        $patients = User::where('role_id', 2)->pluck('id', 'email');

        if ($patients->isEmpty()) {
            $this->command->warn('No patients found. Run PatientSeeder first.');
            return;
        }

        $p = fn(string $email) => $patients->get($email);

        $sessions = [
            // ── Sarah Jenkins (High Risk, Pregnancy) ─────────────────────────
            [
                'patient_email'            => 'sarah.jenkins@example.com',
                'session_date'             => '2026-02-15',
                'visit_type'               => 'new_visit',
                'risk_status'              => 'high_risk',
                'symptoms'                 => 'Severe morning sickness, fatigue, mild spotting',
                'diagnosis'                => 'Early pregnancy — week 8. Mild hyperemesis gravidarum. Spotting requires close monitoring.',
                'treatment_plan'           => 'Prescribe anti-nausea medication. Increase fluid intake. Bed rest advised. Repeat ultrasound in 2 weeks.',
                'ultrasound_notes'         => 'Single intrauterine gestational sac. Fetal heartbeat detected at 160 bpm. Crown-rump length consistent with 8 weeks.',
                'lab_results_notes'        => 'HCG levels elevated as expected. Hemoglobin slightly low at 10.2 g/dL.',
                'pelvic_examination_notes' => 'Cervix closed. Mild uterine tenderness noted.',
                'required_lab_tests'       => ['CBC', 'Blood Sugar', 'Hormone Test'],
                'quick_notes'              => 'Patient anxious about spotting. Reassured. Husband present during consultation.',
                'medications'              => 'Metoclopramide 10mg, Folic Acid 5mg, Iron Supplement',
                'follow_up_date'           => '2026-03-01',
                'private_doctor_notes'     => 'High risk due to previous miscarriage. Consider referral to maternal-fetal medicine if spotting persists.',
            ],
            [
                'patient_email'            => 'sarah.jenkins@example.com',
                'session_date'             => '2026-03-01',
                'visit_type'               => 'follow_up',
                'risk_status'              => 'high_risk',
                'symptoms'                 => 'Reduced morning sickness, mild back pain',
                'diagnosis'                => 'Pregnancy — week 10. Spotting resolved. Progression normal.',
                'treatment_plan'           => 'Continue iron supplementation. Light walking encouraged. Next ultrasound at week 12.',
                'ultrasound_notes'         => 'Fetal heartbeat strong at 170 bpm. No subchorionic hematoma detected.',
                'lab_results_notes'        => 'Hemoglobin improved to 11.4 g/dL after iron therapy.',
                'required_lab_tests'       => ['CBC', 'Urine Analysis'],
                'quick_notes'              => 'Patient reports feeling much better. Spotting completely stopped.',
                'medications'              => 'Folic Acid 5mg, Iron Supplement, Vitamin D3',
                'follow_up_date'           => '2026-03-20',
                'private_doctor_notes'     => 'Patient responding well. Downgrade risk if week 12 scan is clear.',
            ],

            // ── Mia Thompson (Stable, Pregnancy) ─────────────────────────────
            [
                'patient_email'            => 'mia.thompson@example.com',
                'session_date'             => '2026-02-10',
                'visit_type'               => 'follow_up',
                'risk_status'              => 'stable',
                'symptoms'                 => 'Mild swelling in feet, occasional heartburn',
                'diagnosis'                => 'Pregnancy — week 28. Third trimester. Normal progression.',
                'treatment_plan'           => 'Elevate feet when resting. Antacids for heartburn. Kick count monitoring daily.',
                'ultrasound_notes'         => 'Fetal weight estimated at 1.1 kg. Amniotic fluid index normal. Placenta posterior.',
                'lab_results_notes'        => 'Glucose tolerance test passed. All values within normal range.',
                'required_lab_tests'       => ['Blood Sugar', 'Urine Analysis'],
                'quick_notes'              => 'Baby movements felt regularly by mother. No concerns.',
                'medications'              => 'Calcium supplement, Magnesium, Omega-3',
                'follow_up_date'           => '2026-02-24',
                'private_doctor_notes'     => 'Routine third trimester. Schedule GBS test at week 36.',
            ],

            // ── Sophia Davis (Monitor, Pregnancy) ────────────────────────────
            [
                'patient_email'            => 'sophia.davis@example.com',
                'session_date'             => '2026-02-18',
                'visit_type'               => 'follow_up',
                'risk_status'              => 'monitor',
                'symptoms'                 => 'Elevated blood pressure readings at home, mild headache',
                'diagnosis'                => 'Pregnancy — week 32. Gestational hypertension suspected. Requires close monitoring.',
                'treatment_plan'           => 'Daily blood pressure logging. Reduce sodium intake. Immediate visit if BP exceeds 140/90. NST twice weekly.',
                'ultrasound_notes'         => 'Fetal growth appropriate for gestational age. Doppler flow normal.',
                'lab_results_notes'        => 'Proteinuria trace positive. Platelet count normal. Liver enzymes normal.',
                'required_lab_tests'       => ['CBC', 'Urine Analysis', 'Thyroid Function Test'],
                'quick_notes'              => 'Patient instructed on pre-eclampsia warning signs. Emergency contact given.',
                'medications'              => 'Low-dose aspirin 75mg, Calcium supplement 1g',
                'follow_up_date'           => '2026-02-25',
                'private_doctor_notes'     => 'Monitor closely for progression to pre-eclampsia. Consider early delivery at 37 weeks if BP uncontrolled.',
            ],

            // ── Emma Wilson (Stable, Period) ──────────────────────────────────
            [
                'patient_email'            => 'emma.wilson@example.com',
                'session_date'             => '2026-01-20',
                'visit_type'               => 'new_visit',
                'risk_status'              => 'stable',
                'symptoms'                 => 'Irregular periods, cycles ranging 21-35 days, mild cramping',
                'diagnosis'                => 'Oligomenorrhea. Possible PCOS — further investigation needed.',
                'treatment_plan'           => 'Hormonal panel ordered. Pelvic ultrasound for ovarian morphology. Lifestyle modifications: weight management and regular exercise.',
                'pelvic_examination_notes' => 'Mild ovarian enlargement bilaterally. No masses palpated.',
                'required_lab_tests'       => ['Hormone Test', 'Blood Sugar', 'Thyroid Function Test'],
                'quick_notes'              => 'Patient reports hair thinning and weight gain over past 6 months — consistent with PCOS.',
                'medications'              => 'Folic Acid 400mcg, Vitamin D3',
                'follow_up_date'           => '2026-02-10',
                'private_doctor_notes'     => 'PCOS likely. Await hormonal results before starting treatment.',
            ],

            // ── Olivia Brown (Stable, Period) ─────────────────────────────────
            [
                'patient_email'            => 'olivia.brown@example.com',
                'session_date'             => '2025-12-12',
                'visit_type'               => 'follow_up',
                'risk_status'              => 'stable',
                'symptoms'                 => 'Mild dysmenorrhea, regular 28-day cycle',
                'diagnosis'                => 'Primary dysmenorrhea. No underlying pathology identified.',
                'treatment_plan'           => 'NSAIDs as needed during first 2 days of period. Heat therapy. Reassess if pain worsens.',
                'required_lab_tests'       => ['CBC'],
                'quick_notes'              => 'Patient doing well. Pain manageable with ibuprofen.',
                'medications'              => 'Ibuprofen 400mg as needed',
                'follow_up_date'           => '2026-03-12',
                'private_doctor_notes'     => 'Routine follow-up. No red flags.',
            ],

            // ── Nour Hassan (Monitor) ─────────────────────────────────────────
            [
                'patient_email'            => 'nour.hassan@example.com',
                'session_date'             => Carbon::now()->subDays(14)->toDateString(),
                'visit_type'               => 'consultation',
                'risk_status'              => 'monitor',
                'symptoms'                 => 'Recurrent pelvic pain, heavy menstrual bleeding (>7 days)',
                'diagnosis'                => 'Menorrhagia. Endometriosis suspected based on symptom pattern.',
                'treatment_plan'           => 'Laparoscopy scheduled. Hormonal therapy initiated to manage symptoms in the interim.',
                'pelvic_examination_notes' => 'Uterus retroverted. Tenderness in posterior fornix. Nodularity in uterosacral ligaments.',
                'required_lab_tests'       => ['CBC', 'Hormone Test'],
                'quick_notes'              => 'Patient reports symptoms are significantly impacting daily life and work.',
                'medications'              => 'Norethisterone 5mg, Iron Supplement, Tranexamic Acid',
                'follow_up_date'           => Carbon::now()->addDays(14)->toDateString(),
                'private_doctor_notes'     => 'Strong clinical suspicion for endometriosis. Awaiting laparoscopy slot.',
            ],

            // ── Rania Mostafa (High Risk) ─────────────────────────────────────
            [
                'patient_email'            => 'rania.mostafa@example.com',
                'session_date'             => Carbon::now()->subDays(5)->toDateString(),
                'visit_type'               => 'emergency',
                'risk_status'              => 'high_risk',
                'symptoms'                 => 'Sudden severe pelvic pain, nausea, light-headedness',
                'diagnosis'                => 'Suspected ovarian torsion. Urgent surgical consultation required.',
                'treatment_plan'           => 'Immediate surgical referral. Patient admitted for observation. IV fluids started.',
                'ultrasound_notes'         => 'Right ovary enlarged at 6.2 cm. Absent Doppler flow — consistent with torsion.',
                'required_lab_tests'       => ['CBC', 'Blood Sugar', 'Urine Analysis'],
                'quick_notes'              => 'Patient in significant distress. Surgical team notified immediately.',
                'medications'              => 'IV Morphine 4mg, IV Ondansetron 4mg, IV Normal Saline',
                'private_doctor_notes'     => 'Operated same day. Torsion confirmed. Right ovary detorsed successfully. Follow-up in 1 week.',
            ],
        ];

        $count = 0;
        foreach ($sessions as $data) {
            $patientId = $p($data['patient_email']);

            if (!$patientId) {
                $this->command->warn("Patient not found: {$data['patient_email']}");
                continue;
            }

            PatientSession::create([
                'patient_id'               => $patientId,
                'doctor_id'                => $doctor->id,
                'session_date'             => $data['session_date'],
                'visit_type'               => $data['visit_type'],
                'risk_status'              => $data['risk_status'] ?? null,
                'symptoms'                 => $data['symptoms'] ?? null,
                'diagnosis'                => $data['diagnosis'],
                'treatment_plan'           => $data['treatment_plan'] ?? null,
                'ultrasound_notes'         => $data['ultrasound_notes'] ?? null,
                'lab_results_notes'        => $data['lab_results_notes'] ?? null,
                'pelvic_examination_notes' => $data['pelvic_examination_notes'] ?? null,
                'required_lab_tests'       => $data['required_lab_tests'] ?? null,
                'quick_notes'              => $data['quick_notes'] ?? null,
                'medications'              => $data['medications'] ?? null,
                'follow_up_date'           => $data['follow_up_date'] ?? null,
                'private_doctor_notes'     => $data['private_doctor_notes'] ?? null,
            ]);

            $count++;
        }

        $this->command->info("Patient sessions seeded: {$count} records.");
    }
}
