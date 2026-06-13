<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreTreatmentLogRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Appointments\Models\Appointment;
use Modules\Appointments\Models\Prescription;
use Modules\Patient\Models\HydrationLog;
use Modules\Prescriptions\Models\PatientPrescription;
use Modules\Sessions\Models\PatientSession;
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
            $patientSession = PatientSession::where('patient_id', $patientId)->latest()->first();

            if ($patientSession) {
                $appointment = $patientSession->appointment;
            } else {
            $appointment = Appointment::where('patient_id', $patientId)
                ->latest('appointment_date')
                ->first();
            }

            abort_if($appointment === null, 404, __('No appointment found.'));
        }

        abort_if(
            $appointment->patient_id !== $patientId,
            403,
            __('You are not authorized to access this appointment.')
        );

        // Resolve the clinical session linked to this appointment
        $session = PatientSession::where('patient_id', $patientId)
            ->where(function ($q) use ($appointment) {
                $q->where('appointment_id', $appointment->id)
                  ->orWhere(fn($q2) => $q2
                      ->whereNull('appointment_id')
                      ->where('session_date', $appointment->appointment_date->toDateString())
                  );
            })
            ->first();

        if ($session) {
            $medications = PatientPrescription::where('session_id', $session->id)
                ->get()
                ->map(fn($p) => [
                    'id'              => $p->id,
                    'medication_name' => $p->medication_name,
                    'dose_strength'   => $p->dosage,
                    'frequency'       => PatientPrescription::$frequencyLabels[$p->frequency] ?? $p->frequency,
                    'timing'          => null,
                    'duration_days'   => $this->formatDuration($p->start_date, $p->end_date),
                    'warning_note'    => $p->special_instructions,
                ]);

            $labTests = collect($session->required_lab_tests ?? [])->values();
            $scans    = collect();

            $nextAppointmentDate = $session->follow_up_date?->toDateString();
            $nextAppointmentTime = $session->follow_up_time;
        } else {
            $appointment->load(['prescriptions', 'requestedTests']);

            $medications = $appointment->prescriptions->map(fn($p) => [
                'id'              => $p->id,
                'medication_name' => $p->medication_name,
                'dose_strength'   => $p->dose_strength,
                'frequency'       => $this->dosageToFrequencyLabel($p->dosage),
                'timing'          => $p->frequency,
                'duration_days'   => $p->duration_days,
                'warning_note'    => $p->warning_note,
            ]);

            $labTests = $appointment->requestedTests->where('type', 'lab')->pluck('test_name')->values();
            $scans    = $appointment->requestedTests->where('type', 'scan')->pluck('test_name')->values();

            $next = Appointment::where('patient_id', $patientId)
                ->where('id', '!=', $appointment->id)
                ->whereIn('status', ['pending', 'under_review', 'confirmed'])
                ->where('appointment_date', '>=', today())
                ->orderBy('appointment_date')
                ->first();

            $nextAppointmentDate = $next?->appointment_date?->toDateString();
            $nextAppointmentTime = $next?->appointment_time;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id'               => $appointment->id,
                'appointment_date' => $appointment->appointment_date?->toDateString(),
                'doctor'           => config('app.doctor_name'),
                'clinic'           => config('app.clinic_name'),
                'prescribed_medications'  => $medications,
                'additional_instructions' => [
                    __('Drink plenty of water (at least 2-3 liters daily).'),
                    __('Avoid heavy physical effort and lifting for 48 hours.'),
                    __('Follow a balanced diet rich in probiotics.'),
                    __('If fever persists beyond 24h, contact the clinic immediately.'),
                ],
                'follow_up' => [
                    'next_appointment'      => $nextAppointmentDate,
                    'next_appointment_time' => $nextAppointmentTime,
                    'required_lab_tests'    => $labTests,
                    'required_scans'        => $scans,
                ],
            ],
        ]);
    }

    public function downloadPdf(?Appointment $appointment = null): \Symfony\Component\HttpFoundation\Response
    {
        $patientId = Auth::id();

        if ($appointment === null) {
            $patientSession = PatientSession::where('patient_id', $patientId)->latest()->first();

            if ($patientSession) {
                $appointment = $patientSession->appointment;
            } else {
                $appointment = Appointment::where('patient_id', $patientId)
                    ->latest('appointment_date')
                    ->first();
            }

            abort_if($appointment === null, 404, __('No appointment found.'));
        }

        abort_if(
            $appointment->patient_id !== $patientId,
            403,
            __('You are not authorized to access this appointment.')
        );

        $session = PatientSession::where('patient_id', $patientId)
            ->where(function ($q) use ($appointment) {
                $q->where('appointment_id', $appointment->id)
                  ->orWhere(fn($q2) => $q2
                      ->whereNull('appointment_id')
                      ->where('session_date', $appointment->appointment_date->toDateString())
                  );
            })
            ->first();

        $data = $this->buildPdfData($appointment, $session, $patientId);

        return $this->renderPdf($data, 'prescription-' . $appointment->id);
    }

    /**
     * GET /treatment/session/{session}/pdf
     * Download prescription PDF directly from a session ID.
     */
    public function downloadSessionPdf(PatientSession $session): \Symfony\Component\HttpFoundation\Response
    {
        abort_if($session->patient_id !== Auth::id(), 403, __('You are not authorized to access this session.'));

        $appointment = $session->appointment
            ?? Appointment::where('patient_id', Auth::id())
                ->where('appointment_date', $session->session_date->toDateString())
                ->first();

        $data = $this->buildPdfData($appointment, $session, Auth::id());

        return $this->renderPdf($data, 'prescription-session-' . $session->id);
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

    private function buildPdfData(?Appointment $appointment, ?PatientSession $session, int $patientId): array
    {
        if ($session) {
            $medications = PatientPrescription::where('session_id', $session->getAttribute('id'))
                ->get()
                ->map(fn($p) => [
                    'id'              => $p->getAttribute('id'),
                    'medication_name' => $p->getAttribute('medication_name'),
                    'dose_strength'   => $p->getAttribute('dosage'),
                    'frequency'       => PatientPrescription::$frequencyLabels[$p->getAttribute('frequency')] ?? $p->getAttribute('frequency'),
                    'timing'          => null,
                    'duration_days'   => $this->formatDuration($p->getAttribute('start_date'), $p->getAttribute('end_date')),
                    'warning_note'    => $p->getAttribute('special_instructions'),
                ]);

            $labTests            = collect($session->getAttribute('required_lab_tests') ?? [])->values();
            $scans               = collect();
            $nextAppointmentDate = $session->getAttribute('follow_up_date')?->toDateString();
            $nextAppointmentTime = $session->getAttribute('follow_up_time');
        } else {
            $appointment->load(['prescriptions', 'requestedTests']);

            $medications = $appointment->prescriptions->map(fn($p) => [
                'id'              => $p->id,
                'medication_name' => $p->medication_name,
                'dose_strength'   => $p->dose_strength,
                'frequency'       => $this->dosageToFrequencyLabel($p->dosage),
                'timing'          => $p->frequency,
                'duration_days'   => $p->duration_days,
                'warning_note'    => $p->warning_note,
            ]);

            $labTests = $appointment->requestedTests->where('type', 'lab')->pluck('test_name')->values();
            $scans    = $appointment->requestedTests->where('type', 'scan')->pluck('test_name')->values();

            $next = $appointment
                ? Appointment::where('patient_id', $patientId)
                    ->where('id', '!=', $appointment->getAttribute('id'))
                    ->whereIn('status', ['pending', 'under_review', 'confirmed'])
                    ->where('appointment_date', '>=', today())
                    ->orderBy('appointment_date')
                    ->first()
                : null;

            $nextAppointmentDate = $next?->getAttribute('appointment_date')?->toDateString();
            $nextAppointmentTime = $next?->getAttribute('appointment_time');
        }

        return [
            'appointmentId'   => $appointment?->getAttribute('id'),
            'appointmentDate' => $appointment?->getAttribute('appointment_date')?->toDateString(),
            'medications'     => $medications,
            'labTests'        => $labTests,
            'scans'           => $scans,
            'nextDate'        => $nextAppointmentDate,
            'nextTime'        => $nextAppointmentTime,
        ];
    }

    private function renderPdf(array $data, string $filenamePrefix): \Symfony\Component\HttpFoundation\Response
    {
        $patient = Auth::user();

        $pdf = Pdf::loadView('reports.prescription', [
            'appointmentId'          => $data['appointmentId'],
            'appointmentDate'        => $data['appointmentDate'],
            'doctor'                 => config('app.doctor_name'),
            'clinic'                 => config('app.clinic_name'),
            'patientName'            => $patient->name ?? __('Patient'),
            'generatedAt'            => now()->format('M d, Y'),
            'medications'            => $data['medications'],
            'hasTiming'              => $data['medications']->contains(fn($m) => ! empty($m['timing'])),
            'additionalInstructions' => [
                __('Drink plenty of water (at least 2-3 liters daily).'),
                __('Avoid heavy physical effort and lifting for 48 hours.'),
                __('Follow a balanced diet rich in probiotics.'),
                __('If fever persists beyond 24h, contact the clinic immediately.'),
            ],
            'followUp' => [
                'next_appointment'      => $data['nextDate'],
                'next_appointment_time' => $data['nextTime'],
                'required_lab_tests'    => $data['labTests'],
                'required_scans'        => $data['scans'],
            ],
        ]);

        return $pdf->download("{$filenamePrefix}-" . now()->format('Ymd') . '.pdf');
    }

    private function formatDuration($startDate, $endDate): ?string
    {
        if (! $startDate || ! $endDate) {
            return null;
        }

        $days = (int) $startDate->diffInDays($endDate);

        if ($days > 0 && $days % 7 === 0) {
            $weeks = $days / 7;
            return $weeks . ' ' . __($weeks === 1 ? 'week' : 'weeks');
        }

        return $days . ' ' . __($days === 1 ? 'day' : 'days');
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
