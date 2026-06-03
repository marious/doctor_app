<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientSessionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // Basic Information
            'session_date'   => ['required', 'date'],
            'service_type'   => ['required', 'in:pregnant,gynecology,general'],
            'visit_type'     => ['required', 'in:new_visit,follow_up,emergency,consultation'],
            'session_status' => ['nullable', 'in:completed,follow_up_required,in_progress'],
            'risk_status'    => ['nullable', 'in:stable,high_risk,monitor'],

            // Vital Signs
            'bp'     => ['nullable', 'string', 'max:20'],
            'hr'     => ['nullable', 'integer', 'min:20', 'max:300'],
            'temp'   => ['nullable', 'string', 'max:10'],
            'weight' => ['nullable', 'string', 'max:10'],

            // Clinical Details
            'symptoms'       => ['nullable', 'string', 'max:1000'],
            'diagnosis'      => ['required', 'string'],
            'treatment_plan' => ['nullable', 'string'],

            // Ultrasound Findings
            'ultrasound_notes'    => ['nullable', 'string'],
            'ultrasound_files'    => ['nullable', 'array'],
            'ultrasound_files.*'  => ['file', 'mimes:jpg,jpeg,png,pdf,dcm', 'max:10240'],

            // Lab Results
            'lab_results_notes'   => ['nullable', 'string'],
            'lab_results_files'   => ['nullable', 'array'],
            'lab_results_files.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],

            // Pelvic Examination
            'pelvic_examination_notes'   => ['nullable', 'string'],
            'pelvic_examination_files'   => ['nullable', 'array'],
            'pelvic_examination_files.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],

            // Required Lab Tests
            'required_lab_tests'             => ['nullable', 'array'],
            'required_lab_tests.*'           => ['string', 'max:200'],
            'required_lab_tests_custom'      => ['nullable', 'string', 'max:200'],

            // Quick Notes (voice recording + optional transcription text)
            'quick_notes_audio'   => ['nullable', 'file', 'mimes:mp3,mp4,m4a,wav,ogg,webm,aac', 'max:20480'],
            'quick_notes'         => ['nullable', 'string', 'max:2000'],
            'medications'         => ['nullable', 'string', 'max:1000'],
            'follow_up_date'      => ['nullable', 'date', 'after:session_date'],
            'follow_up_time'      => ['required_with:follow_up_date', 'nullable', 'date_format:H:i'],
            'private_doctor_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'diagnosis.required' => __('A primary diagnosis is required.'),
            'visit_type.in'      => __('Visit type must be one of: new_visit, follow_up, emergency, consultation.'),
            'risk_status.in'     => __('Risk status must be one of: stable, high_risk, monitor.'),
            'follow_up_date.after' => __('Follow-up date must be after the session date.'),
        ];
    }
}
