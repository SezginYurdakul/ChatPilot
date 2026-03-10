<?php

namespace Tests\Feature\Admin;

use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Models\Message;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ConversationMessageTest extends TestCase
{
    public function test_send_admin_message(): void
    {
        Event::fake([MessageSent::class]);

        [$user, $token] = $this->createAuthenticatedUser();
        $site = $this->createSite($user);
        $conversation = $this->createConversation($site);

        $response = $this->postJson(
            "/api/v1/admin/conversations/{$conversation->id}/messages",
            ['text' => 'Hello from admin!'],
            $this->authHeaders($token),
        );

        $response->assertStatus(201)
            ->assertJsonPath('message.text', 'Hello from admin!')
            ->assertJsonPath('message.sender_type', 'admin')
            ->assertJsonPath('message.sender_id', $user->id);

        Event::assertDispatched(MessageSent::class);
    }

    public function test_send_admin_message_validates_text(): void
    {
        [$user, $token] = $this->createAuthenticatedUser();
        $site = $this->createSite($user);
        $conversation = $this->createConversation($site);

        $response = $this->postJson(
            "/api/v1/admin/conversations/{$conversation->id}/messages",
            [],
            $this->authHeaders($token),
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['text']);
    }

    public function test_send_admin_message_to_other_users_conversation_returns_403(): void
    {
        [$user, $token] = $this->createAuthenticatedUser();

        $otherUser = $this->createUser();
        $otherSite = $this->createSite($otherUser);
        $otherConversation = $this->createConversation($otherSite);

        $response = $this->postJson(
            "/api/v1/admin/conversations/{$otherConversation->id}/messages",
            ['text' => 'Sneaky message'],
            $this->authHeaders($token),
        );

        $response->assertStatus(403);
    }

    public function test_mark_messages_as_read(): void
    {
        Event::fake([MessageRead::class]);

        [$user, $token] = $this->createAuthenticatedUser();
        $site = $this->createSite($user);
        $conversation = $this->createConversation($site);

        // Create unread visitor messages
        Message::factory()->count(3)->create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'visitor',
            'read_at' => null,
        ]);

        // Create an admin message (should not be affected)
        Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'admin',
            'sender_id' => $user->id,
            'read_at' => null,
        ]);

        $response = $this->postJson(
            "/api/v1/admin/conversations/{$conversation->id}/read",
            [],
            $this->authHeaders($token),
        );

        $response->assertOk()
            ->assertJsonPath('message', 'Messages marked as read.');

        // All visitor messages should be marked as read
        $this->assertEquals(
            0,
            Message::where('conversation_id', $conversation->id)
                ->where('sender_type', 'visitor')
                ->whereNull('read_at')
                ->count()
        );

        // Admin message should still be unread
        $this->assertEquals(
            1,
            Message::where('conversation_id', $conversation->id)
                ->where('sender_type', 'admin')
                ->whereNull('read_at')
                ->count()
        );

        Event::assertDispatched(MessageRead::class);
    }
}
