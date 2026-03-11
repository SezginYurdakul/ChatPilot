<?php

namespace Tests\Feature\Admin;

use App\Models\Message;
use Tests\TestCase;

class StatsTest extends TestCase
{
    public function test_get_stats(): void
    {
        [$user, $token] = $this->createAuthenticatedSuperAdmin();
        $site = $this->createSite($user);
        $conversation = $this->createConversation($site);

        Message::factory()->count(3)->create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'visitor',
        ]);

        Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'ai',
        ]);

        $response = $this->getJson('/api/v1/admin/stats', $this->authHeaders($token));

        $response->assertOk()
            ->assertJsonStructure([
                'total_conversations',
                'total_messages',
                'ai_messages_count',
                'avg_response_time',
                'conversations_by_day',
                'server_errors_count',
                'server_error_rate_percent',
                'job_failures_count',
                'job_failure_rate_percent',
            ])
            ->assertJsonPath('total_conversations', 1)
            ->assertJsonPath('total_messages', 4)
            ->assertJsonPath('ai_messages_count', 1);
    }

    public function test_get_stats_with_period_filter(): void
    {
        [$user, $token] = $this->createAuthenticatedSuperAdmin();
        $site = $this->createSite($user);

        // Conversation within 7 days
        $this->createConversation($site, ['created_at' => now()->subDays(3)]);
        // Conversation older than 7 days but within 30 days
        $this->createConversation($site, ['created_at' => now()->subDays(15)]);

        // 7d should only show 1
        $response = $this->getJson('/api/v1/admin/stats?period=7d', $this->authHeaders($token));
        $response->assertOk()
            ->assertJsonPath('total_conversations', 1);

        // 30d should show 2
        $response = $this->getJson('/api/v1/admin/stats?period=30d', $this->authHeaders($token));
        $response->assertOk()
            ->assertJsonPath('total_conversations', 2);
    }

    public function test_super_admin_stats_include_all_sites_data(): void
    {
        [$user, $token] = $this->createAuthenticatedSuperAdmin();
        $ownSite = $this->createSite($user);
        $this->createConversation($ownSite);

        $otherUser = $this->createUser();
        $otherSite = $this->createSite($otherUser);
        $this->createConversation($otherSite);

        $response = $this->getJson('/api/v1/admin/stats', $this->authHeaders($token));

        $response->assertOk()
            ->assertJsonPath('total_conversations', 2);
    }

    public function test_stats_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/admin/stats');

        $response->assertStatus(401);
    }

    public function test_admin_cannot_access_stats(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $admin = $this->createUser();
        $site = $this->createSite($superAdmin);
        $admin->assignedSites()->attach($site->id);
        $this->createConversation($site);
        $token = $admin->createToken('admin')->plainTextToken;

        $response = $this->getJson('/api/v1/admin/stats', $this->authHeaders($token));

        $response->assertStatus(403);
    }
}
