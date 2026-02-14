<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SendOtpRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:7'],
            'action' => ['nullable', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'phone.regex' => __('Please Enter a valid Phone Number'),
            'phone.min' => __('Please Enter a valid Phone Number'),
        ];
    }
}
