<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'invoice' => $this->invoice_number,

            'status' => $this->status,

            'game' => new GameResource(
                $this->whenLoaded('game')
            ),

            'player' => [
                'uid' => $this->player_uid,
                'server_id' => $this->server_id,
                'nickname' => $this->nickname,
            ],

            'payment' => new PaymentResource(
                $this->whenLoaded('payment')
            ),

            'items' => OrderDetailResource::collection(
                $this->whenLoaded('details')
            ),

            'subtotal' => (float) $this->subtotal,

            'discount' => (float) $this->discount,

            'total_price' => (float) $this->total_price,

            'payment_proof' => $this->payment_proof
                ? asset('storage/' . $this->payment_proof)
                : null,

            'notes' => $this->notes,

            'created_at' => $this->created_at?->toISOString(),

            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}