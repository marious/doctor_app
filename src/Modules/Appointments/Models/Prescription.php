<?php

namespace Modules\Appointments\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Appointments\Builders\PrescriptionQueryBuilder;
use Modules\Core\Models\CustomModel;

class Prescription extends CustomModel
{
    protected $fillable = [
        'appointment_id',
        'medication_name',
        'dosage',
        'frequency',
        'duration_days',
    ];

    public function newEloquentBuilder($query): PrescriptionQueryBuilder
    {
        return new PrescriptionQueryBuilder($query);
    }


    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}