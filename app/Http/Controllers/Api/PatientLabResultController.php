<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePatientLabResultRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Modules\LabResults\Models\PatientLabResult;
use Modules\Sessions\Models\PatientSession;
use Modules\Users\Models\User;

class PatientLabResultController extends Controller
{
    private array $visitTypeLabels = [
        'new_visit'    => 'New Visit',
        'follow_up'    => 'Follow-Up',
        'emergency'    => 'Emergency',
        'consultation' => 'Routine Check',
    ];

    /**
     * GET /doctor/patients/{patient}/lab-results
     * Unified list: dedicated uploads + files uploaded inside sessions.
     */
    public function index(User $patient): JsonResponse
    {
        abort_if($patient->role_id !== 2, 404);

        // Dedicated lab result uploads
        $dedicated = PatientLabResult::with(['session', 'media'])
            ->where('patient_id', $patient->id)
            ->get()
            ->map(fn($r) => $this->formatDedicated($r));

        // Lab result files attached within session recordings
        $fromSessions = PatientSession::where('patient_id', $patient->id)
            ->with('media')
            ->get()
            ->flatMap(fn($s) => $s->getMedia('lab_results')->map(fn($m) => $this->formatSessionMedia($m, $s)));

        $all = $dedicated->concat($fromSessions)->sortByDesc('created_at')->values();

        return response()->json([
            'success' => true,
            'data'    => $all,
        ]);
    }

    /**
     * POST /doctor/patients/{patient}/lab-results
     * Upload a new lab result, optionally linked to a session.
     */
    public function store(StorePatientLabResultRequest $request, User $patient): JsonResponse
    {
        abort_if($patient->role_id !== 2, 404);

        $record = PatientLabResult::create([
            'patient_id' => $patient->id,
            'session_id' => $request->session_id,
            'name'       => $request->name,
        ]);

        $file = $request->file('file');
        $record
            ->addMedia($file)
            ->usingName(Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)))
            ->toMediaCollection('file');

        return response()->json([
            'success' => true,
            'message' => __('Lab result uploaded successfully.'),
            'data'    => $this->formatDedicated($record->load(['session', 'media'])),
        ], 201);
    }

    /**
     * DELETE /doctor/patients/{patient}/lab-results/{labResult}
     * Delete a dedicated lab result upload (and its file).
     */
    public function destroy(User $patient, PatientLabResult $labResult): JsonResponse
    {
        abort_if($labResult->patient_id !== $patient->id, 404);

        $labResult->delete();

        return response()->json([
            'success' => true,
            'message' => __('Lab result deleted.'),
        ]);
    }

    // ─── Private Formatters ───────────────────────────────────────────────────

    private function formatDedicated(PatientLabResult $record): array
    {
        $media = $record->getFirstMedia('file');

        return [
            'id'         => $record->id,
            'name'       => $record->name,
            'file_url'   => $media?->getFullUrl(),
            'file_name'  => $media?->file_name,
            'session'    => $record->session_id ? [
                'id'    => $record->session->id,
                'label' => ($this->visitTypeLabels[$record->session->visit_type] ?? ucfirst($record->session->visit_type ?? ''))
                         . ' — ' . $record->session->session_date?->format('M d, Y'),
            ] : null,
            'source'     => 'upload',
            'created_at' => $record->created_at?->toDateTimeString(),
        ];
    }

    private function formatSessionMedia($media, PatientSession $session): array
    {
        return [
            'id'         => 'session_' . $media->id,
            'name'       => $media->name ?: $media->file_name,
            'file_url'   => $media->getFullUrl(),
            'file_name'  => $media->file_name,
            'session'    => [
                'id'    => $session->id,
                'label' => ($this->visitTypeLabels[$session->visit_type] ?? ucfirst($session->visit_type ?? ''))
                         . ' — ' . $session->session_date?->format('M d, Y'),
            ],
            'source'     => 'session',
            'created_at' => $media->created_at?->toDateTimeString(),
        ];
    }
}
