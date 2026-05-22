<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileStatsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'lessons_completed' => $this->resource['lessons_completed'] ?? 0,
            'exercises_passed' => $this->resource['exercises_passed'] ?? 0,
            'sudden_tests_passed' => $this->resource['sudden_tests_passed'] ?? 0,
            'streak_days' => $this->resource['streak_days'] ?? 0,
            'total_xp' => $this->resource['total_xp'] ?? 0,
            'level_reached' => $this->resource['level_reached'] ?? 1,
            'total_submissions' => $this->resource['total_submissions'] ?? 0,
            'sudden_test_stats' => $this->resource['sudden_test_stats'] ?? [],
        ];
    }
}
