<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "reservation_id" => $this->id ,
            "num_people" =>  $this->num_people,
           "reservation_time" =>  $this->reservation_time,
           "special_request" =>  $this-> special_request,
           "status" =>  $this->status
        ];
    }
}
