<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\category;
use App\Services\User\CartService;
use App\Services\User\CategoryService;

class CategoryController extends Controller
{
    use ApiResponseTrait;

    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function showCategories()
    {
        $categories = $this->categoryService->getCategories();

        if (!$categories) {
            return $this->retrievedResponse(null,'we have not categories yet');
        }

        return $this->retrievedResponse($categories, 'Categories retrieved successfully');
    }
}
