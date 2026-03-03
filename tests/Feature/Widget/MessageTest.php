<?php

namespace Tests\Feature\Widget;

use App\Events\MessageSent;
use App\Models\Message;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class MessageTest extends TestCase
{
    public function test_send_visitor_message(): void
    {
        Event::fake([MessageSent::class]);

        $user = $this->createUser();
        $site = $this->createSite($user);
        $conversation = $this->createConversation($site);

        $response = $this->postJson(
            "/api/v1/conversations/{$conversation->id}/messages",
            ['text' => 'Hello!'],
            $this->widgetHeaders($site->api_key, $conversation->visitor_token),
        );

        $response->assertStatus(201)
            ->assertJsonPath('message.text', 'Hello!')
            ->assertJsonPath('message.sender_type', 'visitor');

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_type' => 'visitor',
            'text' => 'Hello!',
        ]);

        Event::assertDispatched(MessageSent::class);
    }

    public function test_get_messages(): void
    {
        $user = $this->createUser();
        $site = $this->createSite($user);
        $conversation = $this->createConversation($site);

        Message::factory()->count(3)->create([
            'conversation_id' => $conversation->id,
        ]);

        $response = $this->getJson(
            "/api/v1/conversations/{$conversation->id}/messages",
            $this->widgetHeaders($site->api_key, $conversation->visitor_token),
        );

        $response->assertOk()
            ->assertJsonCount(3, 'messages');
    }

    public function test_get_messages_with_after_filter(): void
    {
        $user = $this->createUser();
        $site = $this->createSite($user);
        $conversation = $this->createConversation($site);

        $oldMessage = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'created_at' => now()->subMinutes(5),
        ]);

        Message::factory()->count(2)->create([
            'conversation_id' => $conversation->id,
            'created_at' => now(),
        ]);

        $response = $this->getJson(
            "/api/v1/conversations/{$conversation->id}/messages?after={$oldMessage->id}",
            $this->widgetHeaders($site->api_key, $conversation->visitor_token),
        );

        $response->assertOk()
            ->assertJsonCount(2, 'messages');
    }

    public function test_send_message_without_visitor_token_returns_401(): void
    {
        $user = $this->createUser();
        $site = $this->createSite($user);
        $conversation = $this->createConversation($site);

        $response = $this->postJson(
            "/api/v1/conversations/{$conversation->id}/messages",
            ['text' => 'Hello!'],
            ['X-Site-Key' => $site->api_key, 'Accept' => 'application/json'],
        );

        $response->assertStatus(401)
            ->assertJsonPath('error', 'missing_visitor_token');
    }

    public function test_send_message_with_wrong_visitor_token_returns_403(): void
    {
        $user = $this->createUser();
        $site = $this->createSite($user);
        $conversation = $this->createConversation($site);

        $response = $this->postJson(
            "/api/v1/conversations/{$conversation->id}/messages",
            ['text' => 'Hello!'],
            $this->widgetHeaders($site->api_key, 'wrong-token'),
        );

        $response->assertStatus(403)
            ->assertJsonPath('error', 'invalid_visitor_token');
    }

    public function test_send_message_validation_requires_text(): void
    {
        $user = $this->createUser();
        $site = $this->createSite($user);
        $conversation = $this->createConversation($site);

        $response = $this->postJson(
            "/api/v1/conversations/{$conversation->id}/messages",
            [],
            $this->widgetHeaders($site->api_key, $conversation->visitor_token),
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['text']);
    }

    public function test_send_message_text_max_length(): void
    {
        $user = $this->createUser();
        $site = $this->createSite($user);
        $conversation = $this->createConversation($site);

        $response = $this->postJson(
            "/api/v1/conversations/{$conversation->id}/messages",
            ['text' => str_repeat('a', 1001)],
            $this->widgetHeaders($site->api_key, $conversation->visitor_token),
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['text']);
    }
}
