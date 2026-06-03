<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Core\Resources\MediaResource;
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
         $data = [
              'medical_reports' => [
                'ultrasound_findings' => MediaResource::collection($patient->getMedia('ultrasound_findings')),
                'lab_results' => MediaResource::collection($patient->getMedia('lab_results')),
                'pelvic_examination' => MediaResource::collection($patient->getMedia('pelvic_examination')),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
   
}
