<?php

namespace Modules\Availability\Resources;

use Illuminate\Http\Request;
use Modules\Core\CustomResource;

class AvailabilityDayResource extends CustomResource
{
    public function data(Request $request): array
    {
        return [
            'date'         => $this->date?->toDateString(),
            'is_available' => $this->is_available,
            'slots'        => $this->slots->map(fn($slot) => [
                'id'   => $slot->id,
                'time' => $slot->time,
            ])->values(),
        ];
    }
}
