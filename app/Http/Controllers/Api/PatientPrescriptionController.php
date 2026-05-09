<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePatientPrescriptionRequest;
use App\Http\Requests\Api\UpdatePatientPrescriptionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Prescriptions\Models\PatientPrescription;
use Modules\Prescriptions\Resources\PatientPrescriptionResource;
use Modules\Users\Models\User;

class PatientPrescriptionController extends Controller
{
    /**
     * GET /doctor/patients/{patient}/prescriptions
     * List all prescriptions for a patient, optionally filtered by status.
     */
    public function index(User $patient): JsonResponse
    {
        abort_if($patient->role_id !== 2, 404);

        $prescriptions = PatientPrescription::with('session')
            ->where('patient_id', $patient->id)
            ->orderByDesc('start_date')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => PatientPrescriptionResource::collection($prescriptions),
        ]);
    }

    /**
     * POST /doctor/patients/{patient}/prescriptions
     * Add a new prescription for a patient.
     */
    public function store(StorePatientPrescriptionRequest $request, User $patient): JsonResponse
    {
        abort_if($patient->role_id !== 2, 404);

        $prescription = PatientPrescription::create([
            'patient_id'           => $patient->id,
            'doctor_id'            => Auth::id(),
            'session_id'           => $request->session_id,
            'medication_name'      => $request->medication_name,
            'dosage'               => $request->dosage,
            'frequency'            => $request->frequency,
            'times_per_day'        => $request->times_per_day,
            'start_date'           => $request->start_date,
            'end_date'             => $request->end_date,
            'special_instructions' => $request->special_instructions,
            'patient_reminders'    => $request->boolean('patient_reminders'),
            'auto_stop'            => $request->boolean('auto_stop'),
            'status'               => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Prescription added successfully.'),
            'data'    => new PatientPrescriptionResource($prescription->load('session')),
        ], 201);
    }

    /**
     * POST /doctor/patients/{patient}/prescriptions/{prescription}
     * Update an existing prescription.
     */
    public function update(UpdatePatientPrescriptionRequest $request, User $patient, PatientPrescription $prescription): JsonResponse
    {
        abort_if($prescription->patient_id !== $patient->id, 404);

        $prescription->update(array_filter([
            'session_id'           => $request->session_id,
            'medication_name'      => $request->medication_name,
            'dosage'               => $request->dosage,
            'frequency'            => $request->frequency,
            'times_per_day'        => $request->times_per_day,
            'start_date'           => $request->start_date,
            'end_date'             => $request->end_date,
            'special_instructions' => $request->special_instructions,
            'patient_reminders'    => $request->has('patient_reminders') ? $request->boolean('patient_reminders') : null,
            'auto_stop'            => $request->has('auto_stop') ? $request->boolean('auto_stop') : null,
        ], fn($v) => $v !== null));

        return response()->json([
            'success' => true,
            'message' => __('Prescription updated successfully.'),
            'data'    => new PatientPrescriptionResource($prescription->fresh()->load('session')),
        ]);
    }

    /**
     * POST /doctor/patients/{patient}/prescriptions/{prescription}/stop
     * Manually stop an active prescription.
     */
    public function stop(User $patient, PatientPrescription $prescription): JsonResponse
    {
        abort_if($prescription->patient_id !== $patient->id, 404);
        abort_if($prescription->status === 'stopped', 422, __('Prescription is already stopped.'));

        $prescription->update(['status' => 'stopped']);

        return response()->json([
            'success' => true,
            'message' => __('Prescription stopped.'),
            'data'    => new PatientPrescriptionResource($prescription->load('session')),
        ]);
    }

    /**
     * DELETE /doctor/patients/{patient}/prescriptions/{prescription}
     * Delete a prescription.
     */
    public function destroy(User $patient, PatientPrescription $prescription): JsonResponse
    {
        abort_if($prescription->patient_id !== $patient->id, 404);

        $prescription->delete();

        return response()->json([
            'success' => true,
            'message' => __('Prescription deleted.'),
        ]);
    }
}
