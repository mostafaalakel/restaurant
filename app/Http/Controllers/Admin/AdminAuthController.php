<?php

namespace App\Http\Controllers\Admin;

use App\Services\Admin\AdminAuthService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;

class AdminAuthController extends Controller
{
    use ApiResponseTrait;

    protected $adminAuthService;

    public function __construct(AdminAuthService $adminAuthService)
    {
        $this->adminAuthService = $adminAuthService;
    }

    public function login(Request $request)
    {
        $result = $this->adminAuthService->login($request);

        if ($result['status'] == 'error') {
            return isset($result['errors'])
                ? $this->validationErrorResponse($result['errors'])
                : $this->unauthorizedResponse($result['message']);
        }

        return $this->apiResponse('success', 'You are logged in successfully', $result['data']);
    }

    public function register(Request $request)
    {
        $result = $this->adminAuthService->register($request);

        if ($result['status'] == 'error') {
            return $this->validationErrorResponse($result['errors']);
        }

        return $this->createdResponse($result['data'], 'You are registered successfully');
    }

    public function logout()
    {
        $result = $this->adminAuthService->logout();
        return $this->apiResponse('success', $result['message']);
    }

    public function refresh()
    {
        $result = $this->adminAuthService->refresh();

        if ($result['status'] == 'error') {
            return $this->unauthorizedResponse($result['message']);
        }

        return $this->apiResponse('success', 'Token refreshed successfully', $result['data']);
    }

}
