<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdvertisementRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string', 'max:1000'],
            'banner_image' => ['nullable', 'image', 'max:5120'],
            'button_text'  => ['nullable', 'string', 'max:100'],
            'button_link'  => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'    => __('Banner title is required.'),
            'banner_image.image' => __('Banner must be a valid image file.'),
            'banner_image.max'  => __('Banner image must not exceed 5MB.'),
        ];
    }
}
