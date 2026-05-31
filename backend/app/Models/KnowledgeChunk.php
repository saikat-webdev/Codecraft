<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgeChunk extends Model
{
    public const TYPE_SITE = 'site';

    public const TYPE_MODULE = 'module';

    public const TYPE_LESSON = 'lesson';

    protected $fillable = [
        'source_type',
        'source_id',
        'chunk_key',
        'track',
        'module_slug',
        'lesson_slug',
        'title',
        'content',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'source_id' => 'integer',
    ];
}
