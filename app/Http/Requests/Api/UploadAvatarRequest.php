<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UploadAvatarRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.required' => __('Profile image is required'),
            'avatar.image' => __('Profile image must be an image'),
            'avatar.mimes' => __('Profile image must be a valid image format'),
            'avatar.max' => __('Profile image must not exceed 2MB'),
        ];
    }
}
