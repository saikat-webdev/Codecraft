<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\ExerciseSubmission;
use App\Models\Progress;
use App\Models\SuddenTestAttempt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Central XP, streak, level, and achievement logic.
 * Called after lessons, exercises, and sudden tests complete.
 */
class GamificationService
{
    public const XP_LESSON_COMPLETE = 50;

    public const XP_EXERCISE_CORRECT = 30;

    public const XP_SUDDEN_TEST_PASS = 40;

    public function recordActivity(User $user): User
    {
        $today = Carbon::today();
        $last = $user->last_activity_date
            ? Carbon::parse($user->last_activity_date)
            : null;

        if ($last && $last->isSameDay($today)) {
            return $user;
        }

        if ($last && $last->diffInDays($today) === 1) {
            $user->streak_count = ($user->streak_count ?? 0) + 1;
        } else {
            $user->streak_count = 1;
        }

        $user->longest_streak = max($user->longest_streak ?? 0, $user->streak_count);
        $user->last_activity_date = $today;
        $user->save();

        $this->checkAchievements($user);

        return $user->fresh();
    }

    public function addXp(User $user, int $amount, bool $recordActivity = true): User
    {
        if ($amount <= 0) {
            return $user;
        }

        $user->xp = ($user->xp ?? 0) + $amount;
        $user->level = $this->levelFromXp($user->xp);
        $user->save();

        if ($recordActivity) {
            $this->recordActivity($user);
        } else {
            $this->checkAchievements($user->fresh());
        }

        return $user->fresh();
    }

    public function levelFromXp(int $xp): int
    {
        return max(1, (int) floor(sqrt($xp / 50)) + 1);
    }

    public function xpToNextLevel(int $xp): int
    {
        $currentLevel = $this->levelFromXp($xp);
        $nextThreshold = pow(max(0, $currentLevel), 2) * 50;

        return max(0, $nextThreshold - $xp);
    }

    public function checkAchievements(User $user): Collection
    {
        $stats = $this->buildStats($user);
        $earned = collect();

        $achievements = Achievement::all();

        foreach ($achievements as $achievement) {
            if ($user->achievements()->where('achievement_id', $achievement->id)->exists()) {
                continue;
            }

            $value = $stats[$achievement->criteria_key] ?? 0;

            if ($value >= $achievement->criteria_value) {
                $user->achievements()->attach($achievement->id, [
                    'earned_at' => now(),
                ]);
                $this->addXp($user, $achievement->xp_reward, false);
                $earned->push($achievement);
            }
        }

        return $earned;
    }

    public function buildStats(User $user): array
    {
        $lessonsCompleted = Progress::where('user_id', $user->id)->where('completed', true)->count();
        $exercisesPassed = ExerciseSubmission::where('user_id', $user->id)->where('is_correct', true)->count();
        $suddenPassed = SuddenTestAttempt::where('user_id', $user->id)->where('passed', true)->count();

        return [
            'lessons_completed' => $lessonsCompleted,
            'exercises_passed' => $exercisesPassed,
            'sudden_tests_passed' => $suddenPassed,
            'streak_days' => $user->streak_count ?? 0,
            'total_xp' => $user->xp ?? 0,
            'level_reached' => $user->level ?? 1,
        ];
    }
}
