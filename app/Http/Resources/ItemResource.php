<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,

            'name' => $this->item_name,

            'qty' => $this->qty,

            'price' => (float) $this->price,

            'formatted_price' => 'Rp ' . number_format(
                $this->price,
                0,
                ',',
                '.'
            ),

            'stock' => $this->stock,

            'description' => $this->description,

            'image' => $this->image
                ? asset('storage/' . $this->image)
                : null,

            'is_active' => (bool) $this->is_active,

            'top_seller' => (bool) $this->top_seller,

            'category' => new CategoryResource(
                $this->whenLoaded('category')
            ),
        ];

        /*
        |--------------------------------------------------------------------------
        | Game
        |--------------------------------------------------------------------------
        |
        | Hanya tampil jika relasi game sudah di-load.
        |
        */

        if ($this->relationLoaded('game') && $this->game) {
            $data['game'] = [
                'id' => $this->game->id,

                'name' => $this->game->game_name,

                'slug' => $this->game->slug ?? null,

                'image' => $this->game->image
                    ? asset('storage/' . $this->game->image)
                    : null,
            ];
        }

        return $data;
    }
}