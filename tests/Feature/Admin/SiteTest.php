<?php

namespace Tests\Feature\Admin;

use App\Models\Site;
use Tests\TestCase;

class SiteTest extends TestCase
{
    public function test_list_sites(): void
    {
        [$user, $token] = $this->createAuthenticatedUser();
        $this->createSite($user);
        $this->createSite($user);

        $response = $this->getJson('/api/v1/admin/sites', $this->authHeaders($token));

        $response->assertOk()
            ->assertJsonCount(2, 'sites');
    }

    public function test_list_sites_only_shows_owned(): void
    {
        [$user, $token] = $this->createAuthenticatedUser();
        $this->createSite($user);

        $otherUser = $this->createUser();
        $this->createSite($otherUser);

        $response = $this->getJson('/api/v1/admin/sites', $this->authHeaders($token));

        $response->assertOk()
            ->assertJsonCount(1, 'sites');
    }

    public function test_create_site(): void
    {
        [$user, $token] = $this->createAuthenticatedUser();

        $response = $this->postJson('/api/v1/admin/sites', [
            'name' => 'My Website',
            'domain' => 'example.com',
        ], $this->authHeaders($token));

        $response->assertStatus(201)
            ->assertJsonPath('site.name', 'My Website')
            ->assertJsonPath('site.domain', 'example.com')
            ->assertJsonStructure(['site', 'api_key']);

        $this->assertDatabaseHas('sites', [
            'owner_id' => $user->id,
            'name' => 'My Website',
            'domain' => 'example.com',
        ]);
    }

    public function test_create_site_with_ai_config(): void
    {
        [$user, $token] = $this->createAuthenticatedUser();

        $response = $this->postJson('/api/v1/admin/sites', [
            'name' => 'AI Site',
            'domain' => 'ai.example.com',
            'ai_provider' => 'gemini',
            'ai_api_key' => 'test-key-123',
            'ai_system_prompt' => 'You are helpful.',
        ], $this->authHeaders($token));

        $response->assertStatus(201)
            ->assertJsonPath('site.ai_provider', 'gemini');
    }

    public function test_create_site_with_settings(): void
    {
        [$user, $token] = $this->createAuthenticatedUser();

        $response = $this->postJson('/api/v1/admin/sites', [
            'name' => 'Configured Site',
            'domain' => 'configured.example.com',
            'settings' => [
                'widget' => ['theme' => 'dark'],
                'rate_limit' => ['cooldown_seconds' => 5],
            ],
        ], $this->authHeaders($token));

        $response->assertStatus(201);

        $site = Site::where('name', 'Configured Site')->first();
        $this->assertEquals('dark', $site->settings['widget']['theme']);
        $this->assertEquals(5, $site->settings['rate_limit']['cooldown_seconds']);
    }

    public function test_create_site_filters_unknown_settings_keys(): void
    {
        [$user, $token] = $this->createAuthenticatedUser();

        $response = $this->postJson('/api/v1/admin/sites', [
            'name' => 'Filtered Site',
            'domain' => 'filtered.example.com',
            'settings' => [
                'widget' => ['theme' => 'dark', 'unknown_field' => 'should_be_removed'],
                'nonexistent_group' => ['foo' => 'bar'],
            ],
        ], $this->authHeaders($token));

        $response->assertStatus(201);

        $site = Site::where('name', 'Filtered Site')->first();
        $this->assertEquals('dark', $site->settings['widget']['theme']);
        $this->assertArrayNotHasKey('unknown_field', $site->settings['widget']);
        $this->assertArrayNotHasKey('nonexistent_group', $site->settings);
    }

    public function test_create_site_validation(): void
    {
        [$user, $token] = $this->createAuthenticatedUser();

        $response = $this->postJson('/api/v1/admin/sites', [], $this->authHeaders($token));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'domain']);
    }

    public function test_update_site(): void
    {
        [$user, $token] = $this->createAuthenticatedUser();
        $site = $this->createSite($user);

        $response = $this->patchJson(
            "/api/v1/admin/sites/{$site->id}",
            ['name' => 'Updated Name'],
            $this->authHeaders($token),
        );

        $response->assertOk()
            ->assertJsonPath('site.name', 'Updated Name');
    }

    public function test_update_site_of_other_user_returns_403(): void
    {
        [$user, $token] = $this->createAuthenticatedUser();

        $otherUser = $this->createUser();
        $otherSite = $this->createSite($otherUser);

        $response = $this->patchJson(
            "/api/v1/admin/sites/{$otherSite->id}",
            ['name' => 'Hacked Name'],
            $this->authHeaders($token),
        );

        $response->assertStatus(403);
    }

    public function test_regenerate_api_key(): void
    {
        [$user, $token] = $this->createAuthenticatedUser();
        $site = $this->createSite($user);
        $oldKey = $site->api_key;

        $response = $this->postJson(
            "/api/v1/admin/sites/{$site->id}/regenerate-key",
            [],
            $this->authHeaders($token),
        );

        $response->assertOk()
            ->assertJsonStructure(['api_key', 'message']);

        $site->refresh();
        $this->assertNotEquals($oldKey, $site->api_key);
    }

    public function test_regenerate_key_of_other_user_returns_403(): void
    {
        [$user, $token] = $this->createAuthenticatedUser();

        $otherUser = $this->createUser();
        $otherSite = $this->createSite($otherUser);

        $response = $this->postJson(
            "/api/v1/admin/sites/{$otherSite->id}/regenerate-key",
            [],
            $this->authHeaders($token),
        );

        $response->assertStatus(403);
    }

    public function test_settings_schema_endpoint(): void
    {
        [$user, $token] = $this->createAuthenticatedUser();

        $response = $this->getJson('/api/v1/admin/sites/settings-schema', $this->authHeaders($token));

        $response->assertOk()
            ->assertJsonStructure(['version', 'schema'])
            ->assertJsonPath('version', '1.0.0');
    }
}
