<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuddenTestAttempt extends Model
{
    protected $fillable = [
        'user_id', 'sudden_test_question_id', 'difficulty_at_attempt',
        'passed', 'time_taken_seconds', 'score', 'response', 'xp_awarded',
    ];

    protected $casts = [
        'passed' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(SuddenTestQuestion::class, 'sudden_test_question_id');
    }
}
