<?php

namespace Modules\Users\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Modules\Appointments\Models\Appointment;
use Modules\Patient\Models\PatientTracking;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/*
 * @property string $name
 * @property string $email
 * @property string $password
 * @property int $role_id
 * @property int $age
 * @property string $blood_group
 * @property string $martial_status
 * @property mixed $date_of_marriage
 * @property string|null $husband_name
 * @property string $phone
 * @property string|null $emergency_number
 * @property string|null $address
 * @property boolean $biometric_enabled
 * @property boolean $notification_enabled
 * @property boolean $active
 */

class User extends Authenticatable implements HasMedia
{
    use HasApiTokens, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'name', 'email', 'password', 'email_verified_at', 'role_id', 'date_of_birth', 'blood_group', 'marital_status', 'date_of_marriage', 'husband_name',
        'phone', 'emergency_number', 'address', 'biometric_enabled', 'notification_enabled', 'active', 'risk_status', 'medical_history', 'settings',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'date_of_birth' => 'date:Y-m-d',
        'date_of_marriage' => 'date:Y-m-d',
        'password' => 'hashed',
        'active' => 'boolean',
        'notification_enabled' => 'boolean',
        'biometric_enabled' => 'boolean',
        'medical_history' => 'array',
        'settings' => 'array',
    ];

    public function getDateOfMarriageAttribute($value)
    {
        return $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null;
    }

    public function getAgeAttribute()
    {
        return $this->date_of_birth ? \Carbon\Carbon::parse($this->date_of_birth)->age : null;
    }

    public function patientTracking(): HasOne
    {
        return $this->hasOne(PatientTracking::class, 'patient_id')->latestOfMany();
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'patient_id');
    }

    public function lastCompletedAppointment(): HasOne
    {
        return $this->hasOne(Appointment::class, 'patient_id')
            ->where('status', 'completed')
            ->latestOfMany('appointment_date');
    }
}