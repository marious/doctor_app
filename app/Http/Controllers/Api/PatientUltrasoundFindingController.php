<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePatientUltrasoundFindingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Modules\Sessions\Models\PatientSession;
use Modules\UltrasoundFindings\Models\PatientUltrasoundFinding;
use Modules\Users\Models\User;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PatientUltrasoundFindingController extends Controller
{
    private array $visitTypeLabels = [
        'new_visit'    => 'New Visit',
        'follow_up'    => 'Follow-Up',
        'emergency'    => 'Emergency',
        'consultation' => 'Routine Check',
    ];

    /**
     * GET /doctor/patients/{patient}/ultrasound-findings
     * Unified list: dedicated uploads + files uploaded inside sessions.
     */
    public function index(User $patient): JsonResponse
    {
        abort_if($patient->role_id !== 2, 404);

        $dedicated = PatientUltrasoundFinding::with(['session', 'media'])
            ->where('patient_id', $patient->id)
            ->get()
            ->map(fn($r) => $this->formatDedicated($r));

        $fromSessions = PatientSession::where('patient_id', $patient->id)
            ->with('media')
            ->get()
            ->flatMap(fn($s) => $s->getMedia('ultrasound_findings')->map(fn($m) => $this->formatSessionMedia($m, $s)));

        $all = $dedicated->concat($fromSessions)->sortByDesc('created_at')->values();

        return response()->json([
            'success' => true,
            'data'    => $all,
        ]);
    }

    /**
     * POST /doctor/patients/{patient}/ultrasound-findings
     * Upload a new ultrasound finding, optionally linked to a session.
     */
    public function store(StorePatientUltrasoundFindingRequest $request, User $patient): JsonResponse
    {
        abort_if($patient->role_id !== 2, 404);

        $record = PatientUltrasoundFinding::create([
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
            'message' => __('Ultrasound finding uploaded successfully.'),
            'data'    => $this->formatDedicated($record->load(['session', 'media'])),
        ], 201);
    }

    /**
     * DELETE /doctor/patients/{patient}/ultrasound-findings/{ultrasoundFinding}
     * Delete a dedicated ultrasound finding upload (and its file).
     */
    public function destroy(User $patient,  $ultraSound): JsonResponse
    {
        // abort_if($ultrasoundFinding->patient_id !== $patient->id, 404);

        $media = Media::find($ultraSound);
        if ($media) {
            $ownerIsPatientSession = $media->model_type === (new PatientSession())->getMorphClass()
                && PatientSession::where('id', $media->model_id)
                    ->where('patient_id', $patient->id)
                    ->exists();
            abort_if(! $ownerIsPatientSession, 404);
            $media->delete();
        } else {
            $record = PatientUltrasoundFinding::findOrFail($ultraSound);
            abort_if($record->patient_id !== $patient->id, 404);
            $record->delete();
        }



        return response()->json([
            'success' => true,
            'message' => __('Ultrasound finding deleted.'),
        ]);
    }

    // ─── Private Formatters ───────────────────────────────────────────────────

    private function formatDedicated(PatientUltrasoundFinding $record): array
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
