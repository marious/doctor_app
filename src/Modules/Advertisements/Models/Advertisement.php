<?php

namespace Modules\Advertisements\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Advertisements\Builders\AdvertisementQueryBuilder;
use Modules\Core\Models\CustomModel;
use Modules\Users\Models\User;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Advertisement extends CustomModel implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'created_by',
        'title',
        'description',
        'button_text',
        'button_link',
    ];

    public function newEloquentBuilder($query): AdvertisementQueryBuilder
    {
        return new AdvertisementQueryBuilder($query);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('banner')->singleFile();
    }

    public function getBannerUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('banner') ?: null;
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
