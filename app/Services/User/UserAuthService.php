<?php

namespace App\Services\User;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Events\CartEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;

class UserAuthService
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

        if (!$token = Auth::guard('user')->attempt($credentials)) {
            return ['status' => 'error', 'message' => 'Unauthorized'];
        }

        $user = new UserResource(Auth::guard('user')->user());

        $data = [
            'user' => $user,
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
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ];

        $validate = Validator::make($request->all(), $rules);
        if ($validate->fails()) {
            return ['status' => 'error', 'errors' => $validate->errors()];
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new CartEvent($user));

        $token = Auth::guard('user')->login($user);
        $user = new UserResource($user);

        $data = [
            'user' => $user,
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
        Auth::guard('user')->logout();
        return ['status' => 'success', 'message' => 'User logged out successfully'];
    }

    public function googleRedirect()
    {
        return Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
    }

    public function handleGoogleCallback($request)
    {
        try {
            $userSocial =Socialite::driver('google')->stateless()->userFromToken($request->token);
            $user = User::where('email', $userSocial->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'name' => $userSocial->getName(),
                    'email' => $userSocial->getEmail(),
                    'google_id' => $userSocial->getId(),
                    'provider' => 'google',
                ]);

                event(new CartEvent($user));
            }

            $token = Auth::guard('user')->login($user);
            $user = new UserResource(Auth::guard('user')->user());

            $data = [
                'user' => $user,
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                    'expires_in' => JWTAuth::factory()->getTTL() * 60,
                ],
            ];

            return ['status' => 'success', 'data' => $data];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Authentication failed'];
        }
    }

    public function refresh()
    {
        try {
            $newToken = Auth::guard('user')->refresh();

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
