<?php

namespace Modules\Patient\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CycleSymptomLog extends Model
{
    protected $fillable = [
        'patient_tracking_id',
        'logged_date',
        'symptom',
        'severity',
    ];

    protected $casts = [
        'logged_date' => 'date',
    ];

    public function tracking(): BelongsTo
    {
        return $this->belongsTo(PatientTracking::class, 'patient_tracking_id');
    }
}
