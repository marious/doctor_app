<?php

namespace Modules\Videos\Resources;

use Illuminate\Http\Request;
use Modules\Core\CustomResource;

class VideoResource extends CustomResource
{
    public function data(Request $request): array
    {
        return [
            'id'                => $this->id,
            'title'             => $this->title,
            'short_description' => $this->short_description,
            'target_audience'   => $this->target_audience,
            'video_url'         => $this->video_url,
            'author'            => $this->whenLoaded('author', fn() => [
                'id'   => $this->author->id,
                'name' => $this->author->name,
            ]),
            'created_at'        => $this->created_at?->toDateTimeString(),
        ];
    }
}
