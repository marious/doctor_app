<?php

namespace Modules\Advertisements\Builders;

use Illuminate\Database\Eloquent\Builder;

class AdvertisementQueryBuilder extends Builder
{
    public function byNewest(): static
    {
        return $this->latest();
    }
}
