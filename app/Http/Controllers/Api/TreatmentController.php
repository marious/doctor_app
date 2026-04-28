<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreTreatmentLogRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Appointments\Models\Appointment;
use Modules\Appointments\Models\Prescription;
use Modules\Patient\Models\HydrationLog;
use Modules\Treatments\Models\TreatmentLog;
use Modules\Treatments\Resources\TreatmentResource;

class TreatmentController extends Controller
{
    const CUPS_GOAL = 8;
    const ML_PER_CUP = 250;

    /**
     * Full treatment screen for a completed appointment.
     * Omit the appointment ID to automatically use the patient's latest appointment.
     */
    public function show(?Appointment $appointment = null): JsonResponse
    {
        $patientId = Auth::id();

        if ($appointment === null) {
            $appointment = Appointment::where('patient_id', $patientId)
                ->latest('appointment_date')
                ->first();

            abort_if($appointment === null, 404, __('No appointment found.'));
        }

        abort_if(
            $appointment->patient_id !== $patientId,
            403,
            __('You are not authorized to access this appointment.')
        );

        $patientId = Auth::id();

        $appointment->load(['prescriptions', 'requestedTests']);

        // Today's hydration (auto-create empty record if first access today)
        $hydration = HydrationLog::firstOrCreate(
            ['patient_id' => $patientId, 'date' => today()->toDateString()],
            ['cups_count' => 0]
        );

        // Next upcoming appointment for the follow-up section
        $nextAppointment = Appointment::where('patient_id', $patientId)
            ->where('id', '!=', $appointment->id)
            ->whereIn('status', ['pending', 'under_review', 'confirmed'])
            ->where('appointment_date', '>=', today())
            ->orderBy('appointment_date')
            ->first();

        // Prescriptions formatted for the UI
        $medications = $appointment->prescriptions->map(fn($p) => [
            'id'              => $p->id,
            'medication_name' => $p->medication_name,
            'dose_strength'   => $p->dose_strength,
            'frequency'       => $this->dosageToFrequencyLabel($p->dosage),
            'timing'          => $p->frequency, // stored in the frequency column
            'duration_days'   => $p->duration_days,
            'warning_note'    => $p->warning_note,
        ]);

        // Split requested tests into lab tests and scans
        $labTests = $appointment->requestedTests
            ->where('type', 'lab')
            ->pluck('test_name')
            ->values();

        $scans = $appointment->requestedTests
            ->where('type', 'scan')
            ->pluck('test_name')
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'id'               => $appointment->id,
                'appointment_date' => $appointment->appointment_date?->toDateString(),
                'doctor'           => config('app.doctor_name'),
                'clinic'           => config('app.clinic_name'),
                'diagnosis'        => $appointment->diagnosis,
                'hydration'        => $this->formatHydration($hydration),
                'prescribed_medications'    => $medications,
                'additional_instructions'   => $appointment->additional_instructions ?? [],
                'follow_up' => [
                    'next_appointment'   => $nextAppointment?->appointment_date?->toDateString(),
                    'required_lab_tests' => $labTests,
                    'required_scans'     => $scans,
                ],
            ],
        ]);
    }

    /**
     * List user treatments (active and past based on prescription duration).
     */
    public function index(): JsonResponse
    {
        $patientId = Auth::id();

        $prescriptions = Prescription::with(['appointment.doctor', 'appointment.clinic'])
            ->whereHas('appointment', function ($query) use ($patientId) {
                $query->where('patient_id', $patientId);
            })
            ->get();

        $active = [];
        $past   = [];

        foreach ($prescriptions as $prescription) {
            $endDate = $prescription->created_at->addDays($prescription->duration_days);
            if (now()->startOfDay()->lte($endDate)) {
                $active[] = $prescription;
            } else {
                $past[] = $prescription;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'active_treatments' => TreatmentResource::collection($active),
                'past_treatments'   => TreatmentResource::collection($past),
            ],
        ]);
    }

    /**
     * Get the medication schedule for a specific date with logged statuses.
     */
    public function tracker(Request $request): JsonResponse
    {
        $request->validate([
            'date' => ['required', 'date'],
        ]);

        $patientId = Auth::id();
        $date      = Carbon::parse($request->date)->startOfDay();

        $prescriptions = Prescription::whereHas('appointment', function ($query) use ($patientId) {
                $query->where('patient_id', $patientId);
            })
            ->get()
            ->filter(function ($prescription) use ($date) {
                $startDate = $prescription->created_at->startOfDay();
                $endDate   = $prescription->created_at->addDays($prescription->duration_days)->startOfDay();
                return $date->between($startDate, $endDate);
            });

        $schedule = [];

        foreach ($prescriptions as $prescription) {
            $timeSlots = $this->parseDosageToTimeSlots($prescription->dosage);

            foreach ($timeSlots as $slot) {
                $log = TreatmentLog::where('patient_id', $patientId)
                    ->where('prescription_id', $prescription->id)
                    ->where('date', $date->toDateString())
                    ->where('time_of_day', $slot['period'])
                    ->first();

                $schedule[] = [
                    'prescription_id' => $prescription->id,
                    'medication_name' => $prescription->medication_name,
                    'frequency'       => $prescription->frequency,
                    'time_of_day'     => $slot['period'],
                    'scheduled_time'  => $slot['time'],
                    'status'          => $log ? $log->status : 'pending',
                ];
            }
        }

        $order = ['morning' => 1, 'afternoon' => 2, 'evening' => 3, 'night' => 4];
        usort($schedule, fn($a, $b) => $order[$a['time_of_day']] <=> $order[$b['time_of_day']]);

        return response()->json([
            'success' => true,
            'data' => [
                'date'     => $date->toDateString(),
                'schedule' => array_values($schedule),
            ],
        ]);
    }

    /**
     * Log a medication dose as taken or skipped.
     */
    public function logStatus(StoreTreatmentLogRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $patientId = Auth::id();

        $prescription = Prescription::findOrFail($validated['prescription_id']);

        abort_if(
            $prescription->appointment->patient_id !== $patientId,
            403,
            __('You do not own this prescription.')
        );

        $log = TreatmentLog::updateOrCreate(
            [
                'patient_id'      => $patientId,
                'prescription_id' => $validated['prescription_id'],
                'date'            => $validated['date'],
                'time_of_day'     => $validated['time_of_day'],
            ],
            [
                'status'    => $validated['status'],
                'action_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => __('Treatment status logged successfully.'),
            'data' => [
                'id'          => $log->id,
                'status'      => $log->status,
                'time_of_day' => $log->time_of_day,
            ],
        ]);
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    private function formatHydration(HydrationLog $log): array
    {
        $consumed = $log->cups_count * self::ML_PER_CUP;
        $goal     = self::CUPS_GOAL * self::ML_PER_CUP;

        return [
            'cups_completed'   => $log->cups_count,
            'cups_goal'        => self::CUPS_GOAL,
            'ml_consumed'      => $consumed,
            'ml_goal'          => $goal,
            'progress_percent' => $goal > 0 ? round(($consumed / $goal) * 100) : 0,
            'goal_achieved'    => $log->cups_count >= self::CUPS_GOAL,
            'date'             => $log->date->toDateString(),
        ];
    }

    private function dosageToFrequencyLabel(string $dosage): string
    {
        $parts = explode('-', $dosage);
        $count = count(array_filter($parts, fn($p) => $p !== '0' && $p !== ''));

        return match ($count) {
            1       => __('Once daily'),
            2       => __('Twice daily'),
            3       => __('Three times daily'),
            default => $dosage,
        };
    }

    private function parseDosageToTimeSlots(string $dosage): array
    {
        $slots = [];
        $parts = explode('-', $dosage);

        if (count($parts) === 3) {
            if ($parts[0] != '0') $slots[] = ['period' => 'morning',   'time' => '08:00 AM'];
            if ($parts[1] != '0') $slots[] = ['period' => 'afternoon', 'time' => '02:00 PM'];
            if ($parts[2] != '0') $slots[] = ['period' => 'evening',   'time' => '08:00 PM'];
        } else {
            $slots[] = ['period' => 'morning', 'time' => '08:00 AM'];
        }

        return count($slots) > 0 ? $slots : [['period' => 'morning', 'time' => '08:00 AM']];
    }
}
