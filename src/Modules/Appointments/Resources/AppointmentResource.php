<?php

namespace Modules\Appointments\Resources;

use Illuminate\Http\Request;
use Modules\Core\CustomResource;

class AppointmentResource extends CustomResource
{

    public function data(Request $request): array
    {
        return [
            'id' => $this->id,
            'service_type' => $this->service_type,
            'visit_type' => $this->service_type == 'pregnant' ? __('Pregnancy Care') : __('Gynecology Check-up'),
            'appointment_date' => $this->appointment_date?->toDateString(),
            'appointment_time' => $this->appointment_time,
            'status' => $this->status,
            'notes' => $this->notes,
            'confirmed_at' => $this->confirmed_at?->toDateTimeString(),
            'cancelled_at' => $this->cancelled_at?->toDateTimeString(),
            'created_at' => $this->created_at?->toDateTimeString(),
            'doctor' => config('app.doctor_name'),
            'clinic' => config('app.clinic_name'),

//            'doctor' => [
//                'id' => $this->doctor?->id,
//                'name' => $this->doctor?->name,
//            ],
//
//            'clinic' => [
//                'id' => $this->clinic?->id,
//                'name' => $this->clinic?->name,
//                'address' => $this->clinic?->address,
//            ],

            'diagnosis'               => $this->diagnosis,
            'additional_instructions' => $this->additional_instructions ?? [],

            'prescriptions' => $this->whenLoaded('prescriptions', fn() => $this->prescriptions->map(fn($p) => [
                'id'              => $p->id,
                'medication_name' => $p->medication_name,
                'dose_strength'   => $p->dose_strength,
                'dosage'          => $p->dosage,
                'frequency'       => $p->frequency,
                'duration_days'   => $p->duration_days,
                'warning_note'    => $p->warning_note,
            ])),

            'requested_tests' => $this->whenLoaded('requestedTests', fn() => $this->requestedTests->map(fn($t) => [
                'id'        => $t->id,
                'test_name' => $t->test_name,
                'type'      => $t->type,
                'status'    => $t->status,
            ])),

            'status_timeline' => $this->statusTimeline(),
        ];
    }
}