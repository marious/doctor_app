<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class BulkSaveAvailabilityRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'dates'        => ['required', 'array', 'min:1'],
            'dates.*'      => ['required', 'date_format:Y-m-d'],
            'is_available' => ['required', 'boolean'],
            'slots'        => ['nullable', 'array'],
            'slots.*'      => ['string', 'date_format:H:i'],
        ];
    }

    public function messages(): array
    {
        return [
            'dates.required'       => __('At least one date is required.'),
            'dates.*.date_format'  => __('Each date must be in YYYY-MM-DD format (e.g. 2026-08-01).'),
            'slots.*.date_format'  => __('Each time slot must be in HH:mm format (e.g. 09:00).'),
        ];
    }
}
