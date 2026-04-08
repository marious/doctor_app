<?php

namespace Modules\Patient\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\CustomModel;
use Modules\Users\Models\User;

class PatientTracking extends CustomModel
{
    protected $fillable = [
        'patient_id',
        'tracking_type',

        // Menstrual
        'last_menstruation_date',
        'cycle_length',
        'period_duration',

        // Pregnancy
        'lmp_date',
        'pregnancy_test_status',

        // Computed
        'next_period_date',
        'ovulation_date',
        'estimated_due_date',
    ];

    protected $casts = [
        'last_menstruation_date' => 'date',
        'lmp_date' => 'date',
        'next_period_date' => 'date',
        'ovulation_date' => 'date',
        'estimated_due_date' => 'date',
        'cycle_length' => 'integer',
        'period_duration' => 'integer',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────
    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    // ─── Computed Helpers ─────────────────────────────────────────────────────


    /**
     * Calculate next period date based on last menstruation + cycle length.
     */
    public function computeNextPeriodDate(): ?Carbon
    {
        if (!$this->last_menstruation_date || !$this->cycle_length) {
            return null;
        }

        return $this->last_menstruation_date->copy()->addDays($this->cycle_length);
    }

    /**
     * Ovulation typically occurs ~14 days before next period.
     */
    public function computeOvulationDate(): ?Carbon
    {
        $nextPeriod = $this->computeNextPeriodDate();

        return $nextPeriod?->copy()->subDays(14);
    }

    /**
     * Estimated due date = LMP + 280 days (Naegele's Rule).
     */
    public function computeEstimatedDueDate(): ?Carbon
    {
        if (!$this->lmp_date) {
            return null;
        }

        return $this->lmp_date->copy()->addDays(280);
    }

    /**
     * Gestational age in weeks based on LMP.
     */
    public function gestationalAgeInWeeks(): ?int
    {
        if (!$this->lmp_date) {
            return null;
        }

        return (int) $this->lmp_date->diffInWeeks(Carbon::today());
    }
}