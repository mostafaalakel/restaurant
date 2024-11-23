<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FoodSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'food_id' => $this->id,
            'food_name' => $this->getTranslation('name', app()->getLocale()),
            'image_url' => asset(public_path('upload/food_images/' . $this->image)),
            'price' => $this->price . "$",
            'average_rating' => $this->average_rating,
        ];
        if ($this->generalDiscounts->isNotEmpty()) {
            $data['price_after_discounts'] = $this->price_after_discounts . "$";
            $data['discounts'] = $this->generalDiscounts->map(function ($discount) {
                return [
                    'discount_id' => $discount->id,
                    'discount_value' => $discount->value ."%",
                ];
            });
        }
        return $data;
    }
}
