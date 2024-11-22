<?php

namespace App\Http\Controllers\User;

use App\Http\Traits\ApiResponseTrait;
use App\Models\Food;
use App\Models\category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\FoodResource;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
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
            ->with(['generalDiscounts' => function ($query) {
                $query->where('start_date', '<=', now())
                    ->where('end_date', '>=', now())
                    ->where('is_active', '=', 1);
            }])
            ->withCount(['reviews as average_rating' => function ($query) {
                $query->select(DB::raw('coalesce(avg(rating), 0)'));
            }])
            ->orderByDesc('average_rating')
            ->get();

        $foods->transform(function ($food) {
            return $this->checkIfFoodHasDiscountAndGetPriceAfterDiscounts($food);
        });

        $foodResources = FoodResource::collection($foods);
        return $this->retrievedResponse($foodResources, 'foods retrieved successfully');
    }


    public function checkIfFoodHasDiscountAndGetPriceAfterDiscounts($food)
    {
        if ($food->generalDiscounts->isNotEmpty()) {
            $price_after_discounts = $food->calculate_price_after_discounts; // this is accessor to calculate_price_after_discounts in product model
            $food->setAttribute('price_after_discounts', $price_after_discounts);
        }
        return $food;
    }
}
