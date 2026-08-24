<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name' => $this->category_name,

            'use_qty' => (bool) $this->use_qty,

            'items_count' => $this->when(
                isset($this->items_count),
                $this->items_count
            ),

            'items' => ItemResource::collection(
                $this->whenLoaded('items')
            ),
        ];
    }
}