<?php

namespace Tests\Unit\Observers;

use App\Jobs\ProcessAiResponse;
use App\Models\Message;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class MessageObserverTest extends TestCase
{
    public function test_dispatches_ai_job_for_visitor_message_with_ai_configured(): void
    {
        Queue::fake([ProcessAiResponse::class]);

        $user = $this->createUser();
        $site = $this->createSite($user, [
            'ai_provider' => 'gemini',
            'ai_api_key' => 'test-key',
            'settings' => ['ai' => ['respond_when_offline' => true]],
        ]);
        $conversation = $this->createConversation($site);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'visitor',
            'text' => 'Hello',
            'created_at' => now(),
        ]);

        Queue::assertPushed(ProcessAiResponse::class);
    }

    public function test_does_not_dispatch_for_admin_messages(): void
    {
        Queue::fake([ProcessAiResponse::class]);

        $user = $this->createUser();
        $site = $this->createSite($user, [
            'ai_provider' => 'gemini',
            'ai_api_key' => 'test-key',
        ]);
        $conversation = $this->createConversation($site);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'admin',
            'sender_id' => $user->id,
            'text' => 'Admin reply',
            'created_at' => now(),
        ]);

        Queue::assertNotPushed(ProcessAiResponse::class);
    }

    public function test_does_not_dispatch_for_ai_messages(): void
    {
        Queue::fake([ProcessAiResponse::class]);

        $user = $this->createUser();
        $site = $this->createSite($user, [
            'ai_provider' => 'gemini',
            'ai_api_key' => 'test-key',
        ]);
        $conversation = $this->createConversation($site);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'ai',
            'text' => 'AI response',
            'created_at' => now(),
        ]);

        Queue::assertNotPushed(ProcessAiResponse::class);
    }

    public function test_does_not_dispatch_when_no_ai_provider(): void
    {
        Queue::fake([ProcessAiResponse::class]);

        $user = $this->createUser();
        $site = $this->createSite($user, [
            'ai_provider' => 'none',
            'ai_api_key' => null,
        ]);
        $conversation = $this->createConversation($site);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'visitor',
            'text' => 'Hello',
            'created_at' => now(),
        ]);

        Queue::assertNotPushed(ProcessAiResponse::class);
    }

    public function test_does_not_dispatch_when_respond_when_offline_disabled(): void
    {
        Queue::fake([ProcessAiResponse::class]);

        $user = $this->createUser();
        $site = $this->createSite($user, [
            'ai_provider' => 'gemini',
            'ai_api_key' => 'test-key',
            'settings' => ['ai' => ['respond_when_offline' => false]],
        ]);
        $conversation = $this->createConversation($site);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'visitor',
            'text' => 'Hello',
            'created_at' => now(),
        ]);

        Queue::assertNotPushed(ProcessAiResponse::class);
    }
}
