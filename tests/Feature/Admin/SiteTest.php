<?php

namespace Tests\Feature\Admin;

use App\Models\Site;
use Tests\TestCase;

class SiteTest extends TestCase
{
    public function test_list_sites(): void
    {
        [$user, $token] = $this->createAuthenticatedSuperAdmin();
        $this->createSite($user);
        $this->createSite($user);

        $response = $this->getJson('/api/v1/admin/sites', $this->authHeaders($token));

        $response->assertOk()
            ->assertJsonCount(2, 'sites');
    }

    public function test_super_admin_list_sites_includes_all_sites(): void
    {
        [$user, $token] = $this->createAuthenticatedSuperAdmin();
        $this->createSite($user);

        $otherUser = $this->createUser();
        $this->createSite($otherUser);

        $response = $this->getJson('/api/v1/admin/sites', $this->authHeaders($token));

        $response->assertOk()
            ->assertJsonCount(2, 'sites');
    }

    public function test_create_site(): void
    {
        [$user, $token] = $this->createAuthenticatedSuperAdmin();

        $response = $this->postJson('/api/v1/admin/sites', [
            'name' => 'My Website',
            'domain' => 'example.com',
        ], $this->authHeaders($token));

        $response->assertStatus(201)
            ->assertJsonPath('site.name', 'My Website')
            ->assertJsonPath('site.domain', 'example.com')
            ->assertJsonStructure(['site', 'api_key']);

        $this->assertDatabaseHas('sites', [
            'name' => 'My Website',
            'domain' => 'example.com',
        ]);
        $this->assertDatabaseHas('site_user', [
            'site_id' => $response->json('site.id'),
            'user_id' => $user->id,
        ]);
    }

    public function test_create_site_with_ai_config(): void
    {
        [$user, $token] = $this->createAuthenticatedSuperAdmin();

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
        [$user, $token] = $this->createAuthenticatedSuperAdmin();

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
        [$user, $token] = $this->createAuthenticatedSuperAdmin();

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
        [$user, $token] = $this->createAuthenticatedSuperAdmin();

        $response = $this->postJson('/api/v1/admin/sites', [], $this->authHeaders($token));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'domain']);
    }

    public function test_update_site(): void
    {
        [$user, $token] = $this->createAuthenticatedSuperAdmin();
        $site = $this->createSite($user);

        $response = $this->patchJson(
            "/api/v1/admin/sites/{$site->id}",
            ['name' => 'Updated Name'],
            $this->authHeaders($token),
        );

        $response->assertOk()
            ->assertJsonPath('site.name', 'Updated Name');
    }

    public function test_admin_cannot_update_sites(): void
    {
        [$admin, $token] = $this->createAuthenticatedUser(['role' => 'admin']);
        $site = $this->createSite($this->createSuperAdmin());

        $response = $this->patchJson(
            "/api/v1/admin/sites/{$site->id}",
            ['name' => 'Hacked Name'],
            $this->authHeaders($token),
        );

        $response->assertStatus(403);
    }

    public function test_regenerate_api_key(): void
    {
        [$user, $token] = $this->createAuthenticatedSuperAdmin();
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

    public function test_admin_cannot_regenerate_api_key(): void
    {
        [$admin, $token] = $this->createAuthenticatedUser(['role' => 'admin']);
        $site = $this->createSite($this->createSuperAdmin());

        $response = $this->postJson(
            "/api/v1/admin/sites/{$site->id}/regenerate-key",
            [],
            $this->authHeaders($token),
        );

        $response->assertStatus(403);
    }

    public function test_settings_schema_endpoint(): void
    {
        [$user, $token] = $this->createAuthenticatedSuperAdmin();

        $response = $this->getJson('/api/v1/admin/sites/settings-schema', $this->authHeaders($token));

        $response->assertOk()
            ->assertJsonStructure(['version', 'schema'])
            ->assertJsonPath('version', '1.0.0');
    }

    public function test_super_admin_can_list_all_sites(): void
    {
        [$superAdmin, $token] = $this->createAuthenticatedSuperAdmin();
        $this->createSite($superAdmin);

        $otherUser = $this->createUser();
        $this->createSite($otherUser);

        $response = $this->getJson('/api/v1/admin/sites', $this->authHeaders($token));

        $response->assertOk()
            ->assertJsonCount(2, 'sites');
    }

    public function test_assigned_admin_cannot_list_sites(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $assignedAdmin = $this->createUser();
        $assignedSite = $this->createSite($superAdmin);
        $otherSite = $this->createSite($superAdmin);
        $assignedAdmin->assignedSites()->attach($assignedSite->id);
        $token = $assignedAdmin->createToken('admin')->plainTextToken;

        $response = $this->getJson('/api/v1/admin/sites', $this->authHeaders($token));

        $response->assertStatus(403);
    }
}
