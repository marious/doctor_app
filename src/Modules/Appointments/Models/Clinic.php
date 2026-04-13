<?php

namespace Modules\Appointments\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Appointments\Builders\ClinicQueryBuilder;
use Modules\Core\Models\CustomModel;

class Clinic extends CustomModel
{
    protected $fillable = ['name', 'address', 'phone'];

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function newEloquentBuilder($query): ClinicQueryBuilder
    {
       return new ClinicQueryBuilder($query);
    }
}