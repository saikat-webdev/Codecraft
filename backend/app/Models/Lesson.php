<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'title',
        'slug',
        'description',
        'content',
        'difficulty',
        'estimated_minutes',
        'order',
        'language',
    ];

    protected $casts = [
        'estimated_minutes' => 'integer',
        'order' => 'integer',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function progress()
    {
        return $this->hasMany(Progress::class);
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    public function exercises()
    {
        return $this->hasMany(CodingExercise::class)->orderBy('order');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
