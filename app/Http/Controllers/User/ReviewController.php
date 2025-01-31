<?php

namespace App\Http\Controllers\User;

use App\Services\User\ReviewService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;

class ReviewController extends Controller
{
    use ApiResponseTrait;

    protected $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    public function addReviews(Request $request)
    {
        $result = $this->reviewService->addReview($request);

        if ($result['status'] === 'error') {
            return $this->validationErrorResponse($result['errors']);
        }

        return $this->createdResponse(null, $result['message']);
    }
}
