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
    // public function AddReviews(Request $request)
    // {
    //     $rules = [
    //         'food_id' => 'required|exists:foods,id',
    //         'rating' => 'required|integer|min:1|max:5',
    //         'comment' => 'nullable|string',
    //     ];
    //     $validate = Validator::make($request->all(), rules: $rules);
    //     if ($validate->fails()) {
    //         return response()->json([
    //             'status' => 'validation_error',
    //             'errors' => $validate->errors()
    //         ]);
    //     }
    //     $review = Review::create([
    //         'user_id' => Auth::id(),
    //         'food_id' => $request->food_id,
    //         'rating' => $request->rating,
    //         'comment' => $request->comment,
    //     ]);

    //     return $this->apiResponse(201, 'Review added successfully');
    // }

    // public function showReviews($food_id)
    // {
    //     $reviews = Review::where('food_id', $food_id)->with('user')->get();
    //     if (!$reviews->isEmpty()) {
    //         $reviews = ReviewResource::collection($reviews);
    //         return $this->apiResponse(200, 'Reviews are got it successfully', $reviews);
    //     } else
    //         return $this->apiResponse(200, 'we haven`t any reviews yet ', $reviews);
    // }


    public function addReviews(Request $request)
    {
        $rules = [
            'food_id' => 'required|exists:foods,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ];

        $validate = Validator::make($request->all(), $rules);
        if ($validate->fails()) {
            return $this->validationErrorResponse($validate->errors());
        }

        $review = Review::create([
            'user_id' => Auth::id(),
            'food_id' => $request->food_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return $this->createdResponse([], 'Review added successfully');
    }

    public function showReviews($food_id)
    {
        $reviews = Review::where('food_id', $food_id)->with('user')->get();

        if (!$reviews->isEmpty()) {
            $reviewsResource = ReviewResource::collection($reviews);
            return $this->retrievedResponse($reviewsResource, 'Reviews retrieved successfully');
        } else {
            return $this->apiResponse(200, 'No reviews found yet', []);
        }
    }
}
