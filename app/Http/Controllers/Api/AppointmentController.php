<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAppointmentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\FcmService;
use Modules\Appointments\Models\Appointment;
use Modules\Users\Models\User;
use Modules\Appointments\Resources\AppointmentResource;
use Modules\Availability\Models\DoctorAvailabilityDay;
use Modules\Prescriptions\Models\PatientPrescription;
use Modules\Sessions\Models\PatientSession;

class AppointmentController extends Controller
{
    public function __construct(private readonly FcmService $fcm) {}
    /**
     * List appointments split into past and upcoming.
     */
    public function index(Request $request): JsonResponse
    {
        $patientId = Auth::id();

        $past = Appointment::with(['doctor', 'clinic'])
            ->where('patient_id', $patientId)
            ->past()
            ->latest('appointment_date')
            ->get();

        $upcoming = Appointment::with(['doctor', 'clinic'])
            ->where('patient_id', $patientId)
            ->upcoming()
            ->orderBy('appointment_date')
            ->get();

        $this->attachSessions($past->merge($upcoming), $patientId);

        return response()->json([
            'success' => true,
            'data' => [
                'past_appointments' => AppointmentResource::collection($past),
                'next_appointment' => $upcoming->count() ? AppointmentResource::collection($upcoming) : null,
            ],
        ]);
    }

    /**
     * Book a new appointment.
     */
    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        $appointment = Appointment::create([
            ...$request->validated(),
            'patient_id' => Auth::id(),
            'status'     => 'pending',
        ]);

        /** @var User $patient */
        $patient = User::findOrFail(Auth::id());
        $date    = $appointment->getAttribute('appointment_date')->format('M d, Y');
        $time    = substr($appointment->getAttribute('appointment_time'), 0, 5);

        $this->fcm->sendAppointmentRequested($patient, $date, $time);
        $this->fcm->notifyStaffNewAppointment($patient->getAttribute('name'), $date, $time);

