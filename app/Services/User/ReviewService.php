<?php

namespace App\Services\User;

use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReviewService
{
    public function addReview($request)
    {
        $rules = [
            'food_id' => 'required|exists:foods,id',
            'rating' => 'required|integer|min:1|max:5'
        ];

        $validate = Validator::make($request->all(), $rules);
        if ($validate->fails()) {
            return [
                'status' => 'error',
                'errors' => $validate->errors()
            ];
        }

        Review::updateOrCreate(
            ['user_id' => Auth::guard('user')->id(), 'food_id' => $request->food_id],
            ['rating' => $request->rating]
        );

        return [
            'status' => 'success',
            'message' => 'Review added successfully'
        ];
    }

}
