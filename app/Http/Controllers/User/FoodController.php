<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\FoodResource;
use App\Http\Resources\FoodSummaryResource;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Food;
use App\Services\User\FoodService;
use Illuminate\Http\Request;

class FoodController extends Controller
{
    use ApiResponseTrait;

    protected $foodService;

    public function __construct(FoodService $foodService)
    {
        $this->foodService = $foodService;
    }

    public function foodDetails($foodId)
    {
        $food = $this->foodService->getFoodDetails($foodId);
        return $this->retrievedResponse(new FoodResource($food), 'Food details retrieved successfully');
    }


    public function foodDiscount()
    {
        $foods = $this->foodService->getFoodDiscounts();

        return FoodSummaryResource::collection($foods);
    }

    public function foodFilter(Request $request)
    {
        $foods = $this->foodService->filterFoods($request);
        return FoodSummaryResource::collection($foods);
    }


    public function showFoodOfCategory($category_id)
    {
        $foods = $this->foodService->getFoodOfCategory($category_id);

        return FoodSummaryResource::collection($foods);
    }
}
