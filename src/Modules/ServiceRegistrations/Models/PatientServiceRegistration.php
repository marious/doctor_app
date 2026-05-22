<?php

namespace Modules\ServiceRegistrations\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Services\Models\ClinicService;
use Modules\Users\Models\User;

class PatientServiceRegistration extends Model
{
    protected $table = 'patient_service_registrations';

    protected $fillable = [
        'patient_id',
        'patient_name',
        'patient_phone',
        'service_id',
        'service_name',
        'total_price',
        'service_date',
        'package_end_date',
        'amount_paid',
        'registered_by',
    ];

    protected $casts = [
        'service_date'     => 'date:Y-m-d',
        'package_end_date' => 'date:Y-m-d',
        'total_price'      => 'float',
        'amount_paid'      => 'float',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(ClinicService::class, 'service_id');
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PatientServicePayment::class, 'registration_id')->orderBy('payment_date');
    }

    public function getPaymentStatusAttribute(): string
    {
        if ($this->amount_paid <= 0) {
            return 'unpaid';
        }

        return $this->amount_paid >= $this->total_price ? 'paid' : 'partially_paid';
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->total_price - $this->amount_paid);
    }

    public function getPaymentProgressAttribute(): int
    {
        if ($this->total_price <= 0) {
            return 100;
        }

        return (int) min(100, round(($this->amount_paid / $this->total_price) * 100));
    }
}
