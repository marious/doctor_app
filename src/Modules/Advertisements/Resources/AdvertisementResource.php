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
            'title'                => $this->title,
            'position_title'       => $this->position_title,
            'description'          => $this->description,
            'position_description' => $this->position_description,
            'banner_image'         => $this->banner_url,
            'button_text'          => $this->button_text,
            'button_link'          => $this->button_link,
            'position_button'      => $this->position_button,
            'created_at'  => $this->created_at?->toDateTimeString(),
        ];
    }
}
