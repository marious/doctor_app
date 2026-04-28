<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVideoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title'             => ['sometimes', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'video_url'         => ['sometimes', 'url'],
            'target_audience'   => ['sometimes', Rule::in([
                'all', 'pregnancy', 'pregnancy_1st', 'pregnancy_2nd', 'pregnancy_3rd', 'gynecology',
            ])],
        ];
    }

    public function messages(): array
    {
        return [
            'video_url.url'      => __('Please provide a valid URL.'),
            'target_audience.in' => __('Invalid target audience selected.'),
        ];
    }
}
