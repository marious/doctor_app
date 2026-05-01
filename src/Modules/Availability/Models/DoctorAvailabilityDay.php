<?php

namespace Modules\Availability\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DoctorAvailabilityDay extends Model
{
    protected $table = 'doctor_availability_days';

    protected $fillable = [
        'doctor_id',
        'date',
        'is_available',
    ];

    protected $casts = [
        'date'         => 'date:Y-m-d',
        'is_available' => 'boolean',
    ];

    public function slots(): HasMany
    {
        return $this->hasMany(DoctorAvailabilitySlot::class, 'availability_day_id')->orderBy('time');
    }
}
