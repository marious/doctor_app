<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Users\Models\User;
use Modules\Users\Resources\PatientDirectoryResource;

class PatientDirectoryController extends Controller
{
    /**
     * GET /doctor/patients
     * Patient directory with search, mode filter, and risk filter.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'mode'        => ['nullable', 'in:all,pregnancy,period'],
            'risk_status' => ['nullable', 'in:stable,high_risk,monitor'],
            'search'      => ['nullable', 'string', 'max:100'],
            'per_page'    => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = User::with(['patientTracking', 'lastCompletedAppointment'])
            ->where('role_id', 2)
            ->orderBy('name');

        // Search by name or patient code (#P-001)
        if ($request->filled('search')) {
            $search = ltrim($request->search, '#P-');

            $query->where(function ($q) use ($request, $search) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('id', is_numeric($search) ? (int) $search : -1);
            });
        }

        // Filter by risk status
        if ($request->filled('risk_status')) {
            $query->where('risk_status', $request->risk_status);
        }

        // Filter by tracking mode (pregnancy / period)
        if ($request->filled('mode') && $request->mode !== 'all') {
            $trackingType = $request->mode === 'pregnancy' ? 'pregnancy' : 'menstrual';

            $query->whereHas('patientTracking', fn($q) =>
                $q->where('tracking_type', $trackingType)
            );
        }

        $patients = $query->paginate($request->integer('per_page', 30));

        return response()->json([
            'success' => true,
            'data'    => PatientDirectoryResource::collection($patients),
            'meta'    => [
                'current_page' => $patients->currentPage(),
                'last_page'    => $patients->lastPage(),
                'per_page'     => $patients->perPage(),
                'total'        => $patients->total(),
            ],
        ]);
    }

    /**
     * PATCH /doctor/patients/{patient}/risk-status
     * Update the risk status of a patient (doctor assessment).
     */
    public function updateRiskStatus(Request $request, User $patient): JsonResponse
    {
        abort_if($patient->role_id !== 2, 404);

        $request->validate([
            'risk_status' => ['required', 'in:stable,high_risk,monitor'],
        ]);

        $patient->update(['risk_status' => $request->risk_status]);

        return response()->json([
            'success' => true,
            'message' => __('Risk status updated successfully.'),
            'data'    => new PatientDirectoryResource($patient->load(['patientTracking', 'lastCompletedAppointment'])),
        ]);
    }
}
