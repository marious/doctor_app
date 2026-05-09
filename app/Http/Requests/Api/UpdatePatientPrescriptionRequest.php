<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientPrescriptionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'session_id'           => ['nullable', 'exists:patient_sessions,id'],
            'medication_name'      => ['sometimes', 'string', 'max:255'],
            'dosage'               => ['sometimes', 'string', 'max:100'],
            'frequency'            => ['sometimes', 'in:once_daily,twice_daily,three_times_daily,four_times_daily,every_8_hours,every_12_hours,as_needed,weekly'],
            'times_per_day'        => ['nullable', 'integer', 'min:1', 'max:24'],
            'start_date'           => ['sometimes', 'date'],
            'end_date'             => ['nullable', 'date', 'after_or_equal:start_date'],
            'special_instructions' => ['nullable', 'string'],
            'patient_reminders'    => ['nullable', 'boolean'],
            'auto_stop'            => ['nullable', 'boolean'],
        ];
    }
}
