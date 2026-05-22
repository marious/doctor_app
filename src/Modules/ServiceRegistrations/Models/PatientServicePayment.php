<?php

namespace Modules\ServiceRegistrations\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientServicePayment extends Model
{
    protected $table = 'patient_service_payments';

    protected $fillable = [
        'registration_id',
        'amount',
        'payment_date',
    ];

    protected $casts = [
        'payment_date' => 'date:Y-m-d',
        'amount'       => 'float',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(PatientServiceRegistration::class, 'registration_id');
    }
}
