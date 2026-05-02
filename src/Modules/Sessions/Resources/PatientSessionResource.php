<?php

namespace Modules\Sessions\Resources;

use Illuminate\Http\Request;
use Modules\Core\CustomResource;

class PatientSessionResource extends CustomResource
{
    public function data(Request $request): array
    {
        return [
            'id'           => $this->id,
            'patient_id'   => $this->patient_id,
            'session_date' => $this->session_date?->toDateString(),
            'visit_type'   => $this->visit_type,
            'risk_status'  => $this->risk_status,

            'vital_signs' => [
                'bp'     => $this->bp,
                'hr'     => $this->hr,
                'temp'   => $this->temp,
                'weight' => $this->weight,
            ],

            // Clinical details
            'symptoms'      => $this->symptoms,
            'diagnosis'     => $this->diagnosis,
            'treatment_plan' => $this->treatment_plan,

            // Examinations
            'ultrasound' => [
                'notes' => $this->ultrasound_notes,
                'files' => $this->getMedia('ultrasound_findings')->map(fn($m) => [
                    'id'   => $m->id,
                    'name' => $m->name,
                    'url'  => $m->getUrl(),
                    'mime' => $m->mime_type,
                ]),
            ],
            'lab_results' => [
                'notes' => $this->lab_results_notes,
                'files' => $this->getMedia('lab_results')->map(fn($m) => [
                    'id'   => $m->id,
                    'name' => $m->name,
                    'url'  => $m->getUrl(),
                    'mime' => $m->mime_type,
                ]),
            ],
            'pelvic_examination' => [
                'notes' => $this->pelvic_examination_notes,
                'files' => $this->getMedia('pelvic_examination')->map(fn($m) => [
                    'id'   => $m->id,
                    'name' => $m->name,
                    'url'  => $m->getUrl(),
                    'mime' => $m->mime_type,
                ]),
            ],

            'required_lab_tests'   => $this->required_lab_tests ?? [],
            'quick_notes' => [
                'audio_url'     => $this->getFirstMediaUrl('quick_notes_audio') ?: null,
                'transcription' => $this->quick_notes,
            ],
            'medications'          => $this->medications,
            'follow_up_date'       => $this->follow_up_date?->toDateString(),
            'private_doctor_notes' => $this->private_doctor_notes,

            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
