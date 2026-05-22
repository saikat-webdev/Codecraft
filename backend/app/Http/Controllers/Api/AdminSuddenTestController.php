<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\SuddenTestQuestionResource;
use App\Models\SuddenTestQuestion;
use App\Models\SuddenTestSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSuddenTestController extends BaseApiController
{
    public function settings(): JsonResponse
    {
        return $this->success(SuddenTestSetting::current(), 'Sudden test settings');
    }

    public function updateSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'enabled' => ['sometimes', 'boolean'],
            'min_interval_seconds' => ['sometimes', 'integer', 'min:60', 'max:3600'],
            'max_interval_seconds' => ['sometimes', 'integer', 'min:60', 'max:7200'],
            'default_timer_seconds' => ['sometimes', 'integer', 'min:30', 'max:300'],
            'base_difficulty' => ['sometimes', 'integer', 'min:1', 'max:5'],
        ]);

        $settings = SuddenTestSetting::current();
        $settings->update($validated);

        return $this->success($settings->fresh(), 'Settings updated');
    }

    public function questions(): JsonResponse
    {
        $questions = SuddenTestQuestion::orderByDesc('id')->get();

        return $this->success($questions, 'Questions list');
    }

    public function storeQuestion(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:mcq,coding'],
            'title' => ['required', 'string', 'max:120'],
            'prompt' => ['required', 'string'],
            'options' => ['nullable', 'array'],
            'correct_answer' => ['nullable', 'string'],
            'starter_code' => ['nullable', 'string'],
            'expected_output' => ['nullable', 'string'],
            'language' => ['nullable', 'string', 'max:30'],
            'difficulty' => ['integer', 'min:1', 'max:5'],
            'time_limit_seconds' => ['integer', 'min:30', 'max:300'],
            'is_active' => ['boolean'],
        ]);

        $question = SuddenTestQuestion::create($validated);

        return $this->success($question, 'Question created', 201);
    }

    public function updateQuestion(Request $request, SuddenTestQuestion $question): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['sometimes', 'in:mcq,coding'],
            'title' => ['sometimes', 'string', 'max:120'],
            'prompt' => ['sometimes', 'string'],
            'options' => ['nullable', 'array'],
            'correct_answer' => ['nullable', 'string'],
            'starter_code' => ['nullable', 'string'],
            'expected_output' => ['nullable', 'string'],
            'language' => ['nullable', 'string', 'max:30'],
            'difficulty' => ['integer', 'min:1', 'max:5'],
            'time_limit_seconds' => ['integer', 'min:30', 'max:300'],
            'is_active' => ['boolean'],
        ]);

        $question->update($validated);

        return $this->success($question->fresh(), 'Question updated');
    }

    public function destroyQuestion(SuddenTestQuestion $question): JsonResponse
    {
        $question->delete();

        return $this->success(null, 'Question deleted');
    }
}
