<?php

namespace App\Services\User;

use App\Models\category;

class CategoryService
{
  public function getCategories()
  {
      $categories = Category::select('id', 'name' , 'image')->get()->map(function ($category) {
          return [
              'category_id' => $category->id,
              'name' => $category->getTranslation('name', app()->getLocale()) ,
              'image_url' => url('upload/category_images/' . $category->image),
          ];
      });

      if ($categories->isEmpty()) {
          return null;
      }
       return $categories;
  }
}
