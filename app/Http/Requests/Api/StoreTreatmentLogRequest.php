<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreTreatmentLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'prescription_id' => ['required', 'exists:prescriptions,id'],
            'date' => ['required', 'date'],
            'time_of_day' => ['required', 'in:morning,afternoon,evening,night'],
            'status' => ['required', 'in:taken,skipped'],
        ];
    }
}
