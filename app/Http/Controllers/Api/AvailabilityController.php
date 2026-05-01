<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SaveAvailabilityRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Availability\Models\DoctorAvailabilityDay;
use Modules\Availability\Resources\AvailabilityDayResource;

class AvailabilityController extends Controller
{
    /**
     * GET /doctor/availability/calendar?year=2026&month=5
     * Returns all configured days for the given month.
     */
    public function calendar(Request $request): JsonResponse
    {
        $request->validate([
            'year'  => ['required', 'integer', 'min:2000', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $year  = (int) $request->year;
        $month = (int) $request->month;

        $days = DoctorAvailabilityDay::with('slots')
            ->where('doctor_id', Auth::id())
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->get()
            ->map(fn($day) => [
                'date'         => $day->date->toDateString(),
                'is_available' => $day->is_available,
                'slots_count'  => $day->slots->count(),
            ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'year'  => $year,
                'month' => $month,
                'days'  => $days,
            ],
        ]);
    }

    /**
     * GET /doctor/availability/{date}
     * Returns the availability status and time slots for a specific date.
     */
    public function show(string $date): JsonResponse
    {
        $day = DoctorAvailabilityDay::with('slots')
            ->where('doctor_id', Auth::id())
            ->where('date', $date)
            ->first();

        if (!$day) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'date'         => $date,
                    'is_available' => false,
                    'slots'        => [],
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data'    => new AvailabilityDayResource($day),
        ]);
    }

    /**
     * POST /doctor/availability/{date}
     * Save (or replace) the full day configuration: status + slots.
     * Corresponds to the "Save Changes" button in the UI.
     */
    public function save(SaveAvailabilityRequest $request, string $date): JsonResponse
    {
        $day = DoctorAvailabilityDay::firstOrCreate(
            ['doctor_id' => Auth::id(), 'date' => $date],
            ['is_available' => true]
        );

        $day->update(['is_available' => $request->boolean('is_available')]);

        // Replace all slots
        $day->slots()->delete();

        $slots = collect($request->input('slots', []))
            ->unique()
            ->map(fn($time) => ['availability_day_id' => $day->id, 'time' => $time]);

        $day->slots()->createMany($slots);

        $day->load('slots');

        return response()->json([
            'success' => true,
            'message' => __('Availability saved successfully.'),
            'data'    => new AvailabilityDayResource($day),
        ]);
    }
}
