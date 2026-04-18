<?php

namespace Modules\Patient\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthStat extends Model
{
    protected $fillable = [
        'patient_tracking_id',
        'weight_kg',
        'bpm',
        'recorded_at',
        'weight_status', // healthy_gain | underweight | overweight
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'weight_kg'   => 'float',
        'bpm'         => 'integer',
    ];

    public function tracking(): BelongsTo
    {
        return $this->belongsTo(PatientTracking::class, 'patient_tracking_id');
    }

    /**
     * Evaluate weight gain status based on gestational week
     * Using IOM guidelines for normal pre-pregnancy BMI
     */
    public static function evaluateWeightGain(float $weightGainKg, int $gestationalWeek): string
    {
        // Expected gain per week ~0.45kg after week 12
        $expectedMin = $gestationalWeek > 12 ? ($gestationalWeek - 12) * 0.36 : 0;
        $expectedMax = $gestationalWeek > 12 ? ($gestationalWeek - 12) * 0.54 : 1;

        if ($weightGainKg < $expectedMin) return 'underweight';
        if ($weightGainKg > $expectedMax) return 'overweight';
        return 'healthy_gain';
    }
}