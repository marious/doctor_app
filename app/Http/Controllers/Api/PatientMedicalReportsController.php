<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\LabResults\Models\PatientLabResult;
use Modules\PelvicExaminations\Models\PatientPelvicExamination;
use Modules\Sessions\Models\PatientSession;
use Modules\UltrasoundFindings\Models\PatientUltrasoundFinding;
use Modules\Users\Models\User;

class PatientMedicalReportsController extends Controller
{
    private array $visitTypeLabels = [
        'new_visit'    => 'New Visit',
        'follow_up'    => 'Follow-Up',
        'emergency'    => 'Emergency',
        'consultation' => 'Routine Check',
    ];

    /**
     * GET /doctor/patients/{patient}/medical-reports
     * All medical files grouped by type.
     */
    public function __invoke(User $patient): JsonResponse
    {
        abort_if($patient->role_id !== 2, 404);

        // Load dedicated uploads for each type (3 queries)
        $ultrasounds = PatientUltrasoundFinding::with(['session', 'media'])
            ->where('patient_id', $patient->id)
            ->get();

        $labResults = PatientLabResult::with(['session', 'media'])
            ->where('patient_id', $patient->id)
            ->get();

        $pelvicExams = PatientPelvicExamination::with(['session', 'media'])
            ->where('patient_id', $patient->id)
            ->get();

        // Load sessions once and extract all three media collections from them
        $sessions = PatientSession::where('patient_id', $patient->id)
            ->with('media')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'medical_reports' => [
                    'ultrasound_findings' => $ultrasounds
                        ->map(fn($r) => $this->formatRecord($r, 'file'))
                        ->concat($sessions->flatMap(fn($s) => $s->getMedia('ultrasound_findings')->map(fn($m) => $this->formatSessionMedia($m, $s))))
                        ->sortByDesc('created_at')
                        ->values(),

                    'lab_results' => $labResults
                        ->map(fn($r) => $this->formatRecord($r, 'file'))
                        ->concat($sessions->flatMap(fn($s) => $s->getMedia('lab_results')->map(fn($m) => $this->formatSessionMedia($m, $s))))
                        ->sortByDesc('created_at')
                        ->values(),

                    'pelvic_examination' => $pelvicExams
                        ->map(fn($r) => $this->formatRecord($r, 'file'))
                        ->concat($sessions->flatMap(fn($s) => $s->getMedia('pelvic_examination')->map(fn($m) => $this->formatSessionMedia($m, $s))))
                        ->sortByDesc('created_at')
                        ->values(),
                ],
            ],
        ]);
    }

    private function formatRecord($record, string $collection): array
    {
        $media = $record->getFirstMedia($collection);

        return [
            'id'         => $record->id,
            'name'       => $record->name,
            'file_url'   => $media?->getFullUrl(),
            'file_name'  => $media?->file_name,
            'session'    => $record->session_id ? [
                'id'    => $record->session->id,
                'label' => $this->sessionLabel($record->session),
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
                'label' => $this->sessionLabel($session),
            ],
            'source'     => 'session',
            'created_at' => $media->created_at?->toDateTimeString(),
        ];
    }

    private function sessionLabel(PatientSession $session): string
    {
        return ($this->visitTypeLabels[$session->visit_type] ?? ucfirst($session->visit_type ?? ''))
            . ' — ' . $session->session_date?->format('M d, Y');
    }
}
