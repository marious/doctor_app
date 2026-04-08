<?php

namespace Modules\Patient\Resources;

use Illuminate\Http\Request;
use Modules\Core\CustomResource;

class PatientTrackingResource extends CustomResource
{

    public function data(Request $request): array
    {
        $base = [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'tracking_type' => $this->tracking_type,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];


        if ($this->tracking_type === 'menstrual') {
            $base['menstrual'] = [
                'last_menstruation_date' => $this->last_menstruation_date?->toDateString(),
                'cycle_length' => $this->cycle_length,
                'period_duration' => $this->period_duration,
                'next_period_date' => $this->next_period_date?->toDateString(),
                'ovulation_date' => $this->ovulation_date?->toDateString(),
                'days_until_next_period' => $this->next_period_date
                    ? max(0, (int)now()->diffInDays($this->next_period_date, false))
                    : null,
            ];
        }

        if ($this->tracking_type === 'pregnancy') {
            $base['pregnancy'] = [
                'lmp_date' => $this->lmp_date?->toDateString(),
                'pregnancy_test_status' => $this->pregnancy_test_status,
                'estimated_due_date' => $this->estimated_due_date?->toDateString(),
                'gestational_age_weeks' => $this->gestationalAgeInWeeks(),
                'days_until_due_date' => $this->estimated_due_date
                    ? max(0, (int)now()->diffInDays($this->estimated_due_date, false))
                    : null,
            ];
        }

        return $base;
    }
}