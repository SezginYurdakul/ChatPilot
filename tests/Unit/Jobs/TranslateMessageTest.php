<?php

namespace Tests\Unit\Jobs;

use App\Events\MessageTranslated;
use App\Jobs\TranslateMessage;
use App\Models\Message;
use Illuminate\Support\Facades\Event;
use Stichoza\GoogleTranslate\GoogleTranslate;
use Tests\TestCase;

class TranslateMessageTest extends TestCase
{
    public function test_skips_when_target_language_not_in_allowlist(): void
    {
        Event::fake([MessageTranslated::class]);

        $user = $this->createUser();
        $site = $this->createSite($user);
        $conversation = $this->createConversation($site);
        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'language' => 'en',
            'text' => 'Hello',
        ]);

        $job = new TranslateMessage($message, 'xx');
        $job->handle();

        $message->refresh();
        $this->assertNull($message->translations);
        Event::assertNotDispatched(MessageTranslated::class);
    }

    public function test_skips_when_source_language_unknown(): void
    {
        Event::fake([MessageTranslated::class]);

        $user = $this->createUser();
        $site = $this->createSite($user);
        $conversation = $this->createConversation($site);
        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'language' => null,
            'text' => 'Hello',
        ]);

        $job = new TranslateMessage($message, 'tr');
        $job->handle();

        $message->refresh();
        $this->assertNull($message->translations);
        Event::assertNotDispatched(MessageTranslated::class);
    }

    public function test_skips_when_source_equals_target(): void
    {
        Event::fake([MessageTranslated::class]);

        $user = $this->createUser();
        $site = $this->createSite($user);
        $conversation = $this->createConversation($site);
        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'language' => 'en',
            'text' => 'Hello',
        ]);

        $job = new TranslateMessage($message, 'en');
        $job->handle();

        $message->refresh();
        $this->assertNull($message->translations);
        Event::assertNotDispatched(MessageTranslated::class);
    }

    public function test_translates_and_stores_result(): void
    {
        Event::fake([MessageTranslated::class]);

        $user = $this->createUser();
        $site = $this->createSite($user);
        $conversation = $this->createConversation($site);
        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'language' => 'en',
            'text' => 'Hello',
        ]);

        $job = new class($message, 'tr') extends TranslateMessage {
            protected function makeTranslator(string $targetLanguage): GoogleTranslate
            {
                $mock = \Mockery::mock(GoogleTranslate::class);
                $mock->shouldReceive('setSource')->with('en')->once()->andReturnSelf();
                $mock->shouldReceive('translate')->with('Hello')->once()->andReturn('Merhaba');

                return $mock;
            }
        };

        $job->handle();

        $message->refresh();
        $this->assertSame(['tr' => 'Merhaba'], $message->translations);
        Event::assertDispatched(MessageTranslated::class, function (MessageTranslated $event) use ($message) {
            return $event->message->id === $message->id;
        });
    }

    public function test_handles_translation_exception_gracefully(): void
    {
        Event::fake([MessageTranslated::class]);

        $user = $this->createUser();
        $site = $this->createSite($user);
        $conversation = $this->createConversation($site);
        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'language' => 'en',
            'text' => 'Hello',
        ]);

        $job = new class($message, 'tr') extends TranslateMessage {
            protected function makeTranslator(string $targetLanguage): GoogleTranslate
            {
                $mock = \Mockery::mock(GoogleTranslate::class);
                $mock->shouldReceive('setSource')->with('en')->once()->andReturnSelf();
                $mock->shouldReceive('translate')->with('Hello')->once()->andThrow(new \RuntimeException('boom'));

                return $mock;
            }
        };

        $job->handle();

        $message->refresh();
        $this->assertNull($message->translations);
        Event::assertNotDispatched(MessageTranslated::class);
    }
}
