<?php

namespace App\Http\Traits\baseTrait;

use App\Http\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use App\Models\Food;
use App\Http\Resources\FoodResource;
use Illuminate\Support\Facades\Validator;

trait FoodTrait
{
    use ApiResponseTrait;

    public function AddFood(Request $request)
    {
        $rules = [
            'category_id' => 'required',
            'name' => 'required',
            'image' => 'required',
            'price' => 'required|numeric',
            'description' => 'required'
        ];

        $validate = Validator::make($request->all(), $rules);
        if ($validate->fails()) {
            return $this->validationErrorResponse($validate->errors());
        }

        $image = $request->file('image');
        $image_name = date('YmdHi') . $image->getClientOriginalName();
        $image->move(public_path('upload/food_images'), $image_name);

        $food = Food::create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'image' => $image_name,
            'price' => $request->price,
            'description' => $request->description
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

    public function updateFood(Request $request, $id)
    {
        $food = Food::findOrFail($id);

        $rules = [
            'category_id' => 'required',
            'name' => 'required',
            'image' => 'nullable|image',
            'price' => 'required|numeric',
            'description' => 'required'
        ];
        $validate = Validator::make($request->all(), $rules);
        if ($validate->fails()) {
            return $this->validationErrorResponse($validate->errors());
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $image_name = date('YmdHi') . $image->getClientOriginalName();
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
            'description' => $request->description
        ]);
        return $this->updatedResponse(null, 'food updated successfully');
    }

    public function showFoods()
    {
        $foods = Food::all();
        if (!$foods->isEmpty()) {
            return $this->retrievedResponse(FoodResource::collection($foods), 'food retrieved successfully');
        }
        return $this->notFoundResponse('No foods available');
    }
}
