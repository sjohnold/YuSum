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
        Schema::create('summary_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('summary_id')->constrained()->onDelete('cascade');
            $table->integer('index');
            $table->longText('content');
            $table->longText('summary')->nullable();
            $table->integer('tokens_used')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('summary_chunks');
    }
};
