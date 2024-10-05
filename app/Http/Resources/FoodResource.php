<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FoodResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->image,  
            'image_url' => asset(public_path('upload/food_images/'. $this->image)),
            'description' => $this->description,
            'price' => $this->price ,
            'average_rating' => $this->average_rating,
            'reviews' => ReviewResource::collection($this->reviews) 
        ];
    }
}
