<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->foreignId('module_id')->nullable()->constrained('modules')->nullOnDelete()->after('id');
            $table->integer('estimated_minutes')->default(10)->after('difficulty');
            $table->integer('order')->default(0)->after('estimated_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropForeign(['module_id']);
            $table->dropColumn(['module_id', 'estimated_minutes', 'order']);
        });
    }
};
