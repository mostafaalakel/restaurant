<?php

namespace App\Services\Admin;

use App\Models\Category;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class CategoryService
{
    protected array $rules = [
        'name.en' => 'required',
        'name.ar' => 'required',
    ];

    public function addCategory($request)
    {
        $validator = Validator::make($request->all(), $this->rules);

        $validator->after(function ($validator) use ($request) {
            if (Category::where('name->en', $request->input('name.en'))->exists()) {
                $validator->errors()->add('name.en', 'The English name must be unique.');
            }

            if (Category::where('name->ar', $request->input('name.ar'))->exists()) {
                $validator->errors()->add('name.ar', 'The Arabic name must be unique.');
            }
        });

        if ($validator->fails()) {
            return ['status' => 'error', 'errors' => $validator->errors()];
        }

        $category = Category::create(['name' => $request->input('name')]);

        return $category
            ? ['status' => 'success', 'message' => 'Category added successfully']
            : ['status' => 'error', 'message' => 'Category could not be added, there was an error'];
    }

    public function updateCategory($request, $categoryId)
    {
        $category = Category::find($categoryId);

        if (!$category) {
            return ['status' => 'error', 'message' => 'Category not found'];
        }

        $validator = Validator::make($request->all(), $this->rules);

        $validator->after(function ($validator) use ($request, $categoryId) {
            if (Category::where('id', '!=', $categoryId)->where('name->en', $request->input('name.en'))->exists()) {
                $validator->errors()->add('name.en', 'The English name must be unique.');
            }

            if (Category::where('id', '!=', $categoryId)->where('name->ar', $request->input('name.ar'))->exists()) {
                $validator->errors()->add('name.ar', 'The Arabic name must be unique.');
            }
        });

        if ($validator->fails()) {
            return [
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ];
        }

        $category->update(['name' => $request->input('name')]);

        return ['status' => 'success', 'message' => 'Category updated successfully'];
    }

    public function deleteCategory($categoryId)
    {
        $category = Category::find($categoryId);

        if (!$category) {
            return ['status' => 'error', 'message' => 'Category not found'];
        }

        $category->delete();

        return ['status' => 'success', 'message' => 'Category deleted successfully'];
    }

    public function showCategories()
    {
        $categories = Category::select('id', 'name')->get()->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->getTranslation('name', App::getLocale()),
            ];
        });

        if ($categories->isEmpty()) {
            return ['status' => 'error', 'message' => 'You have no categories yet'];
        }

        return ['status' => 'success', 'data' => $categories, 'message' => 'Categories retrieved successfully'];
    }
}
