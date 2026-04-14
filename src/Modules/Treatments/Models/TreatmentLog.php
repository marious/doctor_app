<?php

namespace Modules\Treatments\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Appointments\Models\Prescription;
use Modules\Core\Models\CustomModel;

class TreatmentLog extends CustomModel
{
    protected $fillable = [
        'patient_id',
        'prescription_id',
        'date',
        'time_of_day',
        'status',
        'action_at',
    ];

    protected $casts = [
        'date' => 'date',
        'action_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }
}
