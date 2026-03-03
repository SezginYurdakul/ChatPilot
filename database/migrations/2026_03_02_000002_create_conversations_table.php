<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained('sites')->cascadeOnDelete();
            $table->string('visitor_token', 64);
            $table->string('visitor_name')->nullable();
            $table->string('status', 20)->default('active'); // active, closed, archived
            $table->jsonb('metadata')->nullable(); // ip, user_agent, page_url, language
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'status', 'last_message_at']);
            $table->index('visitor_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
