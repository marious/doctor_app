<?php

namespace Modules\Users\Resources;

use Illuminate\Http\Request;
use Modules\Core\CustomResource;

class PatientDirectoryResource extends CustomResource
{
    public function data(Request $request): array
    {
        $tracking = $this->patientTracking;
        $lastVisit = $this->lastCompletedAppointment;

        return [
            'id'           => $this->id,
            'patient_code' => '#P-' . str_pad($this->id, 3, '0', STR_PAD_LEFT),
            'name'         => $this->name,
            'avatar'       => $this->getFirstMediaUrl('avatar') ?: null,
            'age'          => $this->age,
            'current_mode' => $this->resolveCurrentMode($tracking),
            'risk_status'  => $this->risk_status,
            'last_visit'   => $lastVisit?->appointment_date?->format('M d, Y'),
        ];
    }

    private function resolveCurrentMode($tracking): ?array
    {
        if (!$tracking) {
            return null;
        }

        return match ($tracking->tracking_type) {
            'pregnancy' => ['key' => 'pregnancy', 'label' => 'Pregnancy'],
            'menstrual' => ['key' => 'period',    'label' => 'Period'],
            default     => null,
        };
    }
}
