<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->foreignUuid('site_id')->constrained('sites')->cascadeOnDelete();
            $table->string('provider', 20); // gemini, openai, claude
            $table->string('model', 50); // gemini-2.5-flash-lite
            $table->integer('prompt_tokens');
            $table->integer('completion_tokens');
            $table->integer('response_time_ms');
            $table->text('error')->nullable();
            $table->timestamp('created_at');

            $table->index(['site_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_logs');
    }
};
