<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\FoodResource;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Food;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FoodController extends Controller
{
    use ApiResponseTrait;

    public function AddFood(Request $request)
    {
        $rules = [
            'category_id' => 'required|exists:categories,id',
            'name.en' => 'required',
            'name.ar' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'price' => 'required|numeric',
            'description.en' => 'required',
            'description.ar' => 'required',
            'stock' => 'required|numeric',
        ];


        $validate = Validator::make($request->all(), $rules);
        $validate->after(function ($validate) use ($request) {

            if (Food::where('name->en', $request->input('name.en'))->exists()) {
                $validate->errors()->add('name.en', 'The English name food must be unique.');
            }

            if (Food::where('name->ar', $request->input('name.ar'))->exists()) {
                $validate->errors()->add('name.ar', 'The Arabic name food must be unique.');
            }
        });

        if ($validate->fails()) {
            return $this->validationErrorResponse($validate->errors());
        }


        $image = $request->file('image');
        $image_name = date('YmdHis') . $image->getClientOriginalName();
        $image->move(public_path('upload/food_images'), $image_name);

        $food = Food::create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'image' => $image_name,
            'price' => $request->price,
            'description' => $request->description,
            'stock' => $request->stock
        ]);

        if ($food) {
            return $this->createdResponse(null, 'food added successfully');
        } else {
            return $this->serverErrorResponse('Food could not be added due to a server error');
        }
    }

    public function deleteFood($id)
    {
        $food = Food::findOrFail($id);
        $image_path = public_path('upload/food_images/' . $food->image);

        if (file_exists($image_path)) {
            unlink($image_path);
        }

        $food->delete();
        return $this->deletedResponse('Food deleted successfully');
    }

    public function updateFood(Request $request, $foodId)
    {
        $food = Food::findOrFail($foodId);

        $rules = [
            'category_id' => 'required|exists:categories,id',
            'name.en' => 'required',
            'name.ar' => 'required',
            'price' => 'required|numeric',
            'description.en' => 'required',
            'description.ar' => 'required',
            'stock' => 'required|numeric',
        ];


        $validate = Validator::make($request->all(), $rules);


        if ($validate->fails()) {
            return $this->validationErrorResponse($validate->errors());
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $image_name = date('YmdHis') . $image->getClientOriginalName();
            $image->move(public_path('upload/food_images'), $image_name);

            if (file_exists(public_path('upload/food_images/' . $food->image))) {
                unlink(public_path('upload/food_images/' . $food->image));
            }

            $food->update(['image' => $image_name]);
        }

        $food->update([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'stock' => $request->stock
        ]);
        return $this->updatedResponse(null, 'food updated successfully');
    }

    public function showFoods()
    {
        $foods = Food::withAverageRating()->WithGeneralDiscounts()->paginate(10);

        if (!$foods->isEmpty()) {
            $foods->transform(function ($food) {
                return $this->checkIfFoodHasDiscountAndGetPriceAfterDiscounts($food);
            });
            return FoodResource::collection($foods);
        }
        return $this->notFoundResponse('No foods available');
    }

    public function checkIfFoodHasDiscountAndGetPriceAfterDiscounts($food)
    {
        if ($food->generalDiscounts->isNotEmpty()) {
            $price_after_discounts = $food->calculate_price_after_discounts; // this is accessor to calculate_price_after_discounts in product model
            $food->setAttribute('price_after_discounts', number_format($price_after_discounts, 2));
        }
        return $food;
    }
}
