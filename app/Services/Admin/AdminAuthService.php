<?php

namespace App\Services\Admin;

use App\Http\Resources\UserResource;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;

class AdminAuthService
{
    public function login($request)
    {
        $rules = [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ];

        $validate = Validator::make($request->all(), $rules);
        if ($validate->fails()) {
            return ['status' => 'error', 'errors' => $validate->errors()];
        }

        $credentials = $request->only('email', 'password');

        if (!$token = Auth::guard('admin')->attempt($credentials)) {
            return ['status' => 'error', 'message' => 'Unauthorized'];
        }

        $admin = new UserResource(Auth::guard('admin')->user());

        $data = [
            'user' => $admin,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
            ],
        ];

        return ['status' => 'success', 'data' => $data];
    }

    public function register($request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:6',
        ];

        $validate = Validator::make($request->all(), $rules);
        if ($validate->fails()) {
            return ['status' => 'error', 'errors' => $validate->errors()];
        }

        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);


        $token = Auth::guard('admin')->login($admin);
        $admin = new UserResource($admin);

        $data = [
            'user' => $admin,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
            ],
        ];

        return ['status' => 'success', 'data' => $data];
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        return ['status' => 'success', 'message' => 'Admin logged out successfully'];
    }

    public function refresh()
    {
        try {
            $newToken = Auth::guard('admin')->refresh();

            return [
                'status' => 'success',
                'data' => [
                    'token' => $newToken,
                    'type' => 'bearer',
                    'expires_in' => JWTAuth::factory()->getTTL() * 60,
                ],
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Unable to refresh token',
            ];
        }
    }
}
