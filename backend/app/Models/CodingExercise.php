<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodingExercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'title',
        'description',
        'starter_code',
        'expected_output',
        'difficulty',
        'order',
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function submissions()
    {
        return $this->hasMany(ExerciseSubmission::class, 'exercise_id');
    }

    public function userSubmissions()
    {
        return $this->hasMany(ExerciseSubmission::class, 'exercise_id')->where('user_id', auth()->id());
    }
}