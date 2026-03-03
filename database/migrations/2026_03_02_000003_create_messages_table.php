<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->string('sender_type', 10); // visitor, admin, ai
            $table->uuid('sender_id')->nullable(); // admin user id (null for visitor/ai)
            $table->text('text');
            $table->string('language', 5)->nullable(); // "en", "nl", "tr"
            $table->jsonb('translations')->nullable(); // {"en": "Hello", "nl": "Hallo"}
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at');

            $table->index(['conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
