<?php

namespace App\Http\Resources;

use App\Services\GamificationService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $gamification = app(GamificationService::class);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar_url' => $this->avatar_url,
            'bio' => $this->bio,
            'is_admin' => (bool) $this->is_admin,
            'learning_goal' => $this->learning_goal,
            'preferred_language' => $this->preferred_language,
            'daily_learning_time' => $this->daily_learning_time,
            'skill_level' => $this->skill_level,
            'xp' => $this->xp ?? 0,
            'level' => $this->level ?? 1,
            'xp_to_next_level' => $gamification->xpToNextLevel($this->xp ?? 0),
            'streak_count' => $this->streak_count ?? 0,
            'longest_streak' => $this->longest_streak ?? 0,
            'last_activity_date' => $this->last_activity_date?->toDateString(),
            'sudden_test_difficulty' => $this->sudden_test_difficulty ?? 2,
            'achievements' => AchievementResource::collection(
                $this->whenLoaded('achievements')
            ),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
