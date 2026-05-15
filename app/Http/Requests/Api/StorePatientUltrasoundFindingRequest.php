<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientUltrasoundFindingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'session_id' => ['nullable', 'exists:patient_sessions,id'],
            'name'       => ['required', 'string', 'max:255'],
            'file'       => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ];
    }
}
