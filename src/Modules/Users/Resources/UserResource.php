<?php

namespace Modules\Users\Resources;

use Illuminate\Http\Request;
use Modules\Core\Resources\MediaResource;
use Modules\Core\CustomResource;

class UserResource extends CustomResource
{

    public function data(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'phone' => $this->resource->phone,
            'date_of_birth' => $this->resource->date_of_birth ? \Carbon\Carbon::parse($this->resource->date_of_birth)->format('Y-m-d') : null,
            'age' => $this->resource->age,
            'blood_group' => $this->resource->blood_group,
            'marital_status' => $this->resource->marital_status,
            'address' => $this->resource->address,
            'date_of_marriage' => $this->resource->date_of_marriage,
            'husband_name' => $this->resource->husband_name,
            'emergency_number' => $this->resource->emergency_number,
            'biometric_enabled' => $this->resource->biometric_enabled,
            'notification_enabled' => $this->resource->notification_enabled,
            'settings' => array_merge([
                'two_factor_auth' => true,
                'appointment_reminders' => true,
                'medication_reminders' => true,
                'pregnancy_updates' => true,
                'period_alerts' => true,
                'push_notifications' => true,
                'email_notifications' => false,
                'biometric_login' => true,
                'app_lock' => false,
                'lang' => 'en',
            ], $this->resource->settings ?? []),
            'avatar' => MediaResource::make($this->resource->getMedia('avatar')->last() ?? []),
            'medical_reports' => [
                'ultrasound_findings' => MediaResource::collection($this->resource->getMedia('ultrasound_findings')),
                'lab_results' => MediaResource::collection($this->resource->getMedia('lab_results')),
                'pelvic_examination' => MediaResource::collection($this->resource->getMedia('pelvic_examination')),
            ],
            'medical_history' => $this->resource->medical_history ?? [],
            'auth_token' => $this->resource->auth_token,
        ];
    }
}