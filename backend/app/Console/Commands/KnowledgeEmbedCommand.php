<?php

namespace App\Console\Commands;

use App\Services\KnowledgeIndexer;
use Illuminate\Console\Command;

class KnowledgeEmbedCommand extends Command
{
    protected $signature = 'knowledge:embed
                            {--fresh : Delete all existing chunks before re-indexing}';

    protected $description = 'Embed CodeCraft site catalog, modules, and lessons into PostgreSQL (pgvector) for AI Instructor RAG';

    public function handle(KnowledgeIndexer $indexer): int
    {
        try {
            $indexer->assertPostgresWithPgVector();
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Building knowledge chunks from modules & lessons…');

        $count = $indexer->index(
            fresh: (bool) $this->option('fresh'),
            onProgress: fn (string $message) => $this->line('  '.$message)
        );

        $this->newLine();
        $this->info("Done. Indexed {$count} chunks with embeddings.");

        $this->line('Test retrieval by asking the AI Instructor about modules or lessons on /ai');

        return self::SUCCESS;
    }
}
