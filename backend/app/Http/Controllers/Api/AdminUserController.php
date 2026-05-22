<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($status = $request->get('status')) {
            if ($status === 'suspended') {
                $query->where('is_suspended', true);
            } elseif ($status === 'active') {
                $query->where('is_suspended', false);
            } elseif ($status === 'admin') {
                $query->where('is_admin', true);
            }
        }

        $users = $query->orderByDesc('id')->paginate(20);

        return $this->success($users, 'Users retrieved');
    }

    public function show(User $user): JsonResponse
    {
        $user->load(['achievements', 'progress', 'suddenTestAttempts']);

        return $this->success($user, 'User retrieved');
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'bio' => ['nullable', 'string', 'max:500'],
            'learning_goal' => ['nullable', 'string', 'max:255'],
            'preferred_language' => ['nullable', 'string', 'max:50'],
            'daily_learning_time' => ['nullable', 'integer', 'min:5', 'max:480'],
            'skill_level' => ['nullable', 'string', 'max:50'],
            'xp' => ['nullable', 'integer', 'min:0'],
            'level' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $user->update($validated);

        return $this->success($user->fresh(), 'User updated');
    }

    public function suspend(User $user): JsonResponse
    {
        if ($user->is_admin) {
            return $this->error('Cannot suspend admin users', 422);
        }

        $user->update(['is_suspended' => true]);

        return $this->success($user->fresh(), 'User suspended');
    }

    public function activate(User $user): JsonResponse
    {
        $user->update(['is_suspended' => false]);

        return $this->success($user->fresh(), 'User activated');
    }

    public function assignRole(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'in:admin,user'],
        ]);

        $user->update(['is_admin' => $validated['role'] === 'admin']);

        return $this->success($user->fresh(), 'User role updated');
    }
}