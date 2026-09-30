<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('summaries', function (Blueprint $table) {
            if (!Schema::hasColumn('summaries', 'prompt_mode')) {
                $table->string('prompt_mode', 50)->default('general')->after('tokens_used');
            }
            if (!Schema::hasColumn('summaries', 'language')) {
                $table->string('language', 10)->default('ru')->after('prompt_mode');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('summaries', function (Blueprint $table) {
            $table->dropColumn(['prompt_mode', 'language']);
        });
    }
};
