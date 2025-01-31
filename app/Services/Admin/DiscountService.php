<?php

namespace App\Services\Admin;

use App\Models\GeneralDiscount;
use App\Models\CodeDiscount;
use Illuminate\Support\Facades\Validator;

class DiscountService
{
    public function getAllGeneralDiscounts()
    {
        return GeneralDiscount::all();
    }

    public function getAllCodeDiscounts()
    {
        return CodeDiscount::all();
    }

    public function storeGeneralDiscount($request)
    {
        $rules = [
            'name.en' => 'required',
            'name.ar' => 'required',
            'value' => 'required|numeric|min:0',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['status' => 'error', 'message' => $validator->errors()];
        }

        return GeneralDiscount::create([
            'name' => $request->name,
            'value' => $request->value,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => $request->is_active,
        ]);
    }

    public function storeCodeDiscount($request)
    {
        $rules = [
            'name' => 'required',
            'code' => 'required|unique:code_discounts,code',
            'value' => 'required|numeric|min:0',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['status' => 'error', 'message' => $validator->errors()];
        }

        return CodeDiscount::create([
            'name' => $request->name,
            'code' => $request->code,
            'value' => $request->value,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => $request->is_active,
        ]);
    }

    public function updateDiscount($discountId, $isCodeDiscount, $request)
    {
        $discount = $isCodeDiscount ? CodeDiscount::find($discountId) : GeneralDiscount::find($discountId);

        if (!$discount) {
            return ['status' => 'error', 'message' => 'Discount not found'];
        }

        $rules = [
            'value' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_active' => 'boolean',
        ];

        if ($isCodeDiscount) {
            $rules['name'] = 'nullable|string';
            $rules['code'] = 'nullable|string|unique:code_discounts,code,' . $discountId;
        } else {
            $rules['name'] = 'nullable|array';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['status' => 'error', 'message' => 'Validation error', 'errors' => $validator->errors()];
        }

        $discount->update($request->all());

        return ['status' => 'success', 'message' => 'Discount updated successfully'];
    }

    public function deleteDiscount($discountId, $isCodeDiscount)
    {
        $discount = $isCodeDiscount ? CodeDiscount::find($discountId) : GeneralDiscount::find($discountId);

        if (!$discount) {
            return ['status' => 'error', 'message' => 'Discount not found'];
        }

        $discount->delete();

        return ['status' => 'success', 'message' => 'Discount deleted successfully'];
    }

    public function attachDiscountToFood($discountId, $isCodeDiscount, $foodIds)
    {
        $discount = $isCodeDiscount ? CodeDiscount::find($discountId) : GeneralDiscount::find($discountId);

        if (!$discount) {
            return ['status' => 'error', 'message' => 'Discount not found'];
        }

        $validator = Validator::make(
            ['food_ids' => $foodIds],
            ['food_ids' => 'required|array', 'food_ids.*' => 'exists:foods,id']
        );

        if ($validator->fails()) {
            return ['status' => 'error', 'message' => 'Validation errors occurred', 'errors' => $validator->errors()];
        }


        $discount->foods()->syncWithoutDetaching($foodIds);

        return ['status' => 'success', 'message' => 'Foods attached to discount successfully'];
    }

    public function detachFoodFromDiscount($discountId, $isCodeDiscount, $foodIds)
    {
        $discount = $isCodeDiscount ? CodeDiscount::find($discountId) : GeneralDiscount::find($discountId);

        if (!$discount) {
            return ['status' => 'error', 'message' => 'Discount not found'];
        }

        $validator = Validator::make(
            ['food_ids' => $foodIds],
            ['food_ids' => 'required|array', 'food_ids.*' => 'exists:foods,id']
        );

        if ($validator->fails()) {
            return ['status' => 'error', 'message' => 'Validation errors occurred', 'errors' => $validator->errors()];
        }

        $discount->foods()->detach($foodIds);

        return ['status' => 'success', 'message' => 'Food successfully detached from discount'];
    }

    public function showFoodByDiscount($discountId, $isCodeDiscount)
    {
        $discount = $isCodeDiscount ? CodeDiscount::find($discountId) : GeneralDiscount::find($discountId);

        if (!$discount) {
            return ['status' => 'error', 'message' => 'Discount not found'];
        }

        if ($discount->foods->isEmpty()) {
            return ['status' => 'error', 'message' => 'No foods found for this discount'];
        }

        return [
            'status' => 'success',
            'data' => $discount->foods->map(function ($food) {
                return [
                    'food_id' => $food->id,
                    'food_name' => $food->getTranslation('name', app()->getLocale()),
                    'image_url' => asset('upload/food_images/' . $food->image),
                    'price' => $food->price . " $",
                    'stock' => $food->stock,
                ];
            })->toArray(),
        ];
    }

}
