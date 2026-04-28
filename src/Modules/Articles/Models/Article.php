<?php

namespace Modules\Articles\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Articles\Builders\ArticleQueryBuilder;
use Modules\Core\Models\CustomModel;
use Modules\Users\Models\User;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Article extends CustomModel implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'created_by',
        'title',
        'short_description',
        'full_text',
        'target_audience',
    ];

    public function newEloquentBuilder($query): ArticleQueryBuilder
    {
        return new ArticleQueryBuilder($query);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('cover') ?: null;
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
