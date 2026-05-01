<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SaveAvailabilityRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'is_available' => ['required', 'boolean'],
            'slots'        => ['nullable', 'array'],
            'slots.*'      => ['string', 'date_format:H:i'],
        ];
    }

    public function messages(): array
    {
        return [
            'slots.*.date_format' => __('Each time slot must be in HH:mm format (e.g. 09:00).'),
        ];
    }
}
