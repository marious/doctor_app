<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreArticleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title'             => ['required', 'string', 'max:255'],
            'short_description' => ['required', 'string', 'max:500'],
            'full_text'         => ['nullable', 'string'],
            'target_audience'   => ['required', Rule::in([
                'all', 'pregnancy', 'pregnancy_1st', 'pregnancy_2nd', 'pregnancy_3rd', 'gynecology',
            ])],
            'cover_image'       => ['nullable', 'image', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'             => __('Article title is required.'),
            'short_description.required' => __('Short description is required.'),
            'target_audience.required'   => __('Please select a target audience.'),
            'target_audience.in'         => __('Invalid target audience selected.'),
            'cover_image.image'          => __('Cover must be a valid image file.'),
            'cover_image.max'            => __('Cover image must not exceed 5MB.'),
        ];
    }
}
