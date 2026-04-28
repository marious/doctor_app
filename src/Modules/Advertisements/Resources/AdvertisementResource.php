<?php

namespace Modules\Advertisements\Resources;

use Illuminate\Http\Request;
use Modules\Core\CustomResource;

class AdvertisementResource extends CustomResource
{
    public function data(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'banner_image' => $this->banner_url,
            'button_text' => $this->button_text,
            'button_link' => $this->button_link,
            'created_at'  => $this->created_at?->toDateTimeString(),
        ];
    }
}
