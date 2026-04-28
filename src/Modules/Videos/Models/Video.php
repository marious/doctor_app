<?php

namespace Modules\Videos\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\CustomModel;
use Modules\Users\Models\User;
use Modules\Videos\Builders\VideoQueryBuilder;

class Video extends CustomModel
{
    protected $fillable = [
        'created_by',
        'title',
        'short_description',
        'video_url',
        'target_audience',
    ];

    public function newEloquentBuilder($query): VideoQueryBuilder
    {
        return new VideoQueryBuilder($query);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
