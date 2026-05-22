<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'avatar_path')) {
                $table->string('avatar_path')->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'bio')) {
                $table->text('bio')->nullable();
            }
            if (! Schema::hasColumn('users', 'is_admin')) {
                $table->boolean('is_admin')->default(false);
            }
            if (! Schema::hasColumn('users', 'learning_goal')) {
                $table->string('learning_goal')->nullable();
            }
            if (! Schema::hasColumn('users', 'preferred_language')) {
                $table->string('preferred_language')->nullable();
            }
            if (! Schema::hasColumn('users', 'daily_learning_time')) {
                $table->unsignedSmallInteger('daily_learning_time')->nullable();
            }
            if (! Schema::hasColumn('users', 'skill_level')) {
                $table->string('skill_level')->nullable();
            }
            if (! Schema::hasColumn('users', 'xp')) {
                $table->unsignedInteger('xp')->default(0);
            }
            if (! Schema::hasColumn('users', 'level')) {
                $table->unsignedSmallInteger('level')->default(1);
            }
            if (! Schema::hasColumn('users', 'streak_count')) {
                $table->unsignedInteger('streak_count')->default(0);
            }
            if (! Schema::hasColumn('users', 'longest_streak')) {
                $table->unsignedInteger('longest_streak')->default(0);
            }
            if (! Schema::hasColumn('users', 'last_activity_date')) {
                $table->date('last_activity_date')->nullable();
            }
            if (! Schema::hasColumn('users', 'sudden_test_difficulty')) {
                $table->unsignedTinyInteger('sudden_test_difficulty')->default(2);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'avatar_path', 'bio', 'is_admin', 'xp', 'level',
                'streak_count', 'longest_streak', 'last_activity_date',
                'sudden_test_difficulty',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
