<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Services\Admin\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiResponseTrait;

    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function addCategory(Request $request)
    {
        $response = $this->categoryService->addCategory($request);

        return $response['status'] === 'error'
            ? $this->validationErrorResponse($response['errors'])
            : $this->createdResponse(null, $response['message']);
    }

    public function updateCategory(Request $request, $categoryId)
    {
        $response = $this->categoryService->updateCategory($request, $categoryId);

        return $response['status'] === 'error'
            ? ($response['message'] == 'Category not found'
                ? $this->notFoundResponse($response['message'])
                : $this->validationErrorResponse($response['errors']))
            : $this->updatedResponse(null, $response['message']);
    }

    public function deleteCategory($categoryId)
    {
        $response = $this->categoryService->deleteCategory($categoryId);

        return $response['status'] === 'error'
            ? $this->notFoundResponse($response['message'])
            : $this->deletedResponse($response['message']);
    }

    public function showCategories()
    {
        $result = $this->categoryService->showCategories();

        if ($result['status'] == 'error') {
            return $this->notFoundResponse($result['message']);
        }

        return $this->retrievedResponse($result['data'], $result['message']);
    }
}
