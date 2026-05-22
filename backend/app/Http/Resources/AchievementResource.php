<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AchievementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'icon' => $this->icon,
            'xp_reward' => $this->xp_reward,
            'criteria_key' => $this->criteria_key,
            'criteria_value' => $this->criteria_value,
            'earned_at' => $this->pivot?->earned_at,
        ];
    }
}
