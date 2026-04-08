<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePatientTrackingRequest;
use Illuminate\Http\JsonResponse;
use Modules\Patient\Models\PatientTracking;
use Modules\Patient\Resources\PatientTrackingResource;

class PatientTrackingController extends Controller
{
    public function store(StorePatientTrackingRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['patient_id'] = auth()->id();

        $tracking = new PatientTracking($validated);

        // ── Compute derived fields ────────────────────────────────────────────
        if ($validated['tracking_type'] === 'menstrual') {
            $tracking->next_period_date = $tracking->computeNextPeriodDate();
            $tracking->ovulation_date = $tracking->computeOvulationDate();
        }

        if ($validated['tracking_type'] === 'pregnancy') {
            $tracking->estimated_due_date = $tracking->computeEstimatedDueDate();
        }

        $tracking->save();

        return response()->json([
            'success' => true,
            'message' => 'Tracking started successfully.',
            'data' => new PatientTrackingResource($tracking),
        ], 201);
    }
}
