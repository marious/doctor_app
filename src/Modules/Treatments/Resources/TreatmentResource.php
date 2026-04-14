<?php

namespace Modules\Treatments\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TreatmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'appointment_id' => $this->appointment_id,
            'medication_name' => $this->medication_name,
            'dosage' => $this->dosage,
            'frequency' => $this->frequency,
            'duration_days' => $this->duration_days,
            'started_at' => $this->created_at->toDateString(),
            'ends_at' => $this->created_at->addDays($this->duration_days)->toDateString(),
            'doctor' => [
                'name' => $this->appointment?->doctor?->name,
            ],
            'clinic' => [
                'name' => $this->appointment?->clinic?->name,
            ],
        ];
    }
}
