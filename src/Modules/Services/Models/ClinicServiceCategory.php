<?php

namespace Modules\Services\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClinicServiceCategory extends Model
{
    protected $table = 'clinic_service_categories';

    protected $fillable = ['name'];

    public function services(): HasMany
    {
        return $this->hasMany(ClinicService::class, 'category_id');
    }
}
