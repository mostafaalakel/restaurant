<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'order_id' => $this->id,
            'name' => $this->user->name,
            'phone_number' => $this->phone_number,
            'total_price' => $this->total_price."$",
            'payment_status' => $this->payment_status,
            'order_status' => $this->order_status
        ];
    }
}
