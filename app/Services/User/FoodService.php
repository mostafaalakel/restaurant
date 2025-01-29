<?php

namespace App\Services\User;

use App\Models\Food;
use Illuminate\Http\Request;

class FoodService
{
    public function getFoodDetails($foodId)
    {
        $food = Food::WithGeneralDiscounts()// this is Local query scope in food model
        ->withAverageRating()// this is Local query scope in food model
        ->find($foodId);

        return $this->applyDiscountIfAvailable($food);
    }

    public function getFoodDiscounts()
    {
        $foods = Food::FoodGeneralDiscounts()// this is Local query scope in food model
        ->withAverageRating()// this is Local query scope in food model
        ->paginate(10);

        return $foods->through(function ($food) {
            return $this->applyDiscountIfAvailable($food);
        });
    }

    public function filterFoods(Request $request)
    {
        $query = Food::query();

        if ($request->has('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        if ($request->has('min_price') && $request->has('max_price')) {
            $query->whereBetween('price', [$request->get('min_price'), $request->get('max_price')]);
        }

        $foods = $query->WithGeneralDiscounts()->withAverageRating()->paginate(10);

        return $foods->through(function ($food) {
            return $this->applyDiscountIfAvailable($food);
        });
    }

    public function getFoodOfCategory($category_id)
    {
        $foods = Food::where('category_id', $category_id)
            ->WithGeneralDiscounts()// this is Local query scope in food model
            ->withAverageRating()// this is Local query scope in food model
            ->paginate(10);

        return $foods->through(function ($food) {
            return $this->applyDiscountIfAvailable($food);
        });
    }

    private function applyDiscountIfAvailable($food)
    {
        if ($food->generalDiscounts->isNotEmpty()) {
            $price_after_discounts = $food->calculate_price_after_discounts;  // this is accessor to calculate_price_after_discounts in product model
            $food->setAttribute('price_after_discounts', number_format($price_after_discounts, 2));
        }
        return $food;
    }
}

