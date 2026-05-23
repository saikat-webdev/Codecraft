<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamAttemptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'exam' => $this->whenLoaded('exam', fn () => [
                'id' => $this->exam->id,
                'title' => $this->exam->title,
                'slug' => $this->exam->slug,
                'track' => $this->exam->track,
            ]),
            'score' => $this->score,
            'max_score' => $this->max_score,
            'percentage' => $this->percentage,
            'passed' => $this->passed,
            'answers' => $this->answers,
            'started_at' => $this->started_at?->toDateTimeString(),
            'completed_at' => $this->completed_at?->toDateTimeString(),
        ];
    }
}
