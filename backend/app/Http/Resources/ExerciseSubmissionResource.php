<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExerciseSubmissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'exercise_id' => $this->exercise_id,
            'submitted_code' => $this->submitted_code,
            'output' => $this->output,
            'is_correct' => $this->is_correct,
            'ai_feedback' => $this->ai_feedback,
            'created_at' => $this->created_at,
            'exercise' => new CodingExerciseResource($this->whenLoaded('exercise')),
        ];
    }
}