<?php

namespace App\Http\Controllers\User;

use App\Models\Review;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\ApiResponseTrait;
use App\Http\Resources\ReviewResource;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    use ApiResponseTrait;

    public function addReviews(Request $request)
    {
        $existingReview = Review::where('food_id', $request->food_id)
            ->where('user_id', Auth::guard('user')->id())
            ->first();

        if ($existingReview) {
            $existingReview->delete();
        }

        $rules = [
            'food_id' => 'required|exists:foods,id',
            'rating' => 'required|integer|min:1|max:5'
        ];

        $validate = Validator::make($request->all(), $rules);
        if ($validate->fails()) {
            return $this->validationErrorResponse($validate->errors());
        }

        Review::create([
            'user_id' => Auth::guard('user')->id(),
            'food_id' => $request->food_id,
            'rating' => $request->rating
        ]);

        return $this->createdResponse(null, 'Review added successfully');
    }


}
