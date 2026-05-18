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
        $lessons = Lesson::orderBy('created_at', 'desc')->paginate(20);

        return $this->success(LessonResource::collection($lessons), 'Lessons retrieved');
    }

    public function show(Lesson $lesson): JsonResponse
    {
        return $this->success(new LessonResource($lesson), 'Lesson retrieved');
    }

    public function store(LessonStoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        $lesson = Lesson::create($data);

        return $this->success(new LessonResource($lesson), 'Lesson created', 201);
    }
}
