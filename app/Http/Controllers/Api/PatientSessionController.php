<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePatientSessionRequest;
use App\Http\Requests\Api\UpdatePatientSessionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\Sessions\Models\PatientSession;
use Modules\Sessions\Resources\PatientSessionResource;
use Modules\Users\Models\User;

class PatientSessionController extends Controller
{
    /**
     * POST /doctor/patients/{patient}/sessions
     * Record a new clinical session for a patient.
     */
    public function store(StorePatientSessionRequest $request, User $patient): JsonResponse
    {
        abort_if($patient->role_id !== 2, 404);

        // Merge custom lab test into the list if provided
        $labTests = $request->input('required_lab_tests', []);

        if ($request->filled('required_lab_tests_custom')) {
            $labTests[] = $request->required_lab_tests_custom;
        }

        $session = PatientSession::create([
            'patient_id'               => $patient->id,
            'doctor_id'                => Auth::id(),
            'session_date'             => $request->session_date,
            'visit_type'               => $request->visit_type,
            'session_status'           => $request->session_status ?? 'completed',
            'risk_status'              => $request->risk_status,
            'bp'                       => $request->bp,
            'hr'                       => $request->hr,
            'temp'                     => $request->temp,
            'weight'                   => $request->weight,
            'symptoms'                 => $request->symptoms,
            'diagnosis'                => $request->diagnosis,
            'treatment_plan'           => $request->treatment_plan,
            'ultrasound_notes'         => $request->ultrasound_notes,
            'lab_results_notes'        => $request->lab_results_notes,
            'pelvic_examination_notes' => $request->pelvic_examination_notes,
            'required_lab_tests'       => $labTests ?: null,
            'quick_notes'              => $request->quick_notes,
            'medications'              => $request->medications,
            'follow_up_date'           => $request->follow_up_date,
            'private_doctor_notes'     => $request->private_doctor_notes,
        ]);

        // Attach uploaded files to their collections
        $this->attachFiles($session, $request, 'ultrasound_files', 'ultrasound_findings');
        $this->attachFiles($session, $request, 'lab_results_files', 'lab_results');
        $this->attachFiles($session, $request, 'pelvic_examination_files', 'pelvic_examination');

        // Attach voice recording for quick notes
        if ($request->hasFile('quick_notes_audio')) {
            $audio = $request->file('quick_notes_audio');
            $session
                ->addMedia($audio)
                ->usingName(Str::slug(pathinfo($audio->getClientOriginalName(), PATHINFO_FILENAME)))
                ->toMediaCollection('quick_notes_audio');
        }

        // Update patient's risk status if provided
        if ($request->filled('risk_status')) {
            $patient->update(['risk_status' => $request->risk_status]);
        }

        return response()->json([
            'success' => true,
            'message' => __('Patient session recorded successfully.'),
            'data'    => new PatientSessionResource($session),
        ], 201);
    }

    /**
     * GET /doctor/patients/{patient}/sessions
     * List all sessions for a patient (most recent first).
     */
    public function index(User $patient): JsonResponse
    {
        abort_if($patient->role_id !== 2, 404);

        $sessions = PatientSession::with([])
            ->where('patient_id', $patient->id)
            ->orderByDesc('session_date')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => PatientSessionResource::collection($sessions),
            'meta'    => [
                'current_page' => $sessions->currentPage(),
                'last_page'    => $sessions->lastPage(),
                'per_page'     => $sessions->perPage(),
                'total'        => $sessions->total(),
            ],
        ]);
    }

    /**
     * GET /doctor/patients/{patient}/sessions/{session}
     * Single session detail.
     */
    public function show(User $patient, PatientSession $session): JsonResponse
    {
        abort_if($session->patient_id !== $patient->id, 404);

        return response()->json([
            'success' => true,
            'data'    => new PatientSessionResource($session),
        ]);
    }

    /**
     * POST /doctor/patients/{patient}/sessions/{session}
     * Update an existing clinical session.
     */
    public function update(UpdatePatientSessionRequest $request, User $patient, PatientSession $session): JsonResponse
    {
        abort_if($session->patient_id !== $patient->id, 404);

        // Merge custom lab test into the list if provided
        $labTests = $request->input('required_lab_tests', $session->required_lab_tests ?? []);

        if ($request->filled('required_lab_tests_custom')) {
            $labTests[] = $request->required_lab_tests_custom;
        }

        $session->update(array_filter([
            'session_date'             => $request->session_date,
            'visit_type'               => $request->visit_type,
            'session_status'           => $request->session_status,
            'risk_status'              => $request->risk_status,
            'bp'                       => $request->bp,
            'hr'                       => $request->hr,
            'temp'                     => $request->temp,
            'weight'                   => $request->weight,
            'symptoms'                 => $request->symptoms,
            'diagnosis'                => $request->diagnosis,
            'treatment_plan'           => $request->treatment_plan,
            'ultrasound_notes'         => $request->ultrasound_notes,
            'lab_results_notes'        => $request->lab_results_notes,
            'pelvic_examination_notes' => $request->pelvic_examination_notes,
            'required_lab_tests'       => $labTests ?: null,
            'quick_notes'              => $request->quick_notes,
            'medications'              => $request->medications,
            'follow_up_date'           => $request->follow_up_date,
            'private_doctor_notes'     => $request->private_doctor_notes,
        ], fn($v) => $v !== null));

        // Append any new files to existing collections
        $this->attachFiles($session, $request, 'ultrasound_files', 'ultrasound_findings');
        $this->attachFiles($session, $request, 'lab_results_files', 'lab_results');
        $this->attachFiles($session, $request, 'pelvic_examination_files', 'pelvic_examination');

        // Replace audio if a new one is uploaded (singleFile collection)
        if ($request->hasFile('quick_notes_audio')) {
            $audio = $request->file('quick_notes_audio');
            $session
                ->addMedia($audio)
                ->usingName(Str::slug(pathinfo($audio->getClientOriginalName(), PATHINFO_FILENAME)))
                ->toMediaCollection('quick_notes_audio');
        }

        // Sync patient risk status if changed
        if ($request->filled('risk_status')) {
            $patient->update(['risk_status' => $request->risk_status]);
        }

        return response()->json([
            'success' => true,
            'message' => __('Patient session updated successfully.'),
            'data'    => new PatientSessionResource($session->fresh()),
        ]);
    }

    // ─── Private Helpers ─────────────────────────────────────────────────────

    private function attachFiles(PatientSession $session, $request, string $field, string $collection): void
    {
        if (!$request->hasFile($field)) {
            return;
        }

        foreach ($request->file($field) as $file) {
            $session
                ->addMedia($file)
                ->usingName(Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)))
                ->toMediaCollection($collection);
        }
    }
}
