<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'content',
        'difficulty',
        'language',
    ];

    public function progress()
    {
        return $this->hasMany(Progress::class);
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
