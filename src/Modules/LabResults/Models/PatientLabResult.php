<?php

namespace Modules\LabResults\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Sessions\Models\PatientSession;
use Modules\Users\Models\User;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PatientLabResult extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'patient_lab_results';

    protected $fillable = [
        'patient_id',
        'session_id',
        'name',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('file')->singleFile();
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(PatientSession::class, 'session_id');
    }
}
