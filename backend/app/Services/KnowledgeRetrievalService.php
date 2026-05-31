<?php

namespace App\Services;

use App\Models\KnowledgeChunk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class KnowledgeRetrievalService
{
    public function __construct(
        protected GeminiEmbeddingService $embedder,
    ) {}

    public function isAvailable(): bool
    {
        return DB::getDriverName() === 'pgsql'
            && Schema::hasTable('knowledge_chunks')
            && $this->embedder->isConfigured()
            && KnowledgeChunk::query()->whereRaw('embedding IS NOT NULL')->exists();
    }

    /**
     * @return array<int, object>
     */
    public function search(string $query, ?int $topK = null): array
    {
        if (! $this->isAvailable()) {
            return [];
        }

        $embedding = $this->embedder->embed(
            $query,
            GeminiEmbeddingService::TASK_RETRIEVAL_QUERY
        );
        if ($embedding === null) {
            return [];
        }

        $topK = $topK ?? (int) config('services.knowledge.top_k', 5);
        $minSimilarity = (float) config('services.knowledge.min_similarity', 0.35);
        $vector = $this->embedder->toPgVector($embedding);

        try {
            $rows = DB::select(
                'SELECT id, source_type, title, content, track, module_slug, lesson_slug, metadata,
                        (1 - (embedding <=> ?::vector)) AS similarity
                 FROM knowledge_chunks
                 WHERE embedding IS NOT NULL
                 ORDER BY embedding <=> ?::vector
                 LIMIT ?',
                [$vector, $vector, $topK]
            );

            return array_values(array_filter(
                $rows,
                fn ($row) => (float) $row->similarity >= $minSimilarity
            ));
        } catch (\Throwable $e) {
            Log::warning('Knowledge vector search failed', ['message' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * @param  array<int, object>  $rows
     */
    public function formatContext(array $rows): string
    {
        if ($rows === []) {
            return '';
        }

        $blocks = ["=== CodeCraft knowledge (from database) ==="];

        foreach ($rows as $index => $row) {
            $label = $row->title ?: ($row->source_type ?? 'chunk');
            $path = array_filter([
                $row->track ? "track:{$row->track}" : null,
                $row->module_slug ? "module:{$row->module_slug}" : null,
                $row->lesson_slug ? "lesson:{$row->lesson_slug}" : null,
            ]);
            $location = $path !== [] ? ' ('.implode(', ', $path).')' : '';

            $blocks[] = sprintf(
                "[%d] %s%s\n%s",
                $index + 1,
                $label,
                $location,
                mb_substr((string) $row->content, 0, 1200)
            );
        }

        $blocks[] = 'Use the above CodeCraft catalog/lesson facts when answering questions about the site, modules, or lessons. If the user asks what to study next, suggest modules/lessons from this data.';

        return implode("\n\n", $blocks);
    }

    public function buildContextForMessage(string $message): string
    {
        if (! config('services.knowledge.enabled', true)) {
            return '';
        }

        $rows = $this->search($message);

        return $this->formatContext($rows);
    }
}
