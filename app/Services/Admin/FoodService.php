<?php

namespace App\Services\Admin;

use App\Models\Food;
use Illuminate\Support\Facades\Validator;

class FoodService
{
    public function validate($request, $foodId = null)
    {
        $rules = [
            'category_id' => 'required|exists:categories,id',
            'name.en' => "required|unique:foods,name->en,{$foodId},id",
            'name.ar' => "required|unique:foods,name->ar,{$foodId},id",
            'price' => 'required|numeric',
            'description.en' => 'required',
            'description.ar' => 'required',
            'stock' => 'required|numeric',
        ];

        if (!$foodId) {
            $rules['image'] = 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
        }

        return Validator::make($request, $rules);
    }

    public function createFood($request)
    {
        $validator = $this->validate($request);
        if ($validator->fails()) {
            return ['status' => 'error', 'errors' => $validator->errors()];
        }

        $request['image'] = $this->uploadImage($request['image']);

        Food::create($request);

        return ['status' => 'success', 'message' => 'Food added successfully'];
    }

    public function updateFood($foodId, $request)
    {
        $food = Food::find($foodId);
        if (!$food) {
            return ['status' => 'error', 'message' => 'Food not found'];
        }

        $validator = $this->validate($request, $foodId);
        if ($validator->fails()) {
            return ['status' => 'error', 'errors' => $validator->errors()];
        }

        if (isset($request['image'])) {
            $this->deleteImage($food->image);
            $request['image'] = $this->uploadImage($request['image']);
        }

        $food->update($request);

        return ['status' => 'success', 'message' => 'Food updated successfully'];
    }

    public function deleteFood($foodId)
    {
        $food = Food::find($foodId);
        if (!$food) {
            return ['status' => 'error', 'message' => 'Food not found'];
        }

        $this->deleteImage($food->image);
        $food->delete();

        return ['status' => 'success', 'message' => 'Food deleted successfully'];
    }

    private function uploadImage($image)
    {
        $imageName = time() . '_' . $image->getClientOriginalName();
        $image->move(public_path('upload/food_images'), $imageName);
        return $imageName;
    }

    private function deleteImage($imageName)
    {
        $imagePath = public_path('upload/food_images/' . $imageName);
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    public function getAllFoods()
    {
        $foods = Food::withAverageRating()->WithGeneralDiscounts()->paginate(10);

        if ($foods->isEmpty()) {
            return ['status' => 'error', 'message' => 'No foods available'];
        }

        $foods->through(function ($food) {
            return $this->applyDiscount($food);
        });

        return ['status' => 'success', 'data' => $foods];
    }

    private function applyDiscount($food)
    {
        if ($food->generalDiscounts->isNotEmpty()) {
            $food->setAttribute('price_after_discounts', number_format($food->calculate_price_after_discounts, 2));
        }
        return $food;
    }
}
