<?php

namespace Modules\Patient\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Symptom extends Model
{
    protected $fillable = ['key', 'label', 'group', 'sort_order'];

    public function logs(): HasMany
    {
        return $this->hasMany(CycleSymptomLog::class);
    }
}
