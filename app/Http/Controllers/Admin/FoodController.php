<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\FoodResource;
use App\Http\Traits\ApiResponseTrait;
use App\Services\Admin\FoodService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

//    public function showFoods()
//    {
//        $result = $this->foodService->getAllFoods();
//
//        return $result['status'] === 'error'
//            ? $this->notFoundResponse($result['message'])
//            : FoodResource::collection($result['data']);
//    }

    public function showFoods()
    {
        $result = $this->foodService->getAllFoods();

        if ($result['status'] === 'error') {
            return $this->notFoundResponse($result['message']);
        }

        $foods = $result['data']->map(function ($food) {
            $data = [
                'food_id' => $food->id,
                'food_name' => $food->getTranslations('name'),
                'image_url' => url('upload/food_images/' . $food->image),
                'description' => $food->getTranslations('description'),
                'price' => $food->price . " $",
                'stock' => $food->stock,
                'average_rating' => $food->average_rating,
            ];

            if ($food->generalDiscounts->isNotEmpty()) {
                $data['price_after_discounts'] = $food->price_after_discounts . " $";
                $data['discounts'] = $food->generalDiscounts->map(function ($discount) {
                    return [
                        'discount_id' => $discount->id,
                        'discount_name' => $discount->getTranslations('name'),
                        'discount_value' => $discount->value . "%",
                        'start_date' => $discount->start_date,
                        'end_date' => $discount->end_date,
                    ];
                });
            }

            return $data;
        });

        return response()->json($foods);
    }

    public function showFoodsTranslated()
    {
        $result = $this->foodService->showFoodsTranslated();

        return $result['status'] === 'error'
            ? $this->notFoundResponse($result['message'])
            : FoodResource::collection($result['data']);
    }
}
