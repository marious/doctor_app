<?php

namespace Modules\Users\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Core\CustomResource;
use Modules\Sessions\Models\PatientSession;
use Modules\Sessions\Resources\SessionTimelineResource;

class PatientProfileResource extends CustomResource
{
    public function data(Request $request): array
    {
        $sessions = PatientSession::where('patient_id', $this->id)
            ->orderByDesc('session_date')
            ->get();

        $thisMonth = $sessions->filter(
            fn($s) => $s->session_date?->isCurrentMonth()
        )->count();

        return [
            // ── Left panel ────────────────────────────────────────────────
            'patient' => [
                'id'                  => $this->id,
                'patient_code'        => '#P-' . str_pad($this->id, 3, '0', STR_PAD_LEFT),
                'name'                => $this->name,
                'avatar'              => $this->getFirstMediaUrl('avatar') ?: null,
                'age'                 => $this->age,
                'risk_status'         => $this->risk_status,
                'last_visit'          => $this->lastCompletedAppointment?->appointment_date?->format('M d, Y'),
                'phone'               => $this->phone,
                'blood_group'         => $this->blood_group,
                'allergies'           => $this->allergies ?? [],
                'current_medications' => $this->current_medications ?? [],
                'medical_history'     => $this->medical_history ?? [],
            ],

            // ── Stats row ─────────────────────────────────────────────────
            'stats' => [
                'total_sessions'   => $sessions->count(),
                'this_month'       => $thisMonth,
                'completed'        => $sessions->where('session_status', 'completed')->count(),
                'follow_up'        => $sessions->where('session_status', 'follow_up_required')->count(),
            ],

            // ── Session timeline ──────────────────────────────────────────
            'sessions' => SessionTimelineResource::collection($sessions),
        ];
    }
}
