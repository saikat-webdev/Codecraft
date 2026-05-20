<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercise_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('exercise_id')->constrained('coding_exercises')->onDelete('cascade');
            $table->text('submitted_code');
            $table->text('output')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->text('ai_feedback')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'exercise_id']);
            $table->index('is_correct');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercise_submissions');
    }
};