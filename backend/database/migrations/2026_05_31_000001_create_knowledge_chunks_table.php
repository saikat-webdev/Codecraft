<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('CREATE EXTENSION IF NOT EXISTS vector');

        Schema::create('knowledge_chunks', function (Blueprint $table) {
            $table->id();
            $table->string('source_type', 32);
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('chunk_key')->unique();
            $table->string('track')->nullable()->index();
            $table->string('module_slug')->nullable()->index();
            $table->string('lesson_slug')->nullable()->index();
            $table->string('title')->nullable();
            $table->text('content');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $dimensions = (int) config('services.gemini.embedding_dimensions', 768);

        DB::statement("ALTER TABLE knowledge_chunks ADD COLUMN embedding vector({$dimensions})");

        try {
            DB::statement(
                'CREATE INDEX knowledge_chunks_embedding_idx ON knowledge_chunks USING hnsw (embedding vector_cosine_ops)'
            );
        } catch (\Throwable) {
            DB::statement(
                'CREATE INDEX knowledge_chunks_embedding_idx ON knowledge_chunks USING ivfflat (embedding vector_cosine_ops) WITH (lists = 100)'
            );
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        Schema::dropIfExists('knowledge_chunks');
    }
};
