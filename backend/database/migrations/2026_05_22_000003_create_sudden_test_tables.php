<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sudden_test_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(false);
            $table->unsignedSmallInteger('min_interval_seconds')->default(180);
            $table->unsignedSmallInteger('max_interval_seconds')->default(480);
            $table->unsignedSmallInteger('default_timer_seconds')->default(90);
            $table->unsignedTinyInteger('base_difficulty')->default(2);
            $table->timestamps();
        });

        Schema::create('sudden_test_questions', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // mcq | coding
            $table->string('title');
            $table->text('prompt');
            $table->json('options')->nullable();
            $table->string('correct_answer')->nullable();
            $table->text('starter_code')->nullable();
            $table->string('expected_output')->nullable();
            $table->string('language')->default('python');
            $table->unsignedTinyInteger('difficulty')->default(2);
            $table->unsignedSmallInteger('time_limit_seconds')->default(90);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('sudden_test_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sudden_test_question_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('difficulty_at_attempt');
            $table->boolean('passed')->default(false);
            $table->unsignedSmallInteger('time_taken_seconds')->nullable();
            $table->unsignedSmallInteger('score')->default(0);
            $table->text('response')->nullable();
            $table->unsignedInteger('xp_awarded')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sudden_test_attempts');
        Schema::dropIfExists('sudden_test_questions');
        Schema::dropIfExists('sudden_test_settings');
    }
};
