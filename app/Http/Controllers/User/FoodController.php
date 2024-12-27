<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\FoodResource;
use App\Http\Resources\FoodSummaryResource;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Food;
use Illuminate\Http\Request;

class FoodController extends Controller
{
    use ApiResponseTrait;

    public function foodDetails($id)
    {
        $food = Food::WithGeneralDiscounts()// this is Local query scope in food model
        ->withAverageRating()// this is Local query scope in food model
        ->find($id);

        $food = $this->checkIfFoodHasDiscountAndGetPriceAfterDiscounts($food);

        $foodResource = new FoodResource($food);
        return $this->retrievedResponse($foodResource, 'Food details retrieved successfully');
    }

    public function foodDiscount()
    {
        $foods = Food::FoodGeneralDiscounts() // this is Local query scope in food model
        ->withAverageRating() // this is Local query scope in food model
        ->paginate(10);

        $foods->transform(function ($food) {
            return $this->checkIfFoodHasDiscountAndGetPriceAfterDiscounts($food);
        });

        return FoodSummaryResource::collection($foods);
    }

    public function foodFilter(Request $request)
    {
        $query = Food::query()->WithGeneralDiscounts(); // this is Local query scope in food model

        if ($request->has('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        if ($request->has('min_price') && $request->has('max_price')) {
            $query->whereBetween('price', [$request->get('min_price'), $request->get('max_price')]);
        }

        $foods = $query->withAverageRating()->paginate(10);

        $foods->transform(function ($food) {
            return $this->checkIfFoodHasDiscountAndGetPriceAfterDiscounts($food);
        });

        return FoodSummaryResource::collection($foods);
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
