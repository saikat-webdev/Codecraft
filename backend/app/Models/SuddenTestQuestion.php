<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SuddenTestQuestion extends Model
{
    protected $fillable = [
        'type', 'title', 'prompt', 'options', 'correct_answer',
        'starter_code', 'expected_output', 'language', 'difficulty',
        'time_limit_seconds', 'is_active',
    ];

    protected $casts = [
        'options' => 'array',
        'is_active' => 'boolean',
    ];

    public function attempts(): HasMany
    {
        return $this->hasMany(SuddenTestAttempt::class);
    }
}
