<?php

namespace App\Services;

use App\Models\SuddenTestAttempt;
use App\Models\SuddenTestQuestion;
use App\Models\SuddenTestSetting;
use App\Models\User;
/**
 * Sudden test selection, difficulty scaling, and result recording.
 */
class SuddenTestService
{
    public function __construct(
        protected GamificationService $gamification,
        protected Judge0Service $judge0,
    ) {}

    public function settings(): SuddenTestSetting
    {
        return SuddenTestSetting::current();
    }

    public function isEnabled(): bool
    {
        return $this->settings()->enabled;
    }

    /**
     * Pick a question near the user's adaptive difficulty (1–5).
     */
    public function pickQuestionForUser(User $user): ?SuddenTestQuestion
    {
        $difficulty = $this->resolveUserDifficulty($user);

        $question = SuddenTestQuestion::query()
            ->where('is_active', true)
            ->whereBetween('difficulty', [
                max(1, $difficulty - 1),
                min(5, $difficulty + 1),
            ])
            ->inRandomOrder()
            ->first();

        if (! $question) {
            $question = SuddenTestQuestion::query()
                ->where('is_active', true)
                ->inRandomOrder()
                ->first();
        }

        return $question;
    }

    public function resolveUserDifficulty(User $user): int
    {
        $stored = (int) ($user->sudden_test_difficulty ?? 2);
        $settings = $this->settings();
        $base = (int) $settings->base_difficulty;

        $recent = SuddenTestAttempt::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        if ($recent->isEmpty()) {
            return max(1, min(5, (int) round(($stored + $base) / 2)));
        }

        $passRate = $recent->where('passed', true)->count() / $recent->count();

        if ($passRate >= 0.8 && $stored < 5) {
            return $stored + 1;
        }

        if ($passRate < 0.4 && $stored > 1) {
            return $stored - 1;
        }

        return max(1, min(5, $stored));
    }

    public function adjustDifficultyAfterAttempt(User $user, bool $passed): void
    {
        $current = (int) ($user->sudden_test_difficulty ?? 2);

        if ($passed && $current < 5) {
            $user->sudden_test_difficulty = $current + 1;
        } elseif (! $passed && $current > 1) {
            $user->sudden_test_difficulty = $current - 1;
        }

        $user->save();
    }

    public function submitAttempt(
        User $user,
        SuddenTestQuestion $question,
        array $payload,
        int $timeTakenSeconds
    ): array {
        $difficulty = $this->resolveUserDifficulty($user);
        $passed = false;
        $score = 0;

        if ($question->type === 'mcq') {
            $answer = $payload['answer'] ?? '';
            $passed = trim($answer) === trim((string) $question->correct_answer);
            $score = $passed ? 100 : 0;
        } elseif ($question->type === 'coding') {
            $code = $payload['code'] ?? '';
            $run = $this->judge0->runCode($code, $question->language ?? 'python', 5);
            $output = trim($run['output'] ?? '');
            $expected = trim((string) $question->expected_output);
            $passed = $run['success'] && ($expected === '' || str_contains($output, $expected) || $output === $expected);
            $score = $passed ? 100 : ($run['success'] ? 50 : 0);
        }

        $xpAwarded = 0;

        if ($passed) {
            $xpAwarded = GamificationService::XP_SUDDEN_TEST_PASS + ($difficulty * 5);
            $this->gamification->addXp($user, $xpAwarded);
        }

        $this->adjustDifficultyAfterAttempt($user, $passed);

        $attempt = SuddenTestAttempt::create([
            'user_id' => $user->id,
            'sudden_test_question_id' => $question->id,
            'difficulty_at_attempt' => $difficulty,
            'passed' => $passed,
            'time_taken_seconds' => $timeTakenSeconds,
            'score' => $score,
            'response' => json_encode($payload),
            'xp_awarded' => $xpAwarded,
        ]);

        $this->gamification->checkAchievements($user->fresh());

        return [
            'attempt' => $attempt,
            'passed' => $passed,
            'score' => $score,
            'xp_awarded' => $xpAwarded,
            'difficulty' => $user->fresh()->sudden_test_difficulty,
            'feedback' => $passed
                ? 'Nice work! Sudden test cleared.'
                : 'Keep practicing — difficulty will adjust to help you learn.',
        ];
    }

    public function userStats(User $user): array
    {
        $attempts = SuddenTestAttempt::where('user_id', $user->id);

        return [
            'total_attempts' => $attempts->count(),
            'passed' => (clone $attempts)->where('passed', true)->count(),
            'average_score' => (int) round((clone $attempts)->avg('score') ?? 0),
            'current_difficulty' => $user->sudden_test_difficulty ?? 2,
        ];
    }
}
