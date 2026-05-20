<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CodingExerciseResource;
use App\Models\CodingExercise;
use App\Models\Lesson;
use Illuminate\Http\Request;

class ExerciseController extends Controller
{
    public function index(Request $request)
    {
        $query = CodingExercise::query();

        if ($request->has('lesson_id')) {
            $query->where('lesson_id', $request->lesson_id);
        }

        $exercises = $query->orderBy('order')->paginate(20);

        return CodingExerciseResource::collection($exercises);
    }

    public function show(CodingExercise $exercise)
    {
        return new CodingExerciseResource($exercise);
    }

    public function byLesson(Lesson $lesson)
    {
        $exercises = $lesson->exercises()->orderBy('order')->get();

        return CodingExerciseResource::collection($exercises);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'starter_code' => 'nullable|string',
            'expected_output' => 'nullable|string',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'order' => 'nullable|integer',
        ]);

        $exercise = CodingExercise::create($validated);

        return new CodingExerciseResource($exercise);
    }
}