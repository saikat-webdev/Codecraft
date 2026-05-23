<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'track' => $this->track,
            'duration_minutes' => $this->duration_minutes,
            'passing_score' => $this->passing_score,
            'is_published' => $this->is_published,
            'order' => $this->order,
            'questions_count' => $this->whenCounted('questions'),
            'questions' => $this->whenLoaded('questions', function () {
                return $this->questions->map(fn ($q) => [
                    'id' => $q->id,
                    'type' => $q->type,
                    'question' => $q->question,
                    'options' => $q->options,
                    'points' => $q->points,
                    'order' => $q->order,
                ]);
            }),
        ];
    }
}
