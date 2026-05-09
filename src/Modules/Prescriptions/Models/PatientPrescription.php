<?php

namespace Modules\Prescriptions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Sessions\Models\PatientSession;
use Modules\Users\Models\User;

class PatientPrescription extends Model
{
    protected $table = 'patient_prescriptions';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'session_id',
        'medication_name',
        'dosage',
        'frequency',
        'times_per_day',
        'start_date',
        'end_date',
        'special_instructions',
        'patient_reminders',
        'auto_stop',
        'status',
    ];

    protected $casts = [
        'start_date'        => 'date:Y-m-d',
        'end_date'          => 'date:Y-m-d',
        'patient_reminders' => 'boolean',
        'auto_stop'         => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(PatientSession::class, 'session_id');
    }

    // ─── Computed ─────────────────────────────────────────────────────────────

    public static array $frequencyLabels = [
        'once_daily'          => 'Once daily',
        'twice_daily'         => 'Twice daily',
        'three_times_daily'   => 'Three times daily',
        'four_times_daily'    => 'Four times daily',
        'every_8_hours'       => 'Every 8 hours',
        'every_12_hours'      => 'Every 12 hours',
        'as_needed'           => 'As needed',
        'weekly'              => 'Weekly',
    ];

    public function resolveStatus(): string
    {
        if ($this->status === 'stopped') {
            return 'stopped';
        }

        if ($this->auto_stop && $this->end_date && $this->end_date->isPast()) {
            return 'completed';
        }

        return 'active';
    }
}
