<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class QuizResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'lesson_id' => $this->lesson_id,
            'question' => $this->question,
            'options' => $this->options,
            'explanation' => $this->explanation,
        ];
    }
}
