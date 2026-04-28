<?php

namespace Modules\Patient\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Users\Models\User;

class HydrationLog extends Model
{
    protected $fillable = [
        'patient_id',
        'date',
        'cups_count',
    ];

    protected $casts = [
        'date' => 'date',
        'cups_count' => 'integer',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