        return response()->json([
            'success' => true,
            'message' => 'Appointment request submitted successfully.',
            'data'    => new AppointmentResource($appointment->load(['doctor', 'clinic', 'session'])),
        ], 201);
    }

    /**
     * Appointment details screen — includes prescriptions and requested tests.
     * Data is resolved from the matching clinical session (by patient + date);
     * falls back to the appointment's own records when no session exists.
     */
    public function show(Appointment $appointment): JsonResponse
    {
        $this->authorizeOwnership($appointment);

        $appointment->load(['doctor', 'clinic']);

        $session = PatientSession::where('patient_id', $appointment->patient_id)
            ->where(function ($q) use ($appointment) {
                $q->where('appointment_id', $appointment->id)
                  ->orWhere(fn($q2) => $q2
                      ->whereNull('appointment_id')
                      ->where('session_date', $appointment->appointment_date->toDateString())
                  );
            })
            ->first();

        $appointment->setRelation('session', $session);

        if ($session) {
            $prescriptions = PatientPrescription::where('session_id', $session->id)->get();
            $appointment->setRelation('prescriptions', $prescriptions);

            $requestedTests = collect($session->required_lab_tests ?? [])
                ->map(fn($name) => (object) [
                    'id'        => null,
                    'test_name' => $name,
                    'type'      => 'lab',
                    'status'    => 'pending',
                ]);
            $appointment->setRelation('requestedTests', $requestedTests);
        } else {
            $appointment->load(['prescriptions', 'requestedTests']);
        }

        return response()->json([
            'success' => true,
            'data' => new AppointmentResource($appointment),
        ]);
    }

    /**
     * Get appointment status + timeline.
     * Matches the "Appointment Status" screen.
     */
    public function status(Appointment $appointment): JsonResponse
    {
        $this->authorizeOwnership($appointment);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $appointment->id,
                'status' => $appointment->status,
                'service_type' => $appointment->service_type,
                'appointment_date' => $appointment->appointment_date?->toDateString(),
                'appointment_time' => $appointment->appointment_time,
                'doctor' => ['name' => $appointment->doctor?->name],
                'clinic' => ['name' => $appointment->clinic?->name],
                'status_timeline' => $appointment->statusTimeline(),
            ],
        ]);
    }

    /**
     * Cancel an appointment (patient action).
     */
    public function cancel(Appointment $appointment): JsonResponse
    {
        $this->authorizeOwnership($appointment);

        abort_if(
            in_array($appointment->status, ['completed', 'cancelled', 'not_approved']),
            422,
            __('This appointment cannot be cancelled.')
        );

        $appointment->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Appointment cancelled successfully.'),
            'data' => new AppointmentResource($appointment->load(['doctor', 'clinic', 'session'])),
        ]);
    }

    /**
     * Change/reschedule appointment date & time.
     */
    public function reschedule(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeOwnership($appointment);

        abort_if(
            in_array($appointment->status, ['completed', 'cancelled', 'not_approved']),
            422,
            'This appointment cannot be rescheduled.'
        );

        $validated = $request->validate([
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required', 'date_format:H:i'],
        ]);

        $appointment->update([
            ...$validated,
            'status' => 'pending', // Reset to pending after reschedule
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Appointment rescheduled successfully.',
            'data' => new AppointmentResource($appointment->load(['doctor', 'clinic', 'session'])),
        ]);
    }

    /**
     * Get available time slots for a given date.
     * Reads real configured slots from DoctorAvailabilityDay and filters out booked ones.
     * Used by the booking screen time picker.
     */
    public function availableSlots(Request $request): JsonResponse
    {
        $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $date = $request->input('date');

        $day = DoctorAvailabilityDay::with('slots')
            ->where('date', $date)
            ->first();

        $isAvailable = $day?->getAttribute('is_available') && $day->getAttribute('slots')->isNotEmpty();

        if (!$isAvailable) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'date'            => $date,
                    'is_available'    => false,
                    'available_slots' => [],
                    'booked_slots'    => [],
                ],
            ]);
        }

        $booked = Appointment::where('appointment_date', $date)
            ->whereNotIn('status', ['cancelled', 'not_approved'])
            ->pluck('appointment_time')
            ->map(fn($t) => substr($t, 0, 5))
            ->toArray();

        $allSlots  = $day->getAttribute('slots')->pluck('time')->map(fn($t) => substr($t, 0, 5))->toArray();
        $available = array_values(array_diff($allSlots, $booked));

        return response()->json([
            'success' => true,
            'data'    => [
                'date'            => $date,
                'is_available'    => true,
                'available_slots' => $available,
                'booked_slots'    => array_values(array_intersect($allSlots, $booked)),
            ],
        ]);
    }


    // ─── Private Helpers ─────────────────────────────────────────────────────

    private function attachSessions($appointments, int $patientId): void
    {
        if ($appointments->isEmpty()) return;

        $sessions = PatientSession::where('patient_id', $patientId)
            ->where(function ($q) use ($appointments) {
                $q->whereIn('appointment_id', $appointments->pluck('id'))
                  ->orWhere(fn($q2) => $q2
                      ->whereNull('appointment_id')
                      ->whereIn('session_date', $appointments->pluck('appointment_date')->map->toDateString()->unique())
                  );
            })
            ->get();

        $byAppointmentId = $sessions->whereNotNull('appointment_id')->keyBy('appointment_id');
        $byDate          = $sessions->whereNull('appointment_id')->keyBy(fn($s) => $s->session_date->toDateString());

        foreach ($appointments as $appointment) {
            $appointment->setRelation(
                'session',
                $byAppointmentId->get($appointment->id)
                    ?? $byDate->get($appointment->appointment_date->toDateString())
            );
        }
    }

    private function authorizeOwnership(Appointment $appointment): void
    {
        abort_if(
            $appointment->patient_id !== Auth::id(),
            403,
            __('You are not authorized to access this appointment.')
        );
    }

}