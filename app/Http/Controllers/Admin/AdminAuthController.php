<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AdminAuthController extends Controller
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

        if (!$token = Auth::guard('admin')->attempt($credentials)) {
            return $this->apiResponse(401, 'Unauthorized');
        }

        $admin = new UserResource(Auth::guard('admin')->user());

        $data = [
            'admin' => $admin,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60
            ]
        ];

        return $this->apiResponse('success', 'You are logged in successfully', $data);
    }


    public function register(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:6',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = Auth::guard('admin')->login($admin);

        $adminResource = new UserResource(Auth::guard('admin')->user());

        $data = [
            'admin' => $adminResource,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60
            ]
        ];

        return $this->createdResponse($data, 'You are registered successfully');
    }

    public function logout()
    {
        try {
            Auth::guard('admin')->logout();
            return $this->apiResponse('success', 'Admin logged out successfully');
        } catch (\Exception $e) {
            return $this->apiResponse(500, 'Something went wrong');
        }
    }

    public function refresh()
    {
        return response()->json([
            'token' => Auth::guard('admin')->refresh()
        ]);
    }

}
