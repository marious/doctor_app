<?php

namespace Modules\Prescriptions\Resources;

use Illuminate\Http\Request;
use Modules\Core\CustomResource;
use Modules\Prescriptions\Models\PatientPrescription;

class PatientPrescriptionResource extends CustomResource
{
    public function data(Request $request): array
    {
        return [
            'id'              => $this->id,
            'medication_name' => $this->medication_name,
            'dosage'          => $this->dosage,
            'frequency'       => $this->frequency,
            'frequency_label' => PatientPrescription::$frequencyLabels[$this->frequency] ?? $this->frequency,
            'times_per_day'   => $this->times_per_day,
            'start_date'      => $this->start_date?->toDateString(),
            'end_date'        => $this->end_date?->toDateString(),
            'date_range'      => $this->resolveDateRange(),
            'special_instructions' => $this->special_instructions,
            'patient_reminders'    => $this->patient_reminders,
            'auto_stop'            => $this->auto_stop,
            'status'               => $this->resolveStatus(),

            'session' => $this->when($this->session_id, fn() => [
                'id'         => $this->session?->id,
                'label'      => $this->session?->visit_type . ' — ' . $this->session?->session_date?->format('M d, Y'),
            ]),

            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }

    private function resolveDateRange(): string
    {
        $start = $this->start_date?->format('M d, Y');
        $end   = $this->end_date?->format('M d, Y');

        return $end ? "{$start} - {$end}" : "From {$start}";
    }
}
