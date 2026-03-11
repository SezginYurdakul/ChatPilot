<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    public function test_super_admin_can_list_users(): void
    {
        [$superAdmin, $token] = $this->createAuthenticatedSuperAdmin();
        $this->createUser(['name' => 'Regular Admin']);

        $response = $this->getJson('/api/v1/admin/users', $this->authHeaders($token));

        $response->assertOk()
            ->assertJsonCount(2, 'users')
            ->assertJsonFragment(['name' => $superAdmin->name])
            ->assertJsonFragment(['name' => 'Regular Admin']);
    }

    public function test_admin_cannot_list_users(): void
    {
        [, $token] = $this->createAuthenticatedUser(['role' => User::ROLE_ADMIN]);

        $response = $this->getJson('/api/v1/admin/users', $this->authHeaders($token));

        $response->assertForbidden()
            ->assertJson(['error' => 'forbidden']);
    }

    public function test_super_admin_can_create_user(): void
    {
        [$superAdmin, $token] = $this->createAuthenticatedSuperAdmin();
        $site = $this->createSite($superAdmin);

        $response = $this->postJson('/api/v1/admin/users', [
            'name' => 'New Admin',
            'email' => 'new@example.com',
            'password' => 'secret123',
            'site_ids' => [$site->id],
        ], $this->authHeaders($token));

        $response->assertCreated()
            ->assertJsonPath('user.name', 'New Admin')
            ->assertJsonPath('user.email', 'new@example.com')
            ->assertJsonPath('user.role', 'admin');

        $this->assertDatabaseHas('users', [
            'email' => 'new@example.com',
            'role' => 'admin',
        ]);

        $this->assertDatabaseHas('site_user', [
            'site_id' => $site->id,
            'user_id' => $response->json('user.id'),
        ]);
    }

    public function test_super_admin_cannot_create_super_admin_from_user_management(): void
    {
        [$superAdmin, $token] = $this->createAuthenticatedSuperAdmin();
        $site = $this->createSite($superAdmin);

        $response = $this->postJson('/api/v1/admin/users', [
            'name' => 'Another Super',
            'email' => 'super2@example.com',
            'password' => 'secret123',
            'role' => 'super_admin',
            'site_ids' => [$site->id],
        ], $this->authHeaders($token));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['role']);
    }

    public function test_create_user_validates_required_fields(): void
    {
        [, $token] = $this->createAuthenticatedSuperAdmin();

        $response = $this->postJson('/api/v1/admin/users', [], $this->authHeaders($token));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password', 'site_ids']);
    }

    public function test_create_user_fails_with_duplicate_email(): void
    {
        [$superAdmin, $token] = $this->createAuthenticatedSuperAdmin();
        $this->createUser(['email' => 'taken@example.com']);
        $site = $this->createSite($superAdmin);

        $response = $this->postJson('/api/v1/admin/users', [
            'name' => 'Duplicate',
            'email' => 'taken@example.com',
            'password' => 'secret123',
            'site_ids' => [$site->id],
        ], $this->authHeaders($token));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_super_admin_can_delete_user(): void
    {
        [, $token] = $this->createAuthenticatedSuperAdmin();
        $admin = $this->createUser(['name' => 'To Delete']);

        $response = $this->deleteJson("/api/v1/admin/users/{$admin->id}", [], $this->authHeaders($token));

        $response->assertOk()
            ->assertJson(['message' => 'User deleted successfully.']);

        $this->assertDatabaseMissing('users', ['id' => $admin->id]);
    }

    public function test_deleting_user_preserves_assigned_sites(): void
    {
        [, $token] = $this->createAuthenticatedSuperAdmin();
        $admin = $this->createUser(['name' => 'Site Owner']);
        $site = $this->createSite($admin);

        $response = $this->deleteJson("/api/v1/admin/users/{$admin->id}", [], $this->authHeaders($token));

        $response->assertOk()
            ->assertJson(['message' => 'User deleted successfully.']);

        $this->assertDatabaseMissing('users', ['id' => $admin->id]);
        $this->assertDatabaseHas('sites', [
            'id' => $site->id,
        ]);
        $this->assertDatabaseMissing('site_user', [
            'site_id' => $site->id,
            'user_id' => $admin->id,
        ]);
    }

    public function test_super_admin_cannot_delete_self(): void
    {
        [$superAdmin, $token] = $this->createAuthenticatedSuperAdmin();

        $response = $this->deleteJson("/api/v1/admin/users/{$superAdmin->id}", [], $this->authHeaders($token));

        $response->assertForbidden()
            ->assertJson(['error' => 'forbidden']);

        $this->assertDatabaseHas('users', ['id' => $superAdmin->id]);
    }

    public function test_admin_cannot_delete_users(): void
    {
        [, $token] = $this->createAuthenticatedUser(['role' => User::ROLE_ADMIN]);
        $otherUser = $this->createUser();

        $response = $this->deleteJson("/api/v1/admin/users/{$otherUser->id}", [], $this->authHeaders($token));

        $response->assertForbidden();
    }

    public function test_unauthenticated_cannot_access_users(): void
    {
        $response = $this->getJson('/api/v1/admin/users', ['Accept' => 'application/json']);

        $response->assertUnauthorized();
    }
}
