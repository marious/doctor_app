<?php

namespace Modules\Services\Resources;

use Illuminate\Http\Request;
use Modules\Core\CustomResource;

class ClinicServiceResource extends CustomResource
{
    public function data(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'category'    => [
                'id'   => $this->category->id,
                'name' => $this->category->name,
            ],
            'price'      => $this->price,
            'is_package' => $this->is_package,
            'is_active'  => $this->is_active,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
