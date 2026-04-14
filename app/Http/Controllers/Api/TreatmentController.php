<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreTreatmentLogRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Appointments\Models\Prescription;
use Modules\Treatments\Models\TreatmentLog;
use Modules\Treatments\Resources\TreatmentResource;

class TreatmentController extends Controller
{
    /**
     * List user treatments (past and active based on prescription duration).
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
        $past = [];

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
                'past_treatments' => TreatmentResource::collection($past),
            ],
        ]);
    }

    /**
     * Get the schedule for a specific date with logged statuses.
     */
    public function tracker(Request $request): JsonResponse
    {
        $request->validate([
            'date' => ['required', 'date'],
        ]);

        $patientId = Auth::id();
        $date = Carbon::parse($request->date)->startOfDay();

        // Find active prescriptions on this date
        $prescriptions = Prescription::whereHas('appointment', function ($query) use ($patientId) {
                $query->where('patient_id', $patientId);
            })
            ->get()
            ->filter(function ($prescription) use ($date) {
                $startDate = $prescription->created_at->startOfDay();
                $endDate = $prescription->created_at->addDays($prescription->duration_days)->startOfDay();
                return $date->between($startDate, $endDate);
            });

        $schedule = [];

        foreach ($prescriptions as $prescription) {
            // Very basic dosage parser: e.g. "1-0-1" -> morning, evening
            // Or fallback to 'morning' if unknown.
            $timeSlots = $this->parseDosageToTimeSlots($prescription->dosage);

            foreach ($timeSlots as $slot) {
                // Check if there is a log
                $log = TreatmentLog::where('patient_id', $patientId)
                    ->where('prescription_id', $prescription->id)
                    ->where('date', $date->toDateString())
                    ->where('time_of_day', $slot['period'])
                    ->first();

                $schedule[] = [
                    'prescription_id' => $prescription->id,
                    'medication_name' => $prescription->medication_name,
                    'frequency' => $prescription->frequency,
                    'time_of_day' => $slot['period'], // e.g. 'morning'
                    'scheduled_time' => $slot['time'], // e.g. '08:00 AM'
                    'status' => $log ? $log->status : 'pending',
                ];
            }
        }

        // Sort morning -> afternoon -> evening -> night
        $order = ['morning' => 1, 'afternoon' => 2, 'evening' => 3, 'night' => 4];
        usort($schedule, fn($a, $b) => $order[$a['time_of_day']] <=> $order[$b['time_of_day']]);

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $date->toDateString(),
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

        // Check ownership
        abort_if(
            $prescription->appointment->patient_id !== $patientId,
            403,
            __('You do not own this prescription.')
        );

        $log = TreatmentLog::updateOrCreate(
            [
                'patient_id' => $patientId,
                'prescription_id' => $validated['prescription_id'],
                'date' => $validated['date'],
                'time_of_day' => $validated['time_of_day'],
            ],
            [
                'status' => $validated['status'],
                'action_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => __('Treatment status logged successfully.'),
            'data' => [
                'id' => $log->id,
                'status' => $log->status,
                'time_of_day' => $log->time_of_day,
            ]
        ]);
    }

    private function parseDosageToTimeSlots(string $dosage): array
    {
        $slots = [];
        $parts = explode('-', $dosage);
        
        // standard format "1-0-1" -> morning, afternoon, evening
        if (count($parts) === 3) {
            if ($parts[0] != '0') $slots[] = ['period' => 'morning', 'time' => '08:00 AM'];
            if ($parts[1] != '0') $slots[] = ['period' => 'afternoon', 'time' => '02:00 PM'];
            if ($parts[2] != '0') $slots[] = ['period' => 'evening', 'time' => '08:00 PM'];
        } else {
            // fallback generic
            $slots[] = ['period' => 'morning', 'time' => '08:00 AM'];
        }

        return count($slots) > 0 ? $slots : [['period' => 'morning', 'time' => '08:00 AM']];
    }
}
