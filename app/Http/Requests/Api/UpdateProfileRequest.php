<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $this->user()->id],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone,' . $this->user()->id],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'blood_group' => ['required', 'string', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'marital_status' => ['required', 'string', 'in:single,married'],
            'address' => ['nullable', 'string'],
            'emergency_number' => ['nullable', 'string', 'max:20'],
            'date_of_marriage' => ['nullable', 'date', 'required_if:marital_status,married'],
            'husband_name' => ['nullable', 'string', 'max:255', 'required_if:marital_status,married'],
            'medical_history' => ['nullable', 'array'],
            'medical_history.*' => ['string'],
        ];
    }
}
