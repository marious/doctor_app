<?php

namespace Modules\Appointments\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Appointments\Builders\RequestedTestQueryBuilder;
use Modules\Core\Models\CustomModel;

class RequestedTest extends CustomModel
{
    protected $fillable = ['appointment_id', 'test_name', 'type', 'status'];

    public function newEloquentBuilder($query): RequestedTestQueryBuilder
    {
        return new RequestedTestQueryBuilder($query);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}