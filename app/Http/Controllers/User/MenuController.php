<?php

namespace App\Http\Controllers\User;

use App\Http\Resources\FoodSummaryResource;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Food;
use App\Models\category;

class MenuController extends FoodController
{
    use ApiResponseTrait;

    public function showCategories()
    {
        $categories = Category::select('id', 'name')->get()->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->getTranslation('name', app()->getLocale())
            ];
        });

        if ($categories->isEmpty()) {
            return $this->notFoundResponse('we have not categories yet');
        }

        return $this->retrievedResponse($categories, 'Categories retrieved successfully');
    }

    public function showFoodOfCategory($category_id)
    {
        $foods = Food::where('category_id', $category_id)
            ->WithGeneralDiscounts() // this is Local query scope in food model
            ->withAverageRating() // this is Local query scope in food model
            ->get();

        $foods->transform(function ($food) {
            return $this->checkIfFoodHasDiscountAndGetPriceAfterDiscounts($food);
        });

        $foodResources = FoodSummaryResource::collection($foods);
        return $this->retrievedResponse($foodResources, 'foods retrieved successfully');
    }
}
