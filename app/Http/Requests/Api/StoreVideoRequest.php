<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVideoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title'             => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'video_url'         => ['required', 'url'],
            'target_audience'   => ['required', Rule::in([
                'all', 'pregnancy', 'pregnancy_1st', 'pregnancy_2nd', 'pregnancy_3rd', 'gynecology',
            ])],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'           => __('Video title is required.'),
            'video_url.required'       => __('Please provide a video URL.'),
            'video_url.url'            => __('Please provide a valid URL.'),
            'target_audience.required' => __('Please select a target audience.'),
            'target_audience.in'       => __('Invalid target audience selected.'),
        ];
    }
}
