<?php

namespace App\Http\Controllers\User;

use App\Services\User\UserAuthService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;

class UserAuthController extends Controller
{
    use ApiResponseTrait;

    protected $authService;

    public function __construct(UserAuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request)
    {
        $result = $this->authService->login($request);

        if ($result['status'] == 'error') {
            return isset($result['errors'])
                ? $this->validationErrorResponse($result['errors'])
                : $this->unauthorizedResponse($result['message']);
        }

        return $this->apiResponse('success', 'You are logged in successfully', $result['data']);
    }

    public function register(Request $request)
    {
        $result = $this->authService->register($request);

        if ($result['status'] == 'error') {
            return $this->validationErrorResponse($result['errors']);
        }

        return $this->createdResponse($result['data'], 'You are registered successfully');
    }

    public function logout()
    {
        $result = $this->authService->logout();
        return $this->apiResponse('success', $result['message']);
    }

    public function refresh()
    {
        $result = $this->authService->refresh();

        if ($result['status'] == 'error') {
            return $this->unauthorizedResponse($result['message']);
        }

        return $this->apiResponse('success', 'Token refreshed successfully', $result['data']);
    }

    public function redirectToGoogle()
    {
        $loginUrl = $this->authService->googleRedirect();
        return $this->retrievedResponse(['login_url' => $loginUrl], 'login_url of google returned successfully');
    }

    public function handleGoogleCallback()
    {
        $result = $this->authService->handleGoogleCallback();

        if ($result['status'] == 'error') {
            return $this->unauthorizedResponse($result['message']);
        }

        return $this->apiResponse('success', 'You are logged in successfully', $result['data']);
    }
}
