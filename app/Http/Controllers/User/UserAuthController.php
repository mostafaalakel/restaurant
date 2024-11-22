<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Events\CartEvent;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class UserAuthController extends Controller
{
    use ApiResponseTrait;

    public function login(Request $request)
    {
        $rules = [
            'email' => 'required|string|email',
            'password' => 'required|string'
        ];

        $validate = Validator::make($request->all(), $rules);
        if ($validate->fails()) {
            return $this->validationErrorResponse($validate->errors());
        }

        $credentials = $request->only('email', 'password');

        if (!$token = Auth::guard('user')->attempt($credentials)) {
            return $this->apiResponse(401, 'Unauthorized');
        }

        $user = new UserResource(Auth::guard('user')->user());

        $data = [
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60
            ]
        ];

        return $this->apiResponse(200, 'You are logged in successfully', $data);
    }

    public function register(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ];

        $validate = Validator::make($request->all(), $rules);
        if ($validate->fails()) {
            return $this->validationErrorResponse($validate->errors());
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new CartEvent($user));

        $token = Auth::guard('user')->login($user);

        $user = new UserResource(Auth::guard('user')->user());

        $data = [
            'user' => $user,
            'authorization' => [
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
            Auth::guard('user')->logout();
            return $this->apiResponse(200, 'User logged out successfully');
        } catch (Exception $e) {
            return $this->apiResponse(500, 'Something went wrong', []);
        }
    }

    public function refresh()
    {
        return response()->json([
            'token' => Auth::guard('user')->refresh()
        ]);
    }

    public function redirectToGoogle()
    {
        $loginUrl = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();

        return response()->json([
            'login_url' => $loginUrl,
        ]);
    }

    public function handleGoogleCallback()
    {
        try {
            $userSocial = Socialite::driver('google')->stateless()->user();

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
            $user = new UserResource($user);

            $data = [
                'user' => $user,
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                    'expires_in' => JWTAuth::factory()->getTTL() * 60,
                ],
            ];

            return $this->apiResponse('success', 'You are logged in successfully', $data);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Authentication failed'], 401);
        }
    }

}
