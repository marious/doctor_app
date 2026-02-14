<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CustomModel extends Model
{
    public function newEloquentBuilder($query): Builder
    {
        throw new \LogicException(sprintf("Model %s must defined `newEloquentBuilder`.`", get_class()));
    }

    /**
     * Get formatted created date.
     *
     * @return string
     */
    public function getFormattedCreatedDateAttribute()
    {
        return $this->created_at->format('Y-m-d');
    }

    /**
     * Get formatted created date and time.
     *
     * @return string
     */
    public function getFormattedCreatedDateTimeAttribute()
    {
        return $this->created_at->format('Y-m-d H:i:s');
    }

    /**
     * Get human readable created date.
     *
     * @return string
     */
    public function getHumanCreatedDateAttribute()
    {
        return $this->created_at->diffForHumans();
    }
}