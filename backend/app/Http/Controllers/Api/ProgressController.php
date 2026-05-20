<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ProgressUpdateRequest;
use App\Http\Resources\LessonSummaryResource;
use App\Http\Resources\ProgressResource;
use App\Models\Lesson;
use App\Models\Progress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgressController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $lessons = Lesson::with('module')->orderBy('module_id')->orderBy('order')->get();
        $progress = Progress::where('user_id', $user->id)->with('lesson.module')->get();
        $completedCount = $progress->where('completed', true)->count();
        $totalLessons = $lessons->count();
        $percentage = $totalLessons ? (int) round(($completedCount / $totalLessons) * 100) : 0;

        $exerciseSubmissions = \App\Models\ExerciseSubmission::where('user_id', $user->id)->get();
        $completedExercises = $exerciseSubmissions->where('is_correct', true)->count();
        $totalExercises = \App\Models\CodingExercise::count();
        $exercisePercentage = $totalExercises ? (int) round(($completedExercises / $totalExercises) * 100) : 0;

        $nextLesson = Lesson::whereDoesntHave('progress', function ($query) use ($user) {
            $query->where('user_id', $user->id)->where('completed', true);
        })->orderBy('module_id')->orderBy('order')->first();

        return $this->success([
            'progress' => ProgressResource::collection($progress),
            'progress_percentage' => $percentage,
            'completed_count' => $completedCount,
            'total_lessons' => $totalLessons,
            'completed_exercises' => $completedExercises,
            'total_exercises' => $totalExercises,
            'exercise_percentage' => $exercisePercentage,
            'next_lesson' => $nextLesson ? new LessonSummaryResource($nextLesson) : null,
            'current_module' => $nextLesson ? [
                'id' => $nextLesson->module?->id,
                'title' => $nextLesson->module?->title,
                'order' => $nextLesson->module?->order,
            ] : null,
        ], 'User progress retrieved');
    }

    public function update(ProgressUpdateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();

        $progress = Progress::updateOrCreate([
            'user_id' => $user->id,
            'lesson_id' => $data['lesson_id'],
        ], [
            'completed' => $data['completed'],
            'score' => $data['score'] ?? null,
            'completed_at' => $data['completed_at'] ?? now(),
        ]);

        return $this->success(new ProgressResource($progress), 'Progress updated');
    }
}
