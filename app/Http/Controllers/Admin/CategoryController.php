<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    use ApiResponseTrait;
    public function addCategory(Request $request)
    {
        $rules = [
            'name.en' => 'required',
            'name.ar' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        $validator->after(function ($validator) use ($request) {
            if (Category::where('name->en', $request->input('name.en'))->exists()) {
                $validator->errors()->add('name.en', 'The English name must be unique.');
            }

            if (Category::where('name->ar', $request->input('name.ar'))->exists()) {
                $validator->errors()->add('name.ar', 'The Arabic name must be unique.');
            }
        });

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }


        $category = Category::create(['name' => $request->input('name')]);

        if ($category) {
            return $this->createdResponse(null, 'Category added successfully');
        } else {
            return $this->serverErrorResponse('Category could not be added, there was an error');
        }
    }

    public function updateCategory(Request $request, $categoryId)
    {
        $category = Category::findOrFail($categoryId);
        $rules = [
            'name.en' => 'required',
            'name.ar' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);

        $validator->after(function ($validator) use ($request) {
            if (Category::where('name->en', $request->input('name.en'))->exists()) {
                $validator->errors()->add('name.en', 'The English name must be unique.');
            }

            if (Category::where('name->ar', $request->input('name.ar'))->exists()) {
                $validator->errors()->add('name.ar', 'The Arabic name must be unique.');
            }
        });

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }


        $category->update(['name' => $request->input('name')]);

        return $this->updatedResponse(null, 'Category updated successfully');
    }

    public function deleteCategory($categoryId)
    {
        $category = Category::findOrFail($categoryId);
        $category->delete();

        return $this->deletedResponse('Category deleted successfully');
    }

    public function showCategories()
    {
        $categories = Category::select('id', 'name')->get()->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->getTranslation('name', App::getLocale())
            ];
        });

        if ($categories->isEmpty()) {
            return $this->notFoundResponse('You have no categories yet');
        }

        return $this->retrievedResponse($categories, 'Categories retrieved successfully');
    }

}
