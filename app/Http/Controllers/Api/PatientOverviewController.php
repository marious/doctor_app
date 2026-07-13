<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Modules\Appointments\Models\Appointment;
use Modules\Patient\Models\CycleSymptomLog;
use Modules\Patient\Models\PatientTracking;
use Modules\Prescriptions\Models\PatientPrescription;
use Modules\Sessions\Models\PatientSession;
use Modules\Users\Models\User;

class PatientOverviewController extends Controller
{
    /**
     * GET /doctor/patients/{patient}/overview
     * Patient overview: info, pregnancy progress, health stats, symptom trends, active prescriptions.
     */
    public function __invoke(User $patient): JsonResponse
    {
        abort_if($patient->role_id !== 2, 404);

        $patient->load([
            'patientTracking.latestHealthStat',
            'lastCompletedAppointment',
        ]);

        $tracking = $patient->patientTracking;
        $latestStat = $tracking?->latestHealthStat;
        $isPregnancy = $tracking?->tracking_type === 'pregnancy';

        $latestSession = PatientSession::where('patient_id', $patient->id)
            ->whereNotNull('bp')
            ->orderByDesc('session_date')
            ->first();

        $previousSession = null;
        if ($latestSession) {
            $previousSession = PatientSession::where('patient_id', $patient->id)
                ->where('id', '<', $latestSession->id)
                ->orderByDesc('session_date')
                ->first();    
        }
        
        $nextAppointment = Appointment::where('patient_id', $patient->id)
            ->whereIn('status', ['confirmed', 'pending'])
            ->where('appointment_date', '>=', today())
            ->orderBy('appointment_date')
            ->first();

        $activePrescriptions = PatientPrescription::where('patient_id', $patient->id)
            ->where('status', 'active')
            ->orderByDesc('start_date')
            ->get()
            ->filter(fn($p) => $p->resolveStatus() === 'active')
            ->take(4)
            ->values();

        $weekDays = collect(CarbonPeriod::create(today()->subDays(6), today())->toArray());

        $symptomCounts = array_fill(0, 7, 0);
        if ($tracking) {
            $symptomLogs = CycleSymptomLog::where('patient_tracking_id', $tracking->id)
                ->whereBetween('logged_date', [today()->subDays(6), today()])
                ->get()
                ->groupBy(fn($log) => Carbon::parse($log->logged_date)->toDateString());

            $symptomCounts = $weekDays->map(
                fn($day) => $symptomLogs->get($day->toDateString(), collect())->count()
            )->values()->toArray();
        }

        $babySize = $isPregnancy ? $tracking->babySizeByWeek() : null;

        return response()->json([
            'success' => true,
            'data' => [
                'patient' => [
                    'id'           => $patient->id,
                    'patient_code' => '#P-' . str_pad($patient->id, 3, '0', STR_PAD_LEFT),
                    'name'         => $patient->name,
                    'avatar'       => $patient->getFirstMediaUrl('avatar') ?: null,
                    'age'          => $patient->age,
                    'condition'    => $isPregnancy ? 'Pregnancy' : 'Menstrual',
                    'risk_status'  => $patient->risk_status,
                    'blood_group'  => $patient->blood_group,
                    'phone'        => $patient->phone,
                    'last_visit'   => $patient->lastCompletedAppointment?->appointment_date?->format('M d, Y'),
                    'next_appointment' => $nextAppointment ? [
                        'date'      => $nextAppointment->appointment_date->toDateString(),
                        'formatted' => $nextAppointment->appointment_date->format('M d, Y'),
                        'time'      => $nextAppointment->appointment_time,
                        'status'    => $nextAppointment->status,
                    ] : null,
                ],

                'pregnancy_progress' => $isPregnancy ? [
                    'current_week'                 => $tracking->gestationalWeeks(),
                    'total_weeks'                  => $tracking->totalWeeks(),
                    'trimester'                    => $tracking->trimester(),
                    'estimated_due_date'           => $tracking->estimated_due_date?->toDateString(),
                    'estimated_due_date_formatted' => $tracking->estimated_due_date?->format('M d, Y'),
                    'days_remaining'               => $tracking->daysRemaining(),
                    'progress_percent'             => $tracking->progressPercent(),
                    'conception_date'              => $tracking->lmp_date?->toDateString(),
                ] : null,

                'health_stats' => [
                    'blood_pressure' => $latestSession?->bp ? [
                        'value'  => $latestSession->bp,
                        'unit'   => 'mmHg',
                        'status' => $this->evaluateBp($latestSession->bp),
                    ] : null,
                    'weight' => ($latestStat?->weight_kg || $latestSession?->weight) ? [
                        'value'  => $latestStat->weight_kg ?? $latestSession?->weight,
                        'unit'   => 'kg',
                        'change' =>  $previousSession ? $this->weightChangePerSession($latestSession?->weight, $previousSession?->weight) : $this->weightChange($tracking),
                        'status' => $latestStat?->weight_status ?? null,
                    ] : null,
                    'heart_rate' => ($latestStat?->bpm || $latestSession?->hr) ? [
                        'value'  => $latestStat?->bpm ?? (int) $latestSession?->hr,
                        'unit'   => 'bpm',
                        'status' => 'resting',
                    ] : null,
                    'fetal_weight' => ($babySize && $babySize['weight_g']) ? [
                        'value'     => round($babySize['weight_g'] / 1000, 2),
                        'unit'      => 'kg',
                        'estimated' => true,
                    ] : null,
                ],

                'symptom_trends' => [
                    'labels' => $weekDays->map(fn($d) => $d->format('D')[0])->values()->toArray(),
                    'data'   => $symptomCounts,
                ],

                'active_prescriptions' => $activePrescriptions->map(fn($p) => [
                    'id'              => $p->id,
                    'medication_name' => $p->medication_name,
                    'dosage'          => $p->dosage,
                    'frequency'       => $p->frequency,
                    'frequency_label' => PatientPrescription::$frequencyLabels[$p->frequency] ?? $p->frequency,
                    'status'          => $p->resolveStatus(),
                ])->values(),
            ],
        ]);
    }

    private function evaluateBp(string $bp): string
    {
        [$systolic] = array_map('intval', explode('/', $bp));

        if ($systolic < 120) return 'normal';
        if ($systolic < 130) return 'elevated';
        if ($systolic < 140) return 'high_stage_1';
        return 'high_stage_2';
    }

    private function weightChange(?PatientTracking $tracking): ?string
    {
        if (!$tracking) return null;

        $stats = $tracking->healthStats()
            ->orderByDesc('recorded_at')
            ->take(2)
            ->get();

        if ($stats->count() < 2) return null;

        $change = round($stats->first()->weight_kg - $stats->last()->weight_kg, 1);

        return ($change >= 0 ? '+' : '') . $change;
    }

    private function weightChangePerSession($latestSessionWeight = null, $previousSessionWeight = null)
    {
        if ($latestSessionWeight && $previousSessionWeight) {
            $change = round($latestSessionWeight - $previousSessionWeight, 1);
            return ($change >= 0 ? '+' : '') . $change;
        }
        return null;
    }
}
