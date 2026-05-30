<?php

namespace Modules\Articles\Builders;

use Illuminate\Database\Eloquent\Builder;

class ArticleQueryBuilder extends Builder
{
    public function forAudience(string $audience): static
    {
        $audiences = ['all', $audience];

        // Trimester-specific patients also receive general pregnancy articles.
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
