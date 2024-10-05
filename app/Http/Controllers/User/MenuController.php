<?php

namespace App\Http\Controllers\User;

use App\Http\Traits\ApiResponseTrait;
use App\Models\Food;
use App\Models\category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\FoodResource;

class MenuController extends Controller
{
    use ApiResponseTrait;
    public function showCategories()
    {
        $categories = Category::select('id', 'name')->get();

        if ($categories->isEmpty()) {
            return $this->notFoundResponse('You have no categories yet');
        }

        return $this->retrievedResponse($categories, 'Categories retrieved successfully');
    }

    public function showFoodOfCategory($category_id)
    {
        $foods =  FoodResource::collection(Food::where('category_id', $category_id)->get());
        return $this->retrievedResponse($foods, 'foods retrieved successfully');
    }
}

