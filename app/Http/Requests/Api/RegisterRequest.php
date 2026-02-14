<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'age' => ['required', 'integer'],
            'blood_group' => ['required', 'string', 'max:5'],
            'marital_status' => ['required', 'string'],
            'date_of_marriage' => ['nullable', 'date'],
            'husband_name' => ['nullable', 'string'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'emergency_number' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }
}
