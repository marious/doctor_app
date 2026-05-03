<?php

namespace Modules\Sessions\Resources;

use Illuminate\Http\Request;
use Modules\Core\CustomResource;

class SessionTimelineResource extends CustomResource
{
    public function data(Request $request): array
    {
        return [
            'id'             => $this->id,
            'session_date'   => $this->session_date?->format('M d, Y'),
            'visit_type'     => $this->visit_type,
            'visit_type_label' => $this->resolveVisitTypeLabel(),
            'session_status' => $this->session_status,
            'diagnosis'      => $this->diagnosis,
            'follow_up_date' => $this->follow_up_date?->toDateString(),
            'medications_count' => $this->countMedications(),
            'attachments_count' => $this->countAttachments(),
        ];
    }

    private function resolveVisitTypeLabel(): string
    {
        return match ($this->visit_type) {
            'new_visit'    => 'New Visit',
            'follow_up'    => 'Follow-Up',
            'emergency'    => 'Emergency',
            'consultation' => 'Routine Check',
            default        => ucfirst($this->visit_type ?? ''),
        };
    }

    private function countMedications(): int
    {
        if (!$this->medications) {
            return 0;
        }

        return count(array_filter(array_map('trim', explode(',', $this->medications))));
    }

    private function countAttachments(): int
    {
        return $this->getMedia('ultrasound_findings')->count()
            + $this->getMedia('lab_results')->count()
            + $this->getMedia('pelvic_examination')->count();
    }
}
