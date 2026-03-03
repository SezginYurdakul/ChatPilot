<?php

namespace Tests;

use App\Models\Conversation;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function createUser(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    protected function createAuthenticatedUser(array $attributes = []): array
    {
        $user = $this->createUser($attributes);
        $token = $user->createToken('admin')->plainTextToken;

        return [$user, $token];
    }

    protected function authHeaders(string $token): array
    {
        return [
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ];
    }

    protected function createSite(User $user, array $attributes = []): Site
    {
        return Site::factory()->create(array_merge(
            ['owner_id' => $user->id],
            $attributes,
        ));
    }

    protected function createConversation(Site $site, array $attributes = []): Conversation
    {
        return Conversation::factory()->create(array_merge(
            ['site_id' => $site->id],
            $attributes,
        ));
    }

    protected function widgetHeaders(string $apiKey, ?string $visitorToken = null): array
    {
        $headers = [
            'X-Site-Key' => $apiKey,
            'Accept' => 'application/json',
        ];

        if ($visitorToken) {
            $headers['X-Visitor-Token'] = $visitorToken;
        }

        return $headers;
    }
}
