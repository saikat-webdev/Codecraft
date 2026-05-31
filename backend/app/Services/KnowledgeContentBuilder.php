<?php

namespace App\Services;

use App\Models\KnowledgeChunk;
use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Support\Str;

class KnowledgeContentBuilder
{
    public function __construct(
        protected int $chunkSize = 1500,
        protected int $chunkOverlap = 200,
    ) {
        $this->chunkSize = (int) config('services.knowledge.chunk_size', 1500);
        $this->chunkOverlap = (int) config('services.knowledge.chunk_overlap', 200);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function buildAll(): array
    {
        $chunks = [];

        foreach ($this->buildSiteCatalogChunk() as $chunk) {
            $chunks[] = $chunk;
        }

        $modules = Module::query()
            ->with(['lessons' => fn ($q) => $q->orderBy('order')])
            ->orderBy('track')
            ->orderBy('order')
            ->get();

        foreach ($modules as $module) {
            foreach ($this->buildModuleChunks($module) as $chunk) {
                $chunks[] = $chunk;
            }

            foreach ($module->lessons as $lesson) {
                foreach ($this->buildLessonChunks($lesson, $module) as $chunk) {
                    $chunks[] = $chunk;
                }
            }
        }

        return $chunks;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function buildSiteCatalogChunk(): array
    {
        $tracks = Module::query()
            ->select('track')
            ->distinct()
            ->orderBy('track')
            ->pluck('track')
            ->filter()
            ->values();

        $lines = [
            'CodeCraft is a beginner-friendly coding education platform.',
            'Tracks available: '.($tracks->isEmpty() ? 'Python, JavaScript, Java, C, React' : $tracks->implode(', ')),
            'Each track contains modules; each module contains ordered lessons with quizzes and coding exercises.',
            'Users can use the Playground, Roadmap, Lessons, Exams, and AI Instructor.',
        ];

        $modules = Module::query()
            ->withCount('lessons')
            ->orderBy('track')
            ->orderBy('order')
            ->get();

        foreach ($modules as $module) {
            $lines[] = sprintf(
                '- Track "%s" | Module "%s" (slug: %s) | %d lessons | %s',
                $module->track ?? 'general',
                $module->title,
                $module->slug,
                $module->lessons_count,
                Str::limit($this->plainText($module->description ?? ''), 120)
            );
        }

        return [[
            'source_type' => KnowledgeChunk::TYPE_SITE,
            'source_id' => null,
            'chunk_key' => 'catalog:site',
            'track' => null,
            'module_slug' => null,
            'lesson_slug' => null,
            'title' => 'CodeCraft site catalog',
            'content' => implode("\n", $lines),
            'metadata' => ['kind' => 'catalog'],
        ]];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function buildModuleChunks(Module $module): array
    {
        $lessonList = $module->lessons
            ->map(fn (Lesson $lesson) => sprintf(
                '  - Lesson "%s" (slug: %s, order: %d, difficulty: %s)',
                $lesson->title,
                $lesson->slug,
                $lesson->order,
                $lesson->difficulty ?? 'beginner'
            ))
            ->implode("\n");

        $content = implode("\n", array_filter([
            "Module: {$module->title}",
            'Track: '.($module->track ?? 'general'),
            "Slug: {$module->slug}",
            'Description: '.$this->plainText($module->description ?? ''),
            'Lessons in this module (in order):',
            $lessonList ?: '  (no lessons yet)',
        ]));

        return [[
            'source_type' => KnowledgeChunk::TYPE_MODULE,
            'source_id' => $module->id,
            'chunk_key' => "module:{$module->id}",
            'track' => $module->track,
            'module_slug' => $module->slug,
            'lesson_slug' => null,
            'title' => $module->title,
            'content' => $content,
            'metadata' => [
                'module_id' => $module->id,
                'lesson_count' => $module->lessons->count(),
            ],
        ]];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function buildLessonChunks(Lesson $lesson, Module $module): array
    {
        $header = implode("\n", [
            "Lesson: {$lesson->title}",
            "Module: {$module->title} (slug: {$module->slug})",
            'Track: '.($module->track ?? 'general'),
            "Lesson slug: {$lesson->slug}",
            'Difficulty: '.($lesson->difficulty ?? 'beginner'),
            'Estimated minutes: '.($lesson->estimated_minutes ?? 'n/a'),
            'Summary: '.$this->plainText($lesson->description ?? ''),
            'Content:',
        ]);

        $body = $this->plainText($lesson->content ?? '');
        $parts = $this->splitText($body);

        $chunks = [];
        foreach ($parts as $index => $part) {
            $chunks[] = [
                'source_type' => KnowledgeChunk::TYPE_LESSON,
                'source_id' => $lesson->id,
                'chunk_key' => "lesson:{$lesson->id}:{$index}",
                'track' => $module->track,
                'module_slug' => $module->slug,
                'lesson_slug' => $lesson->slug,
                'title' => $lesson->title,
                'content' => $header."\n".$part,
                'metadata' => [
                    'lesson_id' => $lesson->id,
                    'module_id' => $module->id,
                    'chunk_index' => $index,
                ],
            ];
        }

        return $chunks;
    }

    /**
     * @return array<int, string>
     */
    protected function splitText(string $text): array
    {
        if ($text === '') {
            return ['(No lesson body text yet.)'];
        }

        if (mb_strlen($text) <= $this->chunkSize) {
            return [$text];
        }

        $chunks = [];
        $start = 0;
        $length = mb_strlen($text);

        while ($start < $length) {
            $piece = mb_substr($text, $start, $this->chunkSize);
            $chunks[] = trim($piece);
            $start += max(1, $this->chunkSize - $this->chunkOverlap);
        }

        return array_values(array_filter($chunks));
    }

    protected function plainText(string $html): string
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }
}
