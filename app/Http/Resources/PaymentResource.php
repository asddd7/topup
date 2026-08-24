<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name' => $this->payment_name,

            'number' => $this->payment_number,

            'account_name' => $this->account_name,

            'type' => $this->payment_type,

            'image' => $this->image
                ? asset('storage/' . $this->image)
                : null,

            'is_active' => (bool) $this->is_active,
        ];
    }
}