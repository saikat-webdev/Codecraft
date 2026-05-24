<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AppSettingsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends BaseApiController
{
    public function register(RegisterRequest $request, AppSettingsService $settings): JsonResponse
    {
        if (! $settings->isRegistrationEnabled()) {
            return $this->error('Registration is currently disabled.', 403);
        }

        if ($settings->isMaintenanceMode()) {
            return $this->error('Registration is disabled while the platform is in maintenance mode.', 503);
        }

        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);
        $token = $user->createToken('api-token')->plainTextToken;

        return $this->success([
            'user' => new UserResource($user->load('achievements')),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Registration successful', 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (! Auth::attempt($credentials)) {
            return $this->error('Invalid credentials', 401);
        }

        $user = Auth::user();

        if ($user->is_suspended) {
            Auth::logout();

            return $this->error('Your account has been suspended. Contact support.', 403);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->success([
            'user' => new UserResource($user->load('achievements')),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->currentAccessToken()?->delete();

        return $this->success(null, 'Logged out successfully');
    }

    public function user(Request $request): JsonResponse
    {
        return $this->success(
            new UserResource($request->user()->load('achievements')),
            'Authenticated user retrieved'
        );
    }
}
