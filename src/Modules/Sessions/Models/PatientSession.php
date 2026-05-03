<?php

namespace Modules\Sessions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Modules\Users\Models\User;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PatientSession extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'patient_sessions';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'session_date',
        'visit_type',
        'session_status',
        'risk_status',
        'bp',
        'hr',
        'temp',
        'weight',
        'symptoms',
        'diagnosis',
        'treatment_plan',
        'ultrasound_notes',
        'lab_results_notes',
        'pelvic_examination_notes',
        'required_lab_tests',
        'quick_notes',
        'medications',
        'follow_up_date',
        'private_doctor_notes',
    ];

    protected $casts = [
        'session_date'       => 'date:Y-m-d',
        'follow_up_date'     => 'date:Y-m-d',
        'required_lab_tests' => 'array',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('ultrasound_findings');
        $this->addMediaCollection('lab_results');
        $this->addMediaCollection('pelvic_examination');
        $this->addMediaCollection('quick_notes_audio')->singleFile();
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function symptoms(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => explode(',', $value)
        );
    }
}
