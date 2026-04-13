<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAppointmentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Appointments\Models\Appointment;
use Modules\Appointments\Resources\AppointmentResource;

class AppointmentController extends Controller
{
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
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Appointment request submitted successfully.',
            'data' => new AppointmentResource($appointment->load(['doctor', 'clinic'])),
        ], 201);
    }

    /**
     * Appointment details screen — includes prescriptions and requested tests.
     */
    public function show(Appointment $appointment): JsonResponse
    {
        $this->authorizeOwnership($appointment);

        $appointment->load(['doctor', 'clinic', 'prescriptions', 'requestedTests']);

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
            'data' => new AppointmentResource($appointment->load(['doctor', 'clinic'])),
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
            'data' => new AppointmentResource($appointment->load(['doctor', 'clinic'])),
        ]);
    }

    /**
     * Get available time slots for a doctor on a given date.
     * Used by the booking screen time picker.
     */
    public function availableSlots(Request $request): JsonResponse
    {
        $request->validate([
            'doctor_id' => ['required', 'exists:users,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $allSlots = ['09:00', '10:30', '11:45', '14:00', '16:30'];

        $booked = Appointment::where('doctor_id', $request->doctor_id)
            ->where('appointment_date', $request->date)
            ->whereNotIn('status', ['cancelled', 'not_approved'])
            ->pluck('appointment_time')
            ->map(fn($t) => substr($t, 0, 5))
            ->toArray();

        $available = array_values(array_filter(
            $allSlots,
            fn($slot) => !in_array($slot, $booked)
        ));

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $request->date,
                'available_slots' => $available,
                'booked_slots' => $booked,
            ],
        ]);
    }


    // ─── Private Helpers ─────────────────────────────────────────────────────

    private function authorizeOwnership(Appointment $appointment): void
    {
        abort_if(
            $appointment->patient_id !== Auth::id(),
            403,
            __('You are not authorized to access this appointment.')
        );
    }

}