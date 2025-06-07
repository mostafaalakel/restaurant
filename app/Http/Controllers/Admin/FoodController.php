<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\FoodResource;
use App\Http\Traits\ApiResponseTrait;
use App\Services\Admin\FoodService;
use Illuminate\Http\Request;

class FoodController extends Controller
{
    use ApiResponseTrait;

    protected $foodService;

    public function __construct(FoodService $foodService)
    {
        $this->foodService = $foodService;
    }

    public function addFood(Request $request)
    {
        $result = $this->foodService->createFood($request->all());

        return $result['status'] === 'error'
            ? $this->validationErrorResponse($result['errors'])
            : $this->createdResponse(null, $result['message']);
    }

    public function updateFood(Request $request, $foodId)
    {
        $result = $this->foodService->updateFood($foodId, $request->all());

        return $result['status'] === 'error'
            ? ($result['errors'] ?? $this->notFoundResponse($result['message']))
            : $this->updatedResponse(null, $result['message']);
    }

    public function deleteFood($foodId)
    {
        $result = $this->foodService->deleteFood($foodId);

        return $result['status'] === 'error'
            ? $this->notFoundResponse($result['message'])
            : $this->deletedResponse($result['message']);
    }

    public function showFoods()
    {
        $result = $this->foodService->getAllFoods();

        return $result['status'] === 'error'
            ? $this->notFoundResponse($result['message'])
            : FoodResource::collection($result['data']);
    }

    public function showFoodsTranslated()
    {
        $result = $this->foodService->showFoodsTranslated();

        return $result['status'] === 'error'
            ? $this->notFoundResponse($result['message'])
            : FoodResource::collection($result['data']);
    }
}
