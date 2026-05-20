<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'module_id' => $this->module_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'content' => $this->content,
            'difficulty' => $this->difficulty,
            'estimated_minutes' => $this->estimated_minutes,
            'order' => $this->order,
            'language' => $this->language,
            'module' => $this->whenLoaded('module', function () {
                return $this->module ? [
                    'id' => $this->module->id,
                    'title' => $this->module->title,
                    'slug' => $this->module->slug,
                    'order' => $this->module->order,
                ] : null;
            }),
            'exercises' => $this->whenLoaded('exercises', function () {
                return CodingExerciseResource::collection($this->exercises);
            }),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
