<?php

namespace App\Http\Controllers\User;

use App\Http\Traits\ApiResponseTrait;
use App\Models\category;

class CategoryController extends FoodController
{
    use ApiResponseTrait;

    public function showCategories()
    {
        $categories = Category::select('id', 'name')->get()->map(function ($category) {
            return [
                'category_id' => $category->id,
                'name' => $category->getTranslation('name', app()->getLocale())
            ];
        });

        if ($categories->isEmpty()) {
            return $this->notFoundResponse('we have not categories yet');
        }

        return $this->retrievedResponse($categories, 'Categories retrieved successfully');
    }
}
