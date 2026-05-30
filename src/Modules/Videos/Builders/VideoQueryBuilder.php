<?php

namespace Modules\Videos\Builders;

use Illuminate\Database\Eloquent\Builder;

class VideoQueryBuilder extends Builder
{
    public function forAudience(string $audience): static
    {
        $audiences = ['all', $audience];

        if (in_array($audience, ['pregnancy_1st', 'pregnancy_2nd', 'pregnancy_3rd'])) {
            $audiences[] = 'pregnancy';
        }

        return $this->whereIn('target_audience', $audiences);
    }

    public function byNewest(): static
    {
        return $this->latest();
    }
}
