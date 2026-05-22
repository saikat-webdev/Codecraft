<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\AchievementResource;
use App\Http\Resources\ProfileStatsResource;
use App\Http\Resources\UserResource;
use App\Models\Achievement;
use App\Models\ExerciseSubmission;
use App\Services\GamificationService;
use App\Services\SuddenTestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;

class ProfileController extends BaseApiController
{
    public function __construct(
        protected GamificationService $gamification,
        protected SuddenTestService $suddenTests,
    ) {}

    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load('achievements');

        return $this->success(new UserResource($user), 'Profile retrieved');
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'bio' => ['nullable', 'string', 'max:500'],
            'learning_goal' => ['nullable', 'string', 'max:255'],
            'preferred_language' => ['nullable', 'string', 'max:50'],
            'daily_learning_time' => ['nullable', 'integer', 'min:5', 'max:480'],
            'skill_level' => ['nullable', 'string', 'max:50'],
        ]);

        $user = $request->user();
        $user->update($validated);
        $this->gamification->recordActivity($user);

        return $this->success(
            new UserResource($user->fresh()->load('achievements')),
            'Profile updated'
        );
    }

    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => ['required', File::image()->max(2048)],
        ]);

        $user = $request->user();

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $path = $request->file('avatar')->store('avatars/'.$user->id, 'public');
        $user->update(['avatar_path' => $path]);

        return $this->success(
            new UserResource($user->fresh()->load('achievements')),
            'Avatar updated'
        );
    }

    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $stats = $this->gamification->buildStats($user);
        $stats['longest_streak'] = $user->longest_streak ?? 0;
        $stats['total_submissions'] = ExerciseSubmission::where('user_id', $user->id)->count();
        $stats['sudden_test_stats'] = $this->suddenTests->userStats($user);

        return $this->success(new ProfileStatsResource($stats), 'Profile stats retrieved');
    }

    public function achievements(Request $request): JsonResponse
    {
        $user = $request->user();
        $earnedIds = $user->achievements()->pluck('achievements.id');

        $all = Achievement::orderBy('criteria_value')->get()->map(function ($achievement) use ($earnedIds, $user) {
            $earned = $earnedIds->contains($achievement->id);
            $pivot = $earned
                ? $user->achievements()->where('achievement_id', $achievement->id)->first()?->pivot
                : null;

            return [
                'id' => $achievement->id,
                'slug' => $achievement->slug,
                'name' => $achievement->name,
                'description' => $achievement->description,
                'icon' => $achievement->icon,
                'xp_reward' => $achievement->xp_reward,
                'earned' => $earned,
                'earned_at' => $pivot?->earned_at,
            ];
        });

        return $this->success($all, 'Achievements retrieved');
    }
}
