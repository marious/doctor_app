<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Patient\Models\CycleSymptomLog;
use Modules\Patient\Models\HealthStat;
use Modules\Patient\Models\PatientTracking;
use Illuminate\Support\Facades\Auth;
use Modules\Users\Resources\TrackerResource;

class TrackerController extends Controller
{
    /**
     * GET /api/v1/tracker
     *
     * Returns the full tracker screen payload for the authenticated patient.
     * Supports ?month=YYYY-MM to navigate calendar forward/backward.
     * Supports ?type=pregnancy|menstrual to switch mode.
     */
    public function show(Request $request): JsonResponse
    {
        $request->validate([
            'type'  => ['nullable', 'in:pregnancy,menstrual'],
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        $query = PatientTracking::with(['latestHealthStat'])
            ->where('patient_id', Auth::id());

        if ($request->filled('type')) {
            $query->where('tracking_type', $request->type);
        }

        $tracking = $query->latest()->first();

        if (!$tracking) {
            return response()->json([
                'success' => false,
                'message' => 'No tracking record found. Please start tracking first.',
                'data'    => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => new TrackerResource($tracking),
        ]);
    }

    /**
     * POST /api/v1/tracker/health-stats
     *
     * Log a new weight / BPM reading.
     */
    public function logHealthStat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'weight_kg' => ['nullable', 'numeric', 'min:30', 'max:200'],
            'bpm'       => ['nullable', 'integer', 'min:40', 'max:200'],
        ]);

        $tracking = PatientTracking::where('patient_id', Auth::id())->latest()->firstOrFail();

        // Evaluate weight gain status for pregnancy mode
        $weightStatus = null;
        if ($tracking->tracking_type === 'pregnancy' && isset($validated['weight_kg'])) {
            $weeks = $tracking->gestationalWeeks() ?? 0;
            // Get initial weight from first stat (pre-pregnancy weight)
            $firstStat = HealthStat::where('patient_tracking_id', $tracking->id)->oldest()->first();
            if ($firstStat?->weight_kg) {
                $gain = $validated['weight_kg'] - $firstStat->weight_kg;
                $weightStatus = HealthStat::evaluateWeightGain($gain, $weeks);
            } else {
                $weightStatus = 'healthy_gain'; // baseline entry
            }
        }

        $stat = HealthStat::create([
            'patient_tracking_id' => $tracking->id,
            'weight_kg'           => $validated['weight_kg'] ?? null,
            'bpm'                 => $validated['bpm'] ?? null,
            'weight_status'       => $weightStatus,
            'recorded_at'         => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Health stats logged successfully.',
            'data'    => [
                'id'            => $stat->id,
                'weight_kg'     => $stat->weight_kg,
                'bpm'           => $stat->bpm,
                'weight_status' => $stat->weight_status,
                'weight_status_label' => $stat->weight_status
                    ? match($stat->weight_status) {
                        'healthy_gain' => 'Healthy Gain',
                        'underweight'  => 'Below Target',
                        'overweight'   => 'Above Target',
                        default        => null,
                    }
                    : null,
                'recorded_at'   => $stat->recorded_at->toDateTimeString(),
            ],
        ], 201);
    }

    /**
     * POST /api/v1/tracker/log-symptoms
     *
     * Log one or more symptoms for a given date.
     */
    public function logSymptom(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'logged_date' => ['required', 'date', 'before_or_equal:today'],
            'symptoms'    => ['required', 'array', 'min:1'],
            'symptoms.*'  => ['string', 'in:cramps,fatigue,headache,bloating,mood_swings,backache,nausea,breast_tenderness'],
            'severity'    => ['nullable', 'in:mild,moderate,severe'],
        ]);

        $tracking = PatientTracking::where('patient_id', Auth::id())
            ->where('tracking_type', 'menstrual')
            ->latest()
            ->firstOrFail();

        $severity = $validated['severity'] ?? 'mild';
        $saved    = [];

        foreach ($validated['symptoms'] as $symptom) {
            $log = CycleSymptomLog::updateOrCreate(
                [
                    'patient_tracking_id' => $tracking->id,
                    'logged_date'         => $validated['logged_date'],
                    'symptom'             => $symptom,
                ],
                ['severity' => $severity]
            );
            $saved[] = ['symptom' => $log->symptom, 'severity' => $log->severity];
        }

        return response()->json([
            'success' => true,
            'message' => 'Symptoms logged successfully.',
            'data'    => [
                'logged_date' => $validated['logged_date'],
                'symptoms'    => $saved,
            ],
        ], 201);
    }

    /**
     * GET /api/v1/tracker/calendar?month=2026-02
     *
     * Standalone calendar endpoint for navigation.
     */
    public function calendar(Request $request): JsonResponse
    {
        $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'type'  => ['nullable', 'in:pregnancy,menstrual'],
        ]);

        $query = PatientTracking::where('patient_id', Auth::id());

        if ($request->filled('type')) {
            $query->where('tracking_type', $request->type);
        }

        $tracking = $query->latest()->firstOrFail();

        $monthParam     = $request->query('month', now()->format('Y-m'));
        [$year, $month] = explode('-', $monthParam);

        $calendar = $tracking->tracking_type === 'menstrual'
            ? $tracking->menstrualCalendarData((int) $year, (int) $month)
            : $tracking->calendarData((int) $year, (int) $month);

        return response()->json([
            'success' => true,
            'data'    => $calendar,
        ]);
    }
}