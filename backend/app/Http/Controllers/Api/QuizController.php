<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\QuizSubmitRequest;
use App\Http\Resources\QuizResource;
use App\Models\Quiz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuizController extends BaseApiController
{
    public function byLesson($slug): JsonResponse
    {
        $quizzes = Quiz::whereHas('lesson', fn($q) => $q->where('slug', $slug))->get();

        return $this->success(QuizResource::collection($quizzes), 'Quizzes retrieved');
    }

    public function submit(QuizSubmitRequest $request): JsonResponse
    {
        $data = $request->validated();

        $quiz = Quiz::where('lesson_id', $data['lesson_id'])->where('question', $data['question'])->first();

        if (! $quiz) {
            return $this->error('Quiz question not found', 404);
        }

        $isCorrect = $quiz->correct_answer === $data['selected_answer'];

        return $this->success([
            'correct' => $isCorrect,
            'explanation' => $quiz->explanation,
        ], 'Quiz submitted');
    }
}
