<?php

namespace Modules\Appointments\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Core\CustomResource;

class DoctorAppointmentResource extends CustomResource
{
    public function data(Request $request): array
    {
        return [
            'id'               => $this->id,
            'patient'          => [
                'id'     => $this->patient?->id,
                'name'   => $this->patient?->name,
                'avatar' => $this->patient?->getFirstMediaUrl('avatar') ?: null,
            ],
            'date_label'       => $this->resolveDateLabel(),
            'appointment_date' => $this->appointment_date?->toDateString(),
            'appointment_time' => $this->appointment_time
                ? Carbon::createFromFormat('H:i', $this->appointment_time)->format('h:i A')
                : null,
            'visit_type'       => $this->service_type === 'pregnant'
                ? __('Pregnancy Care')
                : __('Gynecology Check-up'),
            'status'           => $this->status == 'not_approved' ? 'cancelled': $this->status,
            // 'actions'          => $this->resolveActions(),
        ];
    }

    private function resolveDateLabel(): string
    {
        $date = $this->appointment_date;

        if (!$date) {
            return '';
        }

        if ($date->isToday()) {
            return 'Today';
        }

        if ($date->isTomorrow()) {
            return 'Tomorrow';
        }

        return $date->toDateString();
    }

    private function resolveActions(): array
    {
        return match ($this->status) {
            'pending', 'under_review' => ['approve', 'reject'],
            'confirmed'               => ['reschedule'],
            'cancelled'               => ['reschedule'],
            default                   => [],
        };
    }
}
