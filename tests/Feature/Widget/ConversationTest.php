<?php

namespace Tests\Feature\Widget;

use App\Events\NewConversation;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ConversationTest extends TestCase
{
    public function test_create_conversation(): void
    {
        Event::fake([NewConversation::class]);

        $user = $this->createUser();
        $site = $this->createSite($user);

        $response = $this->postJson('/api/v1/conversations', [
            'visitor_name' => 'John',
        ], $this->widgetHeaders($site->api_key));

        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'visitor_token']);

        $this->assertDatabaseHas('conversations', [
            'site_id' => $site->id,
            'visitor_name' => 'John',
            'status' => 'active',
        ]);

        Event::assertDispatched(NewConversation::class);
    }

    public function test_create_conversation_without_site_key_returns_401(): void
    {
        $response = $this->postJson('/api/v1/conversations', [
            'visitor_name' => 'John',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('error', 'missing_site_key');
    }

    public function test_create_conversation_stores_metadata(): void
    {
        Event::fake([NewConversation::class]);

        $user = $this->createUser();
        $site = $this->createSite($user);

        $response = $this->postJson('/api/v1/conversations', [
            'visitor_name' => 'Jane',
            'metadata' => [
                'page_url' => 'https://example.com/pricing',
                'language' => 'en',
            ],
        ], $this->widgetHeaders($site->api_key));

        $response->assertStatus(201);
    }
}
