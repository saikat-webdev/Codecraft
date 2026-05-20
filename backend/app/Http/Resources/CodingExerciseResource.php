<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CodingExerciseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'lesson_id' => $this->lesson_id,
            'title' => $this->title,
            'description' => $this->description,
            'starter_code' => $this->starter_code,
            'expected_output' => $this->expected_output,
            'difficulty' => $this->difficulty,
            'order' => $this->order,
            'lesson' => new LessonSummaryResource($this->whenLoaded('lesson')),
        ];
    }
}