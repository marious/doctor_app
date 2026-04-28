<?php

namespace Modules\Videos\Builders;

use Illuminate\Database\Eloquent\Builder;

class VideoQueryBuilder extends Builder
{
    public function forAudience(string $audience): static
    {
        return $this->where(function ($q) use ($audience) {
            $q->where('target_audience', 'all')
              ->orWhere('target_audience', $audience);
        });
    }

    public function byNewest(): static
    {
        return $this->latest();
    }
}
