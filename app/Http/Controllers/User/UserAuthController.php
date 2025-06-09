<?php

namespace App\Http\Controllers\User;

use App\Services\User\UserAuthService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;

class UserAuthController extends Controller
{
    use ApiResponseTrait;

    protected $userAuthService;

    public function __construct(UserAuthService $userAuthService)
    {
        $this->userAuthService = $userAuthService;
    }

    public function login(Request $request)
    {
        $result = $this->userAuthService->login($request);

        if ($result['status'] == 'error') {
            return isset($result['errors'])
                ? $this->validationErrorResponse($result['errors'])
                : $this->unauthorizedResponse($result['message']);
        }

        return $this->apiResponse('success', 'You are logged in successfully', $result['data']);
    }

    public function register(Request $request)
    {
        $result = $this->userAuthService->register($request);

        if ($result['status'] == 'error') {
            return $this->validationErrorResponse($result['errors']);
        }

        return $this->createdResponse($result['data'], 'You are registered successfully');
    }

    public function logout()
    {
        $result = $this->userAuthService->logout();
        return $this->apiResponse('success', $result['message']);
    }

    public function refresh()
    {
        $result = $this->userAuthService->refresh();

        if ($result['status'] == 'error') {
            return $this->unauthorizedResponse($result['message']);
        }

        return $this->apiResponse('success', 'Token refreshed successfully', $result['data']);
    }

    public function redirectToGoogle()
    {
        $loginUrl = $this->userAuthService->googleRedirect();
        return $this->retrievedResponse(['login_url' => $loginUrl], 'login_url of google returned successfully');
    }

    public function handleGoogleCallback(Request $request)
    {
        $result = $this->userAuthService->handleGoogleCallback($request);

        if ($result['status'] == 'error') {
            return $this->unauthorizedResponse($result['message']);
        }

        return $this->apiResponse('success', 'You are logged in successfully', $result['data']);
    }


}
