<?php

namespace Tests\Feature\Admin;

use App\Models\Message;
use Tests\TestCase;

class ConversationDetailTest extends TestCase
{
    public function test_show_conversation_with_messages(): void
    {
        [$user, $token] = $this->createAuthenticatedUser();
        $site = $this->createSite($user);
        $conversation = $this->createConversation($site);

        Message::factory()->count(3)->create([
            'conversation_id' => $conversation->id,
        ]);

        $response = $this->getJson(
            "/api/v1/admin/conversations/{$conversation->id}",
            $this->authHeaders($token),
        );

        $response->assertOk()
            ->assertJsonPath('conversation.id', $conversation->id)
            ->assertJsonCount(3, 'messages');
    }

    public function test_show_conversation_of_other_user_returns_403(): void
    {
        [$user, $token] = $this->createAuthenticatedUser();

        $otherUser = $this->createUser();
        $otherSite = $this->createSite($otherUser);
        $otherConversation = $this->createConversation($otherSite);

        $response = $this->getJson(
            "/api/v1/admin/conversations/{$otherConversation->id}",
            $this->authHeaders($token),
        );

        $response->assertStatus(403);
    }

    public function test_update_conversation_status(): void
    {
        [$user, $token] = $this->createAuthenticatedUser();
        $site = $this->createSite($user);
        $conversation = $this->createConversation($site);

        $response = $this->patchJson(
            "/api/v1/admin/conversations/{$conversation->id}",
            ['status' => 'closed'],
            $this->authHeaders($token),
        );

        $response->assertOk()
            ->assertJsonPath('conversation.status', 'closed');

        $this->assertDatabaseHas('conversations', [
            'id' => $conversation->id,
            'status' => 'closed',
        ]);
    }

    public function test_update_conversation_validates_status(): void
    {
        [$user, $token] = $this->createAuthenticatedUser();
        $site = $this->createSite($user);
        $conversation = $this->createConversation($site);

        $response = $this->patchJson(
            "/api/v1/admin/conversations/{$conversation->id}",
            ['status' => 'invalid_status'],
            $this->authHeaders($token),
        );

        $response->assertStatus(422);
    }

    public function test_show_nonexistent_conversation_returns_404(): void
    {
        [$user, $token] = $this->createAuthenticatedUser();

        $response = $this->getJson(
            '/api/v1/admin/conversations/00000000-0000-0000-0000-000000000000',
            $this->authHeaders($token),
        );

        $response->assertStatus(404)
            ->assertJsonPath('error', 'not_found');
    }
}
