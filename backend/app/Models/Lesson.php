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

    /**
     * Normalize legacy content that stored literal \n in code blocks.
     */
    public function getContentAttribute(?string $value): ?string
    {
        if ($value === null || !str_contains($value, '<pre')) {
            return $value;
        }

        return preg_replace_callback(
            '/<pre><code>(.*?)<\/code><\/pre>/s',
            function (array $matches) {
                $inner = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $inner = str_replace(["\\n", "\\t"], ["\n", "\t"], $inner);

                return '<pre><code>' . htmlspecialchars($inner, ENT_NOQUOTES, 'UTF-8') . '</code></pre>';
            },
            $value
        );
    }
}
