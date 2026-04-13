<?php

namespace Modules\Appointments\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Appointments\Builders\AppointmentQueryBuilder;
use Modules\Core\Models\CustomModel;
use Modules\Users\Models\User;

class Appointment extends CustomModel
{
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'clinic_id',
        'service_type',
        'visit_type',
        'appointment_date',
        'appointment_time',
        'status',
        'notes',
        'confirmed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function newEloquentBuilder($query): AppointmentQueryBuilder
    {
        return new AppointmentQueryBuilder($query);
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function requestedTests(): HasMany
    {
        return $this->hasMany(RequestedTest::class);
    }

    /**
     * Status timeline for the UI (matches the 3-step status screen in the app).
     */
    public function statusTimeline(): array
    {
        $steps = [
            ['key' => 'pending', 'label' => 'Request Submitted', 'desc' => 'Your appointment request has been sent successfully'],
            ['key' => 'under_review', 'label' => 'Under Review', 'desc' => 'Your appointment is currently being reviewed by the clinic'],
            ['key' => 'confirmed', 'label' => 'Confirmed', 'desc' => 'Your appointment has been successfully approved'],
        ];

        if ($this->status === 'not_approved') {
            $steps[2] = ['key' => 'not_approved', 'label' => 'Not Approved', 'desc' => 'This appointment could not be approved'];
        }

        $order = ['pending', 'under_review', 'confirmed', 'not_approved'];
        $currentIndex = array_search($this->status, $order);

        return array_map(function ($step, $index) use ($currentIndex, $order) {
            $stepIndex = array_search($step['key'], $order);
            return array_merge($step, [
                'state' => $stepIndex < $currentIndex ? 'completed'
                    : ($stepIndex === $currentIndex ? 'active' : 'pending'),
            ]);
        }, $steps, array_keys($steps));
    }
}