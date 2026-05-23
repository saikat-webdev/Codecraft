<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ExamAttemptResource;
use App\Http\Resources\ExamResource;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Services\GamificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamController extends BaseApiController
{
    public function __construct(protected GamificationService $gamification) {}

    public function index(Request $request): JsonResponse
    {
        $query = Exam::query()
            ->where('is_published', true)
            ->withCount('questions')
            ->orderBy('order');

        if ($track = $request->query('track')) {
            $query->where('track', $track);
        }

        return $this->success(ExamResource::collection($query->get()), 'Exams retrieved');
    }

    public function show(Exam $exam): JsonResponse
    {
        if (!$exam->is_published) {
            return $this->error('Exam not found', 404);
        }

        $exam->load(['questions' => fn ($q) => $q->orderBy('order')]);

        return $this->success(new ExamResource($exam), 'Exam retrieved');
    }

    public function myAttempts(Request $request): JsonResponse
    {
        $attempts = ExamAttempt::where('user_id', $request->user()->id)
            ->with('exam')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return $this->success(ExamAttemptResource::collection($attempts), 'Exam attempts retrieved');
    }

    public function submit(Request $request, Exam $exam): JsonResponse
    {
        if (!$exam->is_published) {
            return $this->error('Exam not found', 404);
        }

        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|integer',
            'answers.*.answer' => 'required|string|max:5000',
            'started_at' => 'nullable|date',
        ]);

        $exam->load('questions');
        $questions = $exam->questions->keyBy('id');

        $score = 0;
        $maxScore = 0;
        $gradedAnswers = [];

        foreach ($validated['answers'] as $entry) {
            $question = $questions->get($entry['question_id']);
            if (!$question) {
                continue;
            }

            $maxScore += $question->points;
            $isCorrect = trim((string) $entry['answer']) === trim((string) $question->correct_answer);

            if ($isCorrect) {
                $score += $question->points;
            }

            $gradedAnswers[] = [
                'question_id' => $question->id,
                'answer' => $entry['answer'],
                'correct' => $isCorrect,
                'points_earned' => $isCorrect ? $question->points : 0,
                'explanation' => $question->explanation,
            ];
        }

        $percentage = $maxScore > 0 ? (int) round(($score / $maxScore) * 100) : 0;
        $passed = $percentage >= $exam->passing_score;

        $attempt = ExamAttempt::create([
            'user_id' => $request->user()->id,
            'exam_id' => $exam->id,
            'score' => $score,
            'max_score' => $maxScore,
            'percentage' => $percentage,
            'passed' => $passed,
            'answers' => $gradedAnswers,
            'started_at' => $validated['started_at'] ?? now(),
            'completed_at' => now(),
        ]);

        if ($passed) {
            $this->gamification->addXp($request->user(), GamificationService::XP_LESSON_COMPLETE);
        }

        return $this->success(new ExamAttemptResource($attempt->load('exam')), 'Exam submitted');
    }
}
