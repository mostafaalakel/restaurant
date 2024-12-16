<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\DiscountResource;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\GeneralDiscount;
use App\Models\CodeDiscount;
use App\Models\Food;

class DiscountController extends Controller
{
    use ApiResponseTrait;

    public function getAllGeneralDiscounts()
    {
        $generalDiscounts = GeneralDiscount::paginate(10);
        return DiscountResource::collection($generalDiscounts);
    }

    public function getAllCodeDiscounts()
    {
        $codeDiscounts = CodeDiscount::paginate(10);
        $codeDiscounts->getCollection()->transform(function ($codeDiscount) {
            $codeDiscount->value = $codeDiscount->value . " %";
            return $codeDiscount;
        });

        return $this->retrievedResponse($codeDiscounts);
    }


    public function storeGeneralDiscount(Request $request)
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
            return $this->validationErrorResponse($validator->errors());
        }

        GeneralDiscount::create([
            'name' => $request->name,
            'value' => $request->value,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => $request->is_active,
        ]);

        return $this->createdResponse(null, 'generalDiscount created successfully');
    }

    public function storeCodeDiscount(Request $request)
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
            return $this->validationErrorResponse($validator->errors());
        }

        CodeDiscount::create([
            'name' => $request->name,
            'code' => $request->code,
            'value' => $request->value,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => $request->is_active,
        ]);

        return $this->createdResponse(null, 'CodeDiscount created successfully');
    }

    public function updateGeneralDiscount(Request $request, $generalDiscountId)
    {
        $rules = [
            'name' => 'nullable|array',
            'value' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_active' => 'boolean',
        ];
        $discount = GeneralDiscount::findOrFail($generalDiscountId);

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $discount->update($request->all());

        return $this->updatedResponse(null, 'General discount updated successfully');
    }

    public function updateCodeDiscount(Request $request, $CodeDiscountId)
    {
        $discount = CodeDiscount::findOrFail($CodeDiscountId);

        $rules = [
            'name' => 'nullable',
            'code' => 'nullable|string|unique:code_discounts,code,' . $CodeDiscountId,
            'value' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_active' => 'boolean'
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $discount->update($request->all());

        return $this->updatedResponse(null, 'Code discount updated successfully');
    }

    public function deleteGeneralDiscount($generalDiscountId)
    {
        $discount = GeneralDiscount::findOrFail($generalDiscountId);
        $discount->delete();

        return $this->deletedResponse('General discount deleted successfully');
    }

    public function deleteCodeDiscount($CodeDiscountId)
    {
        $discount = CodeDiscount::findOrFail($CodeDiscountId);
        $discount->delete();

        return $this->deletedResponse('Code discount deleted successfully');
    }

    public function attachGeneralDiscountToFood(Request $request, $discountId)
    {
        $discount = GeneralDiscount::findOrFail($discountId);

        $validator = Validator::make($request->all(), [
            'food_ids' => 'required|array',
            'food_ids.*' => 'exists:foods,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $discount->foods()->syncWithoutDetaching($request->food_ids);

        return $this->createdResponse(null, 'Foods attached to general discount successfully');
    }

    public function attachCodeDiscountToFood(Request $request, $discountId)
    {
        $discount = CodeDiscount::findOrFail($discountId);

        $validator = Validator::make($request->all(), [
            'food_ids' => 'required|array',
            'food_ids.*' => 'exists:foods,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $discount->foods()->syncWithoutDetaching($request->food_ids);

        return $this->createdResponse(null, 'Foods attached to Code discount successfully');

    }

    public function detachFoodFromGeneralDiscount(Request $request, $generalDiscountId)
    {
        $validator = Validator::make($request->all(), [
            'food_ids' => 'required|array',
            'food_ids.*' => 'exists:foods,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $generalDiscount = GeneralDiscount::findOrFail($generalDiscountId);
        $generalDiscount->foods()->detach($request->food_ids);

        return $this->deletedResponse('Food successfully detached from the general discount');
    }

    public function detachFoodFromCodeDiscount(Request $request, $CodeDiscountId)
    {
        $validator = Validator::make($request->all(), [
            'food_ids' => 'required|array',
            'food_ids.*' => 'exists:foods,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $codeDiscount = CodeDiscount::findOrFail($CodeDiscountId);
        $codeDiscount->foods()->detach($request->food_ids);
        return $this->deletedResponse('Food successfully detached from the code discount');

    }

    public function showFoodByGeneralDiscount($generalDiscountId)
    {
        $foods = GeneralDiscount::find($generalDiscountId)->foods->map(function ($food) {
            return [
                'food_id' => $food->id,
                'food_name' => $food->getTranslation('name', app()->getLocale()),
                'image_url' => asset(public_path('upload/food_images/' . $food->image)),
                'price' => $food->price . " $",
                'stock' => $food->stock,
            ];
        });

        return $this->retrievedResponse($foods);

    }
    public function showFoodByCodeDiscount($codeDiscountId)
    {
        $foods = GeneralDiscount::find($codeDiscountId)->foods->map(function ($food) {
            return [
                'food_id' => $food->id,
                'food_name' => $food->getTranslation('name', app()->getLocale()),
                'image_url' => asset(public_path('upload/food_images/' . $food->image)),
                'price' => $food->price . " $",
                'stock' => $food->stock,
            ];
        });

        return $this->retrievedResponse($foods);

    }
}
