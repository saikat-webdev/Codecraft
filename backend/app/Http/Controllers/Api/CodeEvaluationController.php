<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExerciseSubmissionResource;
use App\Models\CodingExercise;
use App\Models\ExerciseSubmission;
use App\Services\CodeEvaluationService;
use Illuminate\Http\Request;

class CodeEvaluationController extends Controller
{
    protected CodeEvaluationService $evaluator;

    public function __construct(CodeEvaluationService $evaluator)
    {
        $this->evaluator = $evaluator;
    }

    public function evaluate(Request $request, CodingExercise $exercise)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50000',
            'output' => 'nullable|string',
            'error' => 'nullable|string',
        ]);

        $feedback = $this->evaluator->evaluateCode(
            $validated['code'],
            $exercise,
            $validated['output'] ?? null,
            $validated['error'] ?? null
        );

        return response()->json([
            'feedback' => $feedback,
        ]);
    }

    public function submit(Request $request, CodingExercise $exercise)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50000',
            'output' => 'nullable|string',
            'error' => 'nullable|string',
        ]);

        $isCorrect = false;
        if ($validated['output'] && $exercise->expected_output) {
            $normalizedOutput = trim($validated['output']);
            $normalizedExpected = trim($exercise->expected_output);
            $isCorrect = str_contains($normalizedOutput, $normalizedExpected) || $normalizedOutput === $normalizedExpected;
        }

        $aiFeedback = $this->evaluator->evaluateCode(
            $validated['code'],
            $exercise,
            $validated['output'] ?? null,
            $validated['error'] ?? null
        );

        $submission = ExerciseSubmission::create([
            'user_id' => $request->user()->id,
            'exercise_id' => $exercise->id,
            'submitted_code' => $validated['code'],
            'output' => $validated['output'] ?? null,
            'is_correct' => $isCorrect,
            'ai_feedback' => $aiFeedback,
        ]);

        return new ExerciseSubmissionResource($submission);
    }

    public function hints(CodingExercise $exercise)
    {
        $hints = $this->evaluator->getHints($exercise);

        return response()->json([
            'hints' => $hints,
        ]);
    }

    public function history(Request $request)
    {
        $submissions = ExerciseSubmission::where('user_id', $request->user()->id)
            ->with('exercise.lesson')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return ExerciseSubmissionResource::collection($submissions);
    }
}