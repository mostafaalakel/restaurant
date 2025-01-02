<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'order_item_id'=> $this->id ,
            'quantity'=> $this->quantity ,
            'food' => [
                'name' => $this->food->name,
                'image_url' => asset(public_path('upload/food_images/'. $this->food->image)),
            ],
            'price' => $this->price ." $",
            'price_after_discounts' => $this->price_after_discounts ." $",
        ];
    }
}
