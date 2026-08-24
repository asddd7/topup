<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name' => $this->game_name,

            'slug' => $this->slug ?? null,

            'image' => $this->image
                ? asset('storage/' . $this->image)
                : null,

            'description' => $this->description ?? null,
        ];
    }
}