<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
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
            // 'notification_enabled' => ['sometimes', 'boolean'],
            // 'biometric_enabled' => ['sometimes', 'boolean'],
            'settings' => ['sometimes', 'array'],
            'settings.two_factor_auth' => ['sometimes', 'boolean'],
            'settings.appointment_reminders' => ['sometimes', 'boolean'],
            'settings.medication_reminders' => ['sometimes', 'boolean'],
            'settings.pregnancy_updates' => ['sometimes', 'boolean'],
            'settings.period_alerts' => ['sometimes', 'boolean'],
            'settings.push_notifications' => ['sometimes', 'boolean'],
            'settings.email_notifications' => ['sometimes', 'boolean'],
            'settings.lang' => ['sometimes', 'string'],
            'settings.biometric_login' => ['sometimes', 'boolean'],
            'settings.app_lock' => ['sometimes', 'boolean'],
        ];
    }
}
