<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_get_authenticated_user(): void
    {
        [$user, $token] = $this->createAuthenticatedUser([
            'email' => 'admin@test.com',
        ]);

        $response = $this->getJson('/api/v1/auth/user', $this->authHeaders($token));

        $response->assertOk()
            ->assertJsonPath('user.email', 'admin@test.com')
            ->assertJsonPath('user.id', $user->id);
    }

    public function test_get_user_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/auth/user');

        $response->assertStatus(401)
            ->assertJsonPath('error', 'unauthenticated');
    }
}
