<?php

namespace Modules\ServiceRegistrations\Resources;

use Illuminate\Http\Request;
use Modules\Core\CustomResource;

class PatientServiceRegistrationResource extends CustomResource
{
    public function data(Request $request): array
    {
        return [
            'id'           => $this->id,

            'patient' => [
                'id'    => $this->patient_id,
                'name'  => $this->patient_name,
                'phone' => $this->patient_phone,
            ],

            'service' => [
                'id'   => $this->service_id,
                'name' => $this->service_name,
            ],

            'service_date'     => $this->service_date?->toDateString(),
            'package_end_date' => $this->package_end_date?->toDateString(),

            'payment_summary' => [
                'total_price'       => $this->total_price,
                'amount_paid'       => $this->amount_paid,
                'remaining_amount'  => $this->remaining_amount,
                'payment_progress'  => $this->payment_progress,
            ],

            'registered_by' => $this->registeredBy?->name,
            'created_at'    => $this->created_at?->toDateTimeString(),
        ];
    }
}
