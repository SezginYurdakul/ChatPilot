<?php

namespace Tests\Unit\Middleware;

use App\Models\User;
use Tests\TestCase;

class EnsureSuperAdminTest extends TestCase
{
    public function test_super_admin_can_pass_middleware(): void
    {
        [, $token] = $this->createAuthenticatedSuperAdmin();

        $response = $this->getJson('/api/v1/admin/users', $this->authHeaders($token));

        $response->assertOk();
    }

    public function test_admin_is_blocked_by_middleware(): void
    {
        [, $token] = $this->createAuthenticatedUser(['role' => User::ROLE_ADMIN]);

        $response = $this->getJson('/api/v1/admin/users', $this->authHeaders($token));

        $response->assertForbidden()
            ->assertJson([
                'error' => 'forbidden',
                'message' => 'Super admin access required.',
            ]);
    }
}
