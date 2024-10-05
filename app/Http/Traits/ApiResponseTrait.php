<?php

namespace App\Http\Traits;

trait ApiResponseTrait
{
   
    public function apiResponse($status = null, $message = null, $data = null, $code = 200)
    {
        $array = [
            'status' => $status,
            'message' => $message,
            'data' => $data ,
            'status code' => $code 
        ];

        return response()->json($array, $code);
    }

    public function createdResponse($data = null, $message = "Resource created successfully")
    {
        return $this->apiResponse('success', $message, $data, 201);
    }

    public function updatedResponse($data = null, $message = "Resource updated successfully")
    {
        return $this->apiResponse('success', $message, $data, 200);
    }

    public function deletedResponse($message = "Resource deleted successfully")
    {
        return $this->apiResponse('success', $message, null, 200);
    }

    public function retrievedResponse($data = null, $message = "Data retrieved successfully")
    {
        return $this->apiResponse('success', $message, $data, 200);
    }

    public function unauthorizedResponse($message = "Unauthorized access")
    {
        return $this->apiResponse('error', $message, null, 401);
    }

    public function validationErrorResponse($errors = [], $message = "Validation errors occurred")
    {
        return $this->apiResponse('error', $message, $errors, 422);
    }

    public function notFoundResponse($message = "Resource not found")
    {
        return $this->apiResponse('error', $message, null, 404);
    }

    public function serverErrorResponse($message = "Internal server error")
    {
        return $this->apiResponse('error', $message, null, 500);
    }

    public function forbiddenResponse($message = "Access forbidden")
    {
        return $this->apiResponse('error', $message, null, 403);
    }

    public function badRequestResponse($message = "Bad request")
    {
        return $this->apiResponse('error', $message, null, 400);
    }
}
