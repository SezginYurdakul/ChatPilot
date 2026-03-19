<?php

namespace Tests\Feature\Admin;

use App\Events\AdminStatusChanged;
use App\Support\AdminPresence;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PresenceTest extends TestCase
{
    public function test_heartbeat_marks_admin_online(): void
    {
        Event::fake([AdminStatusChanged::class]);

        [$user, $token] = $this->createAuthenticatedUser();
        $site = $this->createSite($user);

        $response = $this->postJson('/api/v1/admin/presence/heartbeat', [], $this->authHeaders($token));

        $response->assertOk()
            ->assertJsonPath('online', true);

        $this->assertTrue(AdminPresence::isOnline($site->id));
        Event::assertDispatched(AdminStatusChanged::class);
    }

    public function test_offline_marks_admin_offline(): void
    {
        Event::fake([AdminStatusChanged::class]);

        [$user, $token] = $this->createAuthenticatedUser();
        $site = $this->createSite($user);

        // First go online
        AdminPresence::heartbeat($site->id);
        $this->assertTrue(AdminPresence::isOnline($site->id));

        // Then go offline
        $response = $this->postJson('/api/v1/admin/presence/offline', [], $this->authHeaders($token));

        $response->assertOk()
            ->assertJsonPath('online', false);

        $this->assertFalse(AdminPresence::isOnline($site->id));
        Event::assertDispatched(AdminStatusChanged::class);
    }

    public function test_heartbeat_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/admin/presence/heartbeat');

        $response->assertStatus(401);
    }

    public function test_offline_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/admin/presence/offline');

        $response->assertStatus(401);
    }

    public function test_heartbeat_marks_all_user_sites_online(): void
    {
        Event::fake([AdminStatusChanged::class]);

        [$user, $token] = $this->createAuthenticatedUser();
        $site1 = $this->createSite($user);
        $site2 = $this->createSite($user);

        $this->postJson('/api/v1/admin/presence/heartbeat', [], $this->authHeaders($token));

        $this->assertTrue(AdminPresence::isOnline($site1->id));
        $this->assertTrue(AdminPresence::isOnline($site2->id));
    }

    public function test_heartbeat_only_broadcasts_when_admin_transitions_to_online(): void
    {
        Event::fake([AdminStatusChanged::class]);

        [$user, $token] = $this->createAuthenticatedUser();
        $site = $this->createSite($user);

        $this->postJson('/api/v1/admin/presence/heartbeat', [], $this->authHeaders($token))
            ->assertOk();

        Event::assertDispatchedTimes(AdminStatusChanged::class, 1);

        Event::fake([AdminStatusChanged::class]);

        $this->postJson('/api/v1/admin/presence/heartbeat', [], $this->authHeaders($token))
            ->assertOk();

        Event::assertNotDispatched(AdminStatusChanged::class);
    }

    public function test_offline_only_broadcasts_when_admin_transitions_to_offline(): void
    {
        Event::fake([AdminStatusChanged::class]);

        [$user, $token] = $this->createAuthenticatedUser();
        $site = $this->createSite($user);

        AdminPresence::heartbeat($site->id);

        $this->postJson('/api/v1/admin/presence/offline', [], $this->authHeaders($token))
            ->assertOk();

        Event::assertDispatchedTimes(AdminStatusChanged::class, 1);

        Event::fake([AdminStatusChanged::class]);

        $this->postJson('/api/v1/admin/presence/offline', [], $this->authHeaders($token))
            ->assertOk();

        Event::assertNotDispatched(AdminStatusChanged::class);
    }

    public function test_heartbeat_marks_assigned_sites_online(): void
    {
        Event::fake([AdminStatusChanged::class]);

        $superAdmin = $this->createSuperAdmin();
        $admin = $this->createUser();
        $assignedSite = $this->createSite($superAdmin);
        $admin->assignedSites()->attach($assignedSite->id);
        $token = $admin->createToken('admin')->plainTextToken;

        $this->postJson('/api/v1/admin/presence/heartbeat', [], $this->authHeaders($token));

        $this->assertTrue(AdminPresence::isOnline($assignedSite->id));
    }

    public function test_super_admin_heartbeat_does_not_mark_any_site_online(): void
    {
        Event::fake([AdminStatusChanged::class]);

        [$superAdmin, $token] = $this->createAuthenticatedSuperAdmin();
        $site = $this->createSite($superAdmin);

        $response = $this->postJson('/api/v1/admin/presence/heartbeat', [], $this->authHeaders($token));

        $response->assertOk()
            ->assertJsonPath('online', false);

        $this->assertFalse(AdminPresence::isOnline($site->id));
        Event::assertNotDispatched(AdminStatusChanged::class);
    }
}
