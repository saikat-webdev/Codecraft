<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ModuleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'track' => $this->track ?? 'python',
            'icon' => $this->icon,
            'description' => $this->description,
            'order' => $this->order,
            'lessons' => LessonSummaryResource::collection($this->whenLoaded('lessons')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
