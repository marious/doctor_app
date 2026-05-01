<?php

namespace Modules\Availability\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorAvailabilitySlot extends Model
{
    protected $table = 'doctor_availability_slots';

    protected $fillable = [
        'availability_day_id',
        'time',
    ];

    public function day(): BelongsTo
    {
        return $this->belongsTo(DoctorAvailabilityDay::class, 'availability_day_id');
    }
}
