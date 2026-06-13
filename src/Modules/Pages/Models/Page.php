<?php

namespace Modules\Pages\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = ['slug', 'title', 'content'];

    // Slugs whose content is a JSON array/object rather than HTML
    private const JSON_SLUGS = ['faq', 'contact_support'];

    public function getContentAttribute(string $value): mixed
    {
        return in_array($this->slug, self::JSON_SLUGS)
            ? json_decode($value, true)
            : $value;
    }
}
