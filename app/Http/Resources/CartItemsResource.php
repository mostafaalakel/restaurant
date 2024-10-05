<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id ,
            'food' => [
                'id' => $this->food->id,
                'name' => $this->food->name,
                'price' => $this->food->price."$",
                'image' => $this->food->image,
            ],
            'quantity' => $this->quantity
        ];
    }
}
