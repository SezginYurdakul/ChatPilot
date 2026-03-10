<?php

namespace Tests\Unit\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Site;
use App\Services\AiService;
use RuntimeException;
use Tests\TestCase;

class AiServiceTest extends TestCase
{
    public function test_resolve_throws_when_no_provider_configured(): void
    {
        $user = $this->createUser();
        $site = $this->createSite($user, [
            'ai_provider' => 'none',
            'ai_api_key' => null,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No AI provider configured');

        (new AiService)->resolve($site);
    }

    public function test_resolve_throws_for_unknown_provider(): void
    {
        $user = $this->createUser();
        $site = $this->createSite($user, [
            'ai_provider' => 'nonexistent_provider',
            'ai_api_key' => 'test-key',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown AI provider');

        (new AiService)->resolve($site);
    }

    public function test_resolve_returns_gemini_provider(): void
    {
        $user = $this->createUser();
        $site = $this->createSite($user, [
            'ai_provider' => 'gemini',
            'ai_api_key' => 'test-gemini-key',
        ]);

        $provider = (new AiService)->resolve($site);

        $this->assertInstanceOf(\App\Services\Ai\GeminiProvider::class, $provider);
    }

    public function test_resolve_returns_openai_provider(): void
    {
        $user = $this->createUser();
        $site = $this->createSite($user, [
            'ai_provider' => 'openai',
            'ai_api_key' => 'test-openai-key',
        ]);

        $provider = (new AiService)->resolve($site);

        $this->assertInstanceOf(\App\Services\Ai\OpenAiProvider::class, $provider);
    }

    public function test_build_history_returns_formatted_messages(): void
    {
        $user = $this->createUser();
        $site = $this->createSite($user);
        $conversation = $this->createConversation($site);

        Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'visitor',
            'text' => 'Hello',
            'created_at' => now()->subMinutes(2),
        ]);

        Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'ai',
            'text' => 'Hi there!',
            'created_at' => now()->subMinute(),
        ]);

        $history = (new AiService)->buildHistory($site, $conversation);

        $this->assertCount(2, $history);
        $this->assertEquals('user', $history[0]['role']);
        $this->assertEquals('Hello', $history[0]['content']);
        $this->assertEquals('assistant', $history[1]['role']);
        $this->assertEquals('Hi there!', $history[1]['content']);
    }

    public function test_build_history_limits_messages(): void
    {
        $user = $this->createUser();
        $site = $this->createSite($user, [
            'settings' => ['ai' => ['max_history_messages' => 3]],
        ]);
        $conversation = $this->createConversation($site);

        for ($i = 0; $i < 10; $i++) {
            Message::factory()->create([
                'conversation_id' => $conversation->id,
                'sender_type' => 'visitor',
                'created_at' => now()->subMinutes(10 - $i),
            ]);
        }

        $history = (new AiService)->buildHistory($site, $conversation);

        $this->assertCount(3, $history);
    }
}
