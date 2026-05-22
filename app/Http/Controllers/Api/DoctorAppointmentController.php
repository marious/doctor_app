<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\DoctorRescheduleAppointmentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Appointments\Models\Appointment;
use Modules\Appointments\Resources\DoctorAppointmentResource;

class DoctorAppointmentController extends Controller
{
    /**
     * GET /doctor/appointments
     * List all upcoming appointments for the authenticated doctor,
     * grouped into today and upcoming.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status'   => ['nullable', 'in:pending,under_review,confirmed,not_approved,completed,cancelled'],
            'date'     => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Appointment::with('patient')
            // ->where('doctor_id', Auth::id())
            ->orderBy('appointment_date', 'DESC')
            ->orderBy('appointment_time', 'DESC');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->where('appointment_date', $request->date);
        }

        $appointments = $query->paginate($request->integer('per_page', 500));

        return response()->json([
            'success' => true,
            'data'    => DoctorAppointmentResource::collection($appointments),
            'meta'    => [
                'current_page' => $appointments->currentPage(),
                'last_page'    => $appointments->lastPage(),
                'per_page'     => $appointments->perPage(),
                'total'        => $appointments->total(),
            ],
        ]);
    }

    /**
     * GET /doctor/appointments/{appointment}
     * Single appointment detail.
     */
    public function show(Appointment $appointment): JsonResponse
    {
        // $this->authorizeDoctor($appointment);

        $appointment->load(['patient', 'prescriptions', 'requestedTests']);

        return response()->json([
            'success' => true,
            'data'    => new DoctorAppointmentResource($appointment),
        ]);
    }

    /**
     * POST /doctor/appointments/{appointment}/approve
     * Approve a pending appointment.
     */
    public function approve(Appointment $appointment): JsonResponse
    {
        // $this->authorizeDoctor($appointment);

        abort_if(
            !in_array($appointment->status, ['pending', 'under_review']),
            422,
            __('Only pending appointments can be approved.')
        );

        $appointment->update([
            'status'       => 'confirmed',
            'confirmed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Appointment approved successfully.'),
            'data'    => new DoctorAppointmentResource($appointment->load('patient')),
        ]);
    }

    /**
     * POST /doctor/appointments/{appointment}/reject
     * Reject a pending appointment.
     */
    public function reject(Appointment $appointment): JsonResponse
    {
        // $this->authorizeDoctor($appointment);

        abort_if(
            !in_array($appointment->status, ['pending', 'under_review']),
            422,
            __('Only pending appointments can be rejected.')
        );

        $appointment->update([
            'status'       => 'not_approved',
            'cancelled_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Appointment rejected.'),
            'data'    => new DoctorAppointmentResource($appointment->load('patient')),
        ]);
    }

    /**
     * POST /doctor/appointments/{appointment}/reschedule
     * Change the date and time of a confirmed or cancelled appointment.
     */
    public function reschedule(DoctorRescheduleAppointmentRequest $request, Appointment $appointment): JsonResponse
    {
        // $this->authorizeDoctor($appointment);

        abort_if(
            in_array($appointment->status, ['completed', 'not_approved']),
            422,
            __('This appointment cannot be rescheduled.')
        );

        $appointment->update([
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'status'           => 'confirmed',
            'confirmed_at'     => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Appointment rescheduled successfully.'),
            'data'    => new DoctorAppointmentResource($appointment->load('patient')),
        ]);
    }

    // ─── Private Helpers ─────────────────────────────────────────────────────

    private function authorizeDoctor(Appointment $appointment): void
    {
        abort_if(
            $appointment->doctor_id !== Auth::id(),
            403,
            __('You are not authorized to manage this appointment.')
        );
    }
}
