<?php

namespace App\Http\Controllers;

use App\Http\Resources\FoodSummaryResource;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Food;
use App\Services\User\FoodService;

class HomeController extends Controller
{
    use ApiResponseTrait;

    protected $foodService;

    public function __construct(FoodService $foodService)
    {
        $this->foodService = $foodService;
    }

    public function index()
    {
        // sorting data depends on rating and discounts value (FoodGeneralDiscounts and withAverageRating are query scope in food model)
        $foods = Food::FoodGeneralDiscounts()->withAverageRating()->get();
        $foods->transform(function ($food) {
            return $this->foodService->applyDiscountIfAvailable($food);
        });
        return $this->retrievedResponse(FoodSummaryResource::collection($foods), 'Food discount retrieved successfully');
    }
}
