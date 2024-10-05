<?php

namespace App\Http\Traits\baseTrait;

use Illuminate\Http\Request;
use App\Models\category;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Validator;

trait CategoryTrait
{
    use ApiResponseTrait;
    public function addCategory(Request $request)
    {
        $rules = ['name' => 'required|unique:categories'];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $category = Category::create(['name' => $request->name]);

        if ($category) {
            return $this->createdResponse(null, 'Category added successfully');
        } else {
            return $this->serverErrorResponse('Category could not be added, there was an error');
        }
    }

    public function updateCategory(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $rules = ['name' => 'required'];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $category->update(['name' => $request->name]);

        return $this->updatedResponse(null, 'Category updated successfully');
    }

    public function deleteCategory($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return $this->deletedResponse('Category deleted successfully');
    }

    public function showCategories()
    {
        $categories = Category::select('id', 'name')->get();

        if ($categories->isEmpty()) {
            return $this->notFoundResponse('You have no categories yet');
        }

        return $this->retrievedResponse($categories, 'Categories retrieved successfully');
    }
}
