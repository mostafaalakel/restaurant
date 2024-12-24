<?php

namespace App\Http\Resources;

use App\Models\CodeDiscount;
use App\Models\GeneralDiscount;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiscountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'discount_id' => $this->id,
            'discount_name' => $this instanceof GeneralDiscount
                ? $this->getTranslation('name', app()->getLocale())
                : $this->name,
            'value' => $this->value . " %",
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'is_active' => $this->is_active,
        ];

        if ($this->resource->getAttribute('code')) {
            $data['code'] = $this->code;
        }
        return $data;
    }

}
