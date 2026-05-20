<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\LessonStoreRequest;
use App\Http\Resources\LessonResource;
use App\Models\Lesson;
use Illuminate\Http\JsonResponse;

class LessonController extends BaseApiController
{
    public function index(): JsonResponse
    {
        $lessons = Lesson::with('module')
            ->orderBy('module_id')
            ->orderBy('order')
            ->get();

        return $this->success(LessonResource::collection($lessons), 'Lessons retrieved');
    }

    public function show(Lesson $lesson): JsonResponse
    {
        $lesson->load('module');

        return $this->success(new LessonResource($lesson), 'Lesson retrieved');
    }

    public function store(LessonStoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        $lesson = Lesson::create($data);

        return $this->success(new LessonResource($lesson), 'Lesson created', 201);
    }
}
