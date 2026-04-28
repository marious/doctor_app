<?php

namespace Modules\Articles\Resources;

use Illuminate\Http\Request;
use Modules\Core\CustomResource;

class ArticleResource extends CustomResource
{
    public function data(Request $request): array
    {
        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'short_description' => $this->short_description,
            'full_text'        => $this->full_text,
            'target_audience'  => $this->target_audience,
            'cover_image'      => $this->cover_url,
            'author'           => $this->whenLoaded('author', fn() => [
                'id'   => $this->author->id,
                'name' => $this->author->name,
            ]),
            'created_at'       => $this->created_at?->toDateTimeString(),
        ];
    }
}
