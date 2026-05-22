<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Safe payload for learners — omits correct answers for MCQ. */
class SuddenTestQuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'prompt' => $this->prompt,
            'options' => $this->type === 'mcq' ? $this->options : null,
            'starter_code' => $this->type === 'coding' ? $this->starter_code : null,
            'language' => $this->language,
            'difficulty' => $this->difficulty,
            'time_limit_seconds' => $this->time_limit_seconds,
        ];
    }
}
