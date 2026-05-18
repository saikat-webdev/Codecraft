<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ProgressUpdateRequest;
use App\Http\Resources\ProgressResource;
use App\Models\Progress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgressController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $progress = Progress::where('user_id', $user->id)->with('lesson')->get();

        return $this->success(ProgressResource::collection($progress), 'User progress retrieved');
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
            'completed_at' => $data['completed_at'] ?? null,
        ]);

        return $this->success(new ProgressResource($progress), 'Progress updated');
    }
}
