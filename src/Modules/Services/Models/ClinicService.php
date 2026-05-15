<?php

namespace Modules\Services\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClinicService extends Model
{
    use SoftDeletes;

    protected $table = 'clinic_services';

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'is_package',
        'is_active',
    ];

    protected $casts = [
        'price'      => 'float',
        'is_package' => 'boolean',
        'is_active'  => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ClinicServiceCategory::class, 'category_id');
    }
}
