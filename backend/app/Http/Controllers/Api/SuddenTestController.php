<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\SuddenTestQuestionResource;
use App\Models\SuddenTestQuestion;
use App\Services\SuddenTestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SuddenTestController extends BaseApiController
{
    public function __construct(protected SuddenTestService $suddenTests) {}

    /** Public config — whether popups should run (no question leak). */
    public function config(): JsonResponse
    {
        $settings = $this->suddenTests->settings();

        return $this->success([
            'enabled' => $settings->enabled,
            'min_interval_seconds' => $settings->min_interval_seconds,
            'max_interval_seconds' => $settings->max_interval_seconds,
            'default_timer_seconds' => $settings->default_timer_seconds,
        ], 'Sudden test config');
    }

    /** Authenticated: fetch one challenge for the popup. */
    public function challenge(Request $request): JsonResponse
    {
        if (! $this->suddenTests->isEnabled()) {
            return $this->error('Sudden tests are disabled', 404);
        }

        $user = $request->user();
        $question = $this->suddenTests->pickQuestionForUser($user);

        if (! $question) {
            return $this->error('No sudden test questions available', 404);
        }

        return $this->success([
            'question' => new SuddenTestQuestionResource($question),
            'difficulty' => $this->suddenTests->resolveUserDifficulty($user),
            'timer_seconds' => $question->time_limit_seconds
                ?? $this->suddenTests->settings()->default_timer_seconds,
        ], 'Sudden test challenge');
    }

    public function submit(Request $request, SuddenTestQuestion $question): JsonResponse
    {
        if (! $this->suddenTests->isEnabled()) {
            return $this->error('Sudden tests are disabled', 403);
        }

        $validated = $request->validate([
            'answer' => ['nullable', 'string'],
            'code' => ['nullable', 'string', 'max:50000'],
            'time_taken_seconds' => ['required', 'integer', 'min:0', 'max:600'],
        ]);

        $result = $this->suddenTests->submitAttempt(
            $request->user(),
            $question,
            $validated,
            $validated['time_taken_seconds']
        );

        return $this->success([
            'passed' => $result['passed'],
            'score' => $result['score'],
            'xp_awarded' => $result['xp_awarded'],
            'difficulty' => $result['difficulty'],
            'feedback' => $result['feedback'],
        ], 'Sudden test submitted');
    }
}
