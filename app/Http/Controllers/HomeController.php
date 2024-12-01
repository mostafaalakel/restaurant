<?php

namespace App\Http\Controllers;

use App\Http\Controllers\User\FoodController;
use App\Http\Resources\FoodResource;
use App\Http\Resources\FoodSummaryResource;
use App\Http\Traits\ApiResponseTrait;
use App\Models\category;
use App\Models\Food;
use Illuminate\Http\Request;

class HomeController extends FoodController
{
    use ApiResponseTrait;

    public function index()
    {
        // sorting data depends on rating and discounts value (FoodGeneralDiscounts and withAverageRating are query scope in food model)
        $foods = Food::FoodGeneralDiscounts()->withAverageRating()->get();

        $foods->transform(function ($food) {
            return $this->checkIfFoodHasDiscountAndGetPriceAfterDiscounts($food);
        });

        return $this->retrievedResponse(FoodSummaryResource::collection($foods), 'Food discount retrieved successfully');


    }
}
