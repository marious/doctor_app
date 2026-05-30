<?php

namespace Modules\Appointments\Builders;

use Illuminate\Database\Eloquent\Builder;

class AppointmentQueryBuilder extends Builder
{
    // ─── Scopes ───────────────────────────────────────────────────────────────
    public function past()
    {
        return $this->where(function ($q) {
            $q->whereIn('status', ['completed', 'not_approved', 'cancelled'])
              ->orWhere('appointment_date', '<', now()->toDateString());
        });
    }

    public function upcoming()
    {
        return $this->whereIn('status', ['pending', 'under_review', 'confirmed'])
            ->where('appointment_date', '>=', now()->toDateString());
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isPast($model): bool
    {
        return in_array($model->status, ['completed', 'cancelled', 'not_approved'])
            || $model->appointment_date->isPast();
    }


    public function isUpcoming($model): bool
    {
        return in_array($model->status, ['pending', 'under_review', 'confirmed'])
            && !$model->appointment_date->isPast();
    }
}