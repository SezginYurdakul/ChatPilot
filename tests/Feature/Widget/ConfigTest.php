<?php

namespace Tests\Feature\Widget;

use Tests\TestCase;

class ConfigTest extends TestCase
{
    public function test_get_config_returns_site_settings(): void
    {
        $user = $this->createUser();
        $site = $this->createSite($user, [
            'settings' => [
                'widget' => ['theme' => 'dark', 'position' => 'bottom-left'],
            ],
        ]);

        $response = $this->getJson('/api/v1/site/config', [
            'X-Site-Key' => $site->api_key,
            'Accept' => 'application/json',
        ]);

        $response->assertOk()
            ->assertJsonPath('settings.widget.theme', 'dark')
            ->assertJsonPath('admin_online', false);
    }

    public function test_get_config_without_site_key_returns_401(): void
    {
        $response = $this->getJson('/api/v1/site/config');

        $response->assertStatus(401)
            ->assertJsonPath('error', 'missing_site_key');
    }

    public function test_get_config_with_invalid_site_key_returns_401(): void
    {
        $response = $this->getJson('/api/v1/site/config', [
            'X-Site-Key' => 'sk_invalid_key',
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('error', 'invalid_site_key');
    }

    public function test_get_config_with_inactive_site_returns_401(): void
    {
        $user = $this->createUser();
        $site = $this->createSite($user, ['is_active' => false]);

        $response = $this->getJson('/api/v1/site/config', [
            'X-Site-Key' => $site->api_key,
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('error', 'invalid_site_key');
    }
}
