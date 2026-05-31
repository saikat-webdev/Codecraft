<?php

namespace App\Services;

use App\Models\KnowledgeChunk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KnowledgeIndexer
{
    public function __construct(
        protected KnowledgeContentBuilder $builder,
        protected GeminiEmbeddingService $embedder,
    ) {}

    public function assertPostgresWithPgVector(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            throw new \RuntimeException('knowledge:embed requires PostgreSQL (DB_CONNECTION=pgsql).');
        }

        if (! Schema::hasTable('knowledge_chunks')) {
            throw new \RuntimeException('Run php artisan migrate first to create knowledge_chunks.');
        }
    }

    /**
     * @param  callable|null  $onProgress  fn(string $message): void
     */
    public function index(bool $fresh = false, ?callable $onProgress = null): int
    {
        $this->assertPostgresWithPgVector();

        if (! $this->embedder->isConfigured()) {
            throw new \RuntimeException('Set GEMINI_API_KEY in .env before embedding.');
        }

        if ($fresh) {
            KnowledgeChunk::query()->delete();
            $onProgress !== null && $onProgress('Cleared existing knowledge chunks.');
        }

        $definitions = $this->builder->buildAll();
        $indexed = 0;
        $delayMs = (int) config('services.knowledge.embed_delay_ms', 150);

        foreach ($definitions as $definition) {
            $onProgress !== null && $onProgress("Embedding {$definition['chunk_key']}…");

            $embedding = $this->embedder->embed(
                $definition['content'],
                GeminiEmbeddingService::TASK_RETRIEVAL_DOCUMENT
            );
            if ($embedding === null) {
                $onProgress !== null && $onProgress("  Skipped (embedding failed): {$definition['chunk_key']}");

                continue;
            }

            $chunk = KnowledgeChunk::query()->updateOrCreate(
                ['chunk_key' => $definition['chunk_key']],
                [
                    'source_type' => $definition['source_type'],
                    'source_id' => $definition['source_id'],
                    'track' => $definition['track'],
                    'module_slug' => $definition['module_slug'],
                    'lesson_slug' => $definition['lesson_slug'],
                    'title' => $definition['title'],
                    'content' => $definition['content'],
                    'metadata' => $definition['metadata'],
                ]
            );

            $vector = $this->embedder->toPgVector($embedding);
            DB::update(
                'UPDATE knowledge_chunks SET embedding = ?::vector, updated_at = NOW() WHERE id = ?',
                [$vector, $chunk->id]
            );

            $indexed++;
            if ($delayMs > 0) {
                usleep($delayMs * 1000);
            }
        }

        return $indexed;
    }
}
