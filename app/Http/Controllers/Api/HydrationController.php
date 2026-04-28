<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Patient\Models\HydrationLog;

class HydrationController extends Controller
{
    private const CUPS_GOAL = 8;
    private const ML_PER_CUP = 250;

    /**
     * Get today's hydration status for the authenticated patient.
     */
    public function show(): JsonResponse
    {
        $log = HydrationLog::firstOrCreate(
            ['patient_id' => Auth::id(), 'date' => today()->toDateString()],
            ['cups_count' => 0]
        );

        return response()->json([
            'success' => true,
            'data' => $this->format($log),
        ]);
    }

    /**
     * Set today's cups count (called when the patient taps a cup).
     */
    public function log(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cups_count' => ['required', 'integer', 'min:0', 'max:8'],
        ]);

        $log = HydrationLog::updateOrCreate(
            ['patient_id' => Auth::id(), 'date' => today()->toDateString()],
            ['cups_count' => $validated['cups_count']]
        );

        return response()->json([
            'success' => true,
            'data' => $this->format($log),
        ]);
    }

    /**
     * Reset today's hydration tracker to zero cups.
     */
    public function reset(): JsonResponse
    {
        $log = HydrationLog::updateOrCreate(
            ['patient_id' => Auth::id(), 'date' => today()->toDateString()],
            ['cups_count' => 0]
        );

        return response()->json([
            'success' => true,
            'message' => __('Hydration tracker reset successfully.'),
            'data' => $this->format($log),
        ]);
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    private function format(HydrationLog $log): array
    {
        $consumed = $log->cups_count * self::ML_PER_CUP;
        $goal     = self::CUPS_GOAL * self::ML_PER_CUP;

        return [
            'date'             => $log->date->toDateString(),
            'cups_completed'   => $log->cups_count,
            'cups_goal'        => self::CUPS_GOAL,
            'ml_consumed'      => $consumed,
            'ml_goal'          => $goal,
            'progress_percent' => $goal > 0 ? (int) round(($consumed / $goal) * 100) : 0,
            'goal_achieved'    => $log->cups_count >= self::CUPS_GOAL,
        ];
    }
}
