<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class ConversationListTest extends TestCase
{
    public function test_list_conversations(): void
    {
        [$user, $token] = $this->createAuthenticatedUser();
        $site = $this->createSite($user);
        $this->createConversation($site, ['last_message_at' => now()]);
        $this->createConversation($site, ['last_message_at' => now()->subHour()]);

        $response = $this->getJson('/api/v1/admin/conversations', $this->authHeaders($token));

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_list_conversations_filters_by_status(): void
    {
        [$user, $token] = $this->createAuthenticatedUser();
        $site = $this->createSite($user);
        $this->createConversation($site, ['status' => 'active', 'last_message_at' => now()]);
        $this->createConversation($site, ['status' => 'closed', 'last_message_at' => now()]);

        $response = $this->getJson('/api/v1/admin/conversations?status=active', $this->authHeaders($token));

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_list_conversations_only_shows_owned_sites(): void
    {
        [$user, $token] = $this->createAuthenticatedUser();
        $ownSite = $this->createSite($user);
        $this->createConversation($ownSite, ['last_message_at' => now()]);

        // Another user's site
        $otherUser = $this->createUser();
        $otherSite = $this->createSite($otherUser);
        $this->createConversation($otherSite, ['last_message_at' => now()]);

        $response = $this->getJson('/api/v1/admin/conversations', $this->authHeaders($token));

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_list_conversations_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/admin/conversations');

        $response->assertStatus(401);
    }

    public function test_list_conversations_is_paginated(): void
    {
        [$user, $token] = $this->createAuthenticatedUser();
        $site = $this->createSite($user);

        for ($i = 0; $i < 25; $i++) {
            $this->createConversation($site, ['last_message_at' => now()->subMinutes($i)]);
        }

        $response = $this->getJson('/api/v1/admin/conversations', $this->authHeaders($token));

        $response->assertOk()
            ->assertJsonCount(20, 'data')
            ->assertJsonPath('last_page', 2);
    }
}
