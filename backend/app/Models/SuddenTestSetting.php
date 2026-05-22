<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuddenTestSetting extends Model
{
    protected $fillable = [
        'enabled', 'min_interval_seconds', 'max_interval_seconds',
        'default_timer_seconds', 'base_difficulty',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public static function current(): self
    {
        return static::firstOrCreate([], [
            'enabled' => true,
            'min_interval_seconds' => 60,
            'max_interval_seconds' => 120,
            'default_timer_seconds' => 90,
            'base_difficulty' => 2,
        ]);
    }
}
