<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class LogoutTest extends TestCase
{
    public function test_logout_revokes_token(): void
    {
        [$user, $token] = $this->createAuthenticatedUser();

        $response = $this->postJson('/api/v1/auth/logout', [], $this->authHeaders($token));

        $response->assertOk()
            ->assertJsonPath('message', 'Logged out successfully.');

        // Token should be deleted from database
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    public function test_logout_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(401)
            ->assertJsonPath('error', 'unauthenticated');
    }
}
