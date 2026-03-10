<?php

namespace Tests\Unit\Observers;

use App\Jobs\ProcessAiResponse;
use App\Jobs\TranslateMessage;
use App\Models\Message;
use App\Support\AdminPresence;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class MessageObserverTest extends TestCase
{
    public function test_dispatches_ai_job_for_visitor_message_with_ai_configured(): void
    {
        Queue::fake([ProcessAiResponse::class, TranslateMessage::class]);

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
        Queue::fake([ProcessAiResponse::class, TranslateMessage::class]);

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
        Queue::fake([ProcessAiResponse::class, TranslateMessage::class]);

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
        Queue::fake([ProcessAiResponse::class, TranslateMessage::class]);

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

    public function test_dispatches_ai_job_when_site_key_missing_but_default_key_exists(): void
    {
        Queue::fake([ProcessAiResponse::class, TranslateMessage::class]);
        Config::set('chatpilot.default_ai_key', 'fallback-key');

        $user = $this->createUser();
        $site = $this->createSite($user, [
            'ai_provider' => 'gemini',
            'ai_api_key' => null,
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

    public function test_does_not_dispatch_when_respond_when_offline_disabled(): void
    {
        Queue::fake([ProcessAiResponse::class, TranslateMessage::class]);

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

    public function test_does_not_dispatch_ai_when_admin_is_online(): void
    {
        Queue::fake([ProcessAiResponse::class, TranslateMessage::class]);

        $user = $this->createUser();
        $site = $this->createSite($user, [
            'ai_provider' => 'gemini',
            'ai_api_key' => 'test-key',
            'settings' => ['ai' => ['respond_when_offline' => true]],
        ]);
        $conversation = $this->createConversation($site);

        // Admin is online
        AdminPresence::heartbeat($site->id);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'visitor',
            'text' => 'Hello',
            'created_at' => now(),
        ]);

        Queue::assertNotPushed(ProcessAiResponse::class);
    }

    public function test_dispatches_translation_for_visitor_message(): void
    {
        Queue::fake([ProcessAiResponse::class, TranslateMessage::class]);

        $user = $this->createUser();
        $site = $this->createSite($user, [
            'ai_provider' => 'none',
            'settings' => ['language' => 'en'],
        ]);
        $conversation = $this->createConversation($site);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'visitor',
            'text' => 'Merhaba',
            'created_at' => now(),
        ]);

        Queue::assertPushed(TranslateMessage::class, function ($job) {
            return $job->targetLanguage === 'en';
        });
    }

    public function test_dispatches_translation_for_admin_message(): void
    {
        Queue::fake([ProcessAiResponse::class, TranslateMessage::class]);

        $user = $this->createUser();
        $site = $this->createSite($user, [
            'settings' => ['language' => 'en'],
        ]);
        $conversation = $this->createConversation($site, [
            'metadata' => ['language' => 'tr'],
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'admin',
            'sender_id' => $user->id,
            'text' => 'Hello there',
            'created_at' => now(),
        ]);

        Queue::assertPushed(TranslateMessage::class, function ($job) {
            return $job->targetLanguage === 'tr';
        });
        Queue::assertNotPushed(ProcessAiResponse::class);
    }
}
