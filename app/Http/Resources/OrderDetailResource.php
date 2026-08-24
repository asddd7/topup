<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'item' => new ItemResource(
                $this->whenLoaded('item')
            ),

            'qty' => $this->qty,

            'price' => (float) $this->price,

            'subtotal' => (float) $this->subtotal,
        ];
    }
}