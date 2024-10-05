<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\FoodResource;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Food;
use Illuminate\Http\Request;

class FoodController extends Controller
{
    use ApiResponseTrait;
    public function foodDetails($id)
    {
        $food = Food::find($id);

        if (!$food) {
            return $this->notFoundResponse('Food not found');
        }

        $foodResource = new FoodResource($food);
        return $this->retrievedResponse($foodResource, 'Food details retrieved successfully');
    }
    
}
