<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LessonSummaryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'difficulty' => $this->difficulty,
            'estimated_minutes' => $this->estimated_minutes,
            'order' => $this->order,
            'module' => $this->whenLoaded('module', function () {
                return [
                    'id' => $this->module->id,
                    'title' => $this->module->title,
                    'slug' => $this->module->slug ?? null,
                    'order' => $this->module->order,
                ];
            }),
        ];
    }
}
