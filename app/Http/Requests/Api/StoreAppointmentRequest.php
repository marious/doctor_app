<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'service_type' => ['required', Rule::in(['pregnant', 'gynecology'])],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'service_type.required' => __('Please choose a service (Pregnant or Gynecology).'),
            'service_type.in' => __('Invalid service type.'),
            'appointment_date.required' => __('Please select an appointment date.'),
            'appointment_date.after_or_equal' => __('Appointment date must be today or in the future.'),
            'appointment_time.required' => __('Please select a time slot.'),
            'appointment_time.date_format' => __('Invalid time format. Use HH:MM.'),
        ];
    }
}
