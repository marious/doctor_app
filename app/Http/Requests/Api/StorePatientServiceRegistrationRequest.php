<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientServiceRegistrationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'patient_id'       => ['required', 'exists:users,id'],
            'service_id'       => ['required', 'exists:clinic_services,id'],
            'service_date'     => ['required', 'date'],
            'package_end_date' => ['nullable', 'date', 'after_or_equal:service_date'],
            'amount_paid'      => ['required', 'numeric', 'min:0'],
        ];
    }
}
