<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailBookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return  [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'edition' => $this->edition,
            'authors' => AuthorResource::collection($this->whenLoaded('authors')),
            'publisher' => $this->publisher,
            'year' => $this->year,
            'genres' => GenreResource::collection($this->whenLoaded('genres')),
            'format' => $this->format,
            'pages' => $this->pages,
            'country' => $this->country,
            'isbn' => $this->isbn,
        ];
    }
}
