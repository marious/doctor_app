<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\DoctorRescheduleAppointmentRequest;
use App\Services\FcmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Appointments\Models\Appointment;
use Modules\Appointments\Resources\DoctorAppointmentResource;

class DoctorAppointmentController extends Controller
{
    public function __construct(private readonly FcmService $fcm) {}
    /**
     * GET /doctor/appointments
     * List all upcoming appointments for the authenticated doctor,
     * grouped into today and upcoming.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status'       => ['nullable', 'in:pending,under_review,confirmed,not_approved,completed,cancelled'],
            'date'         => ['nullable', 'date'],
            'date_from'    => ['nullable', 'date'],
            'date_to'      => ['nullable', 'date', 'after_or_equal:date_from'],
            'service_type' => ['nullable', 'in:pregnant,gynecology'],
            'visit_type'   => ['nullable', 'in:appointment,new_visit'],
            'patient_name' => ['nullable', 'string', 'max:100'],
            'per_page'     => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Appointment::with('patient')
            // ->where('doctor_id', Auth::id())
            ->orderBy('appointment_date', 'ASC')
            ->orderBy('appointment_time', 'ASC');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        } else {
            $query->whereNotIn('status', ['completed', 'cancelled', 'not_approved']);
        }

        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->input('date'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('appointment_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('appointment_date', '<=', $request->input('date_to'));
        }

        if ($request->filled('service_type')) {
            $query->where('service_type', $request->input('service_type'));
        }

        if ($request->filled('visit_type')) {
            $query->where('visit_type', $request->input('visit_type'));
        }

        if ($request->filled('patient_name')) {
            $query->whereHas('patient', fn ($q) =>
                $q->where('name', 'like', '%' . $request->input('patient_name') . '%')
            );
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
            !\in_array($appointment->getAttribute('status'), ['pending', 'under_review']),
            422,
            __('Only pending appointments can be approved.')
        );

        $appointment->update([
            'status'       => 'confirmed',
            'confirmed_at' => now(),
        ]);

        $this->fcm->sendAppointmentApproved(
            $appointment->getAttribute('patient'),
            $appointment->getAttribute('appointment_date')->format('M d, Y'),
            substr($appointment->getAttribute('appointment_time'), 0, 5)
        );

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
            !\in_array($appointment->getAttribute('status'), ['pending', 'under_review', 'confirmed', 'not_approved']),
            422,
            __('This Appointment Cannot Be Rejected')
        );

        $appointment->update([
            'status'       => 'not_approved',
            'cancelled_at' => now(),
        ]);

        $this->fcm->sendAppointmentDeclined(
            $appointment->getAttribute('patient'),
            $appointment->getAttribute('appointment_date')->format('M d, Y'),
            substr($appointment->getAttribute('appointment_time'), 0, 5)
        );

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
            \in_array($appointment->getAttribute('status'), ['completed', 'not_approved']),
            422,
            __('This appointment cannot be rescheduled.')
        );

        $appointment->update([
            'appointment_date' => $request->input('appointment_date'),
            'appointment_time' => $request->input('appointment_time'),
            'status'           => 'confirmed',
            'confirmed_at'     => now(),
        ]);

        $this->fcm->sendAppointmentRescheduled(
            $appointment->getAttribute('patient'),
            $request->input('appointment_date'),
            substr($request->input('appointment_time'), 0, 5)
        );

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
