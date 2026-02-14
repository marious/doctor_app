<?php

namespace Modules\Core\Builders;

use Illuminate\Database\Eloquent\Builder;

class OtpQueryBuilder extends Builder
{
    /**
     * Scope query to only include valid OTPs.
     *
     * @param $query
     * @return self
     */
    public function valid($query): self
    {
        return $query->where('expired_at', '>', now())
            ->where('verified_at', null)
            ->where('attempts', '<', 5);
    }

    /**
     * Find valid OTP by phone and action
     *
     * @param $phone
     * @param $action
     * @return self
     */
    public function findValidByPhone($phone, $action): self
    {
        $query = $this->model->newQuery();
        return $this->valid($query)
            ->where('phone', $phone)
            ->where('action', $action)
            ->latest();
    }

}