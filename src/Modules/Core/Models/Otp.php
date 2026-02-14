<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Builders\OtpQueryBuilder;
use Modules\Users\Models\User;

class Otp extends CustomModel
{
    protected $table = 'otp';
    protected $fillable = [
        'user_id',
        'phone',
        'email',
        'code',
        'action',
        'expired_at',
        'verified_at',
        'attempts',
        'is_used',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
        'verified_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    public function newEloquentBuilder($query): OtpQueryBuilder
    {
        return new OtpQueryBuilder($query);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Increment Attempts Count
     *
     * @return void
     */
    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }

    /**
     * Check the OTP is expired
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check the OTP is verified
     *
     * @return bool
     */
    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    /**
     * Verify the OTP
     *
     * @return void
     */
    public function verify()
    {
        $this->update(['verified_at' => now()]);
    }
}