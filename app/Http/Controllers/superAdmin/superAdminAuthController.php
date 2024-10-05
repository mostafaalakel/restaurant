<?php

namespace App\Http\Controllers\superAdmin;

use App\Models\Admin;
use App\Models\SuperAdmin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class superAdminAuthController extends Controller
{
    use ApiResponseTrait;
    public function login(Request $request)
    {
        $rules = [
            'email' => 'required|string|email',
            'password' => 'required|string'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $credentials = $request->only('email', 'password');

        if (!$token = Auth::guard('superAdmin')->attempt($credentials)) {
            return $this->apiResponse(401, 'Unauthorized');
        }

        $superAdmin = new UserResource(Auth::guard('superAdmin')->user());

        $data = [
            'superAdmin' => $superAdmin,
            'authorization' =>  [
                'token' => $token,
                'type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60
            ]
        ];

        return $this->apiResponse(200, 'You are logged in successfully', $data);
    }

    public function me()
    {
        $superAdmin_info = new UserResource(Auth::guard('superAdmin')->user());
        return $this->apiResponse(200, 'superAdmin information retrieved successfully', $superAdmin_info);
    }

    public function register(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:super_admins',
            'password' => 'required|string|min:6',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $superAdmin = SuperAdmin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = Auth::guard('superAdmin')->login($superAdmin);

        $superAdminResource = new UserResource(Auth::guard('superAdmin')->user());

        $data = [
            'superAdmin' => $superAdminResource,
            'authorization' =>  [
                'token' => $token,
                'type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60
            ]
        ];

        return $this->apiResponse(201, 'You are registered successfully', $data);
    }

    public function logout()
    {
        try {
            Auth::guard('superAdmin')->logout();
            return $this->apiResponse(200, 'superAdmin logged out successfully');
        } catch (\Exception $e) {
            return $this->apiResponse(500, 'Something went wrong');
        }
    }
    public function refresh()
    {
        return response()->json([
            'token' => Auth::guard('superAdmin')->refresh()
        ]);
    }
}
