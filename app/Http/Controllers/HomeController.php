<?php

namespace App\Http\Controllers;

use App\Http\Resources\FoodResource;
use App\Http\Traits\ApiResponseTrait;
use App\Models\category;
use App\Models\Food;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    use ApiResponseTrait;
    public function index()
    {

        $categories = Category::with(['foods' => function ($query) {
            $query->orderBy('id', 'desc')->take(4);
        }])->get();

        $HomeFoods = [];

        foreach ($categories as $category) {
            $HomeFoods[$category->name] = FoodResource::collection($category->foods);
        }

        return $this->retrievedResponse($HomeFoods , 'Most popular foods were retrieved successfully.');
    }
}
