<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //return parent::toArray($request);
        return  [
            'id' => $this->id,
            'title' => $this->title,
            'authors' => AuthorResource::collection($this->whenLoaded('authors')),
            'publisher' => $this->publisher,
            'year' => $this->year,
        ];
    }
}
