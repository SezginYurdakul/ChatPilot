<?php

namespace Tests;

use App\Models\Conversation;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    public function createApplication()
    {
        // Force isolated test runtime before Laravel bootstraps configuration.
        putenv('APP_ENV=testing');
        putenv('CACHE_STORE=array');
        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=:memory:');
        putenv('QUEUE_CONNECTION=sync');
        putenv('SESSION_DRIVER=array');

        $_ENV['APP_ENV'] = 'testing';
        $_ENV['CACHE_STORE'] = 'array';
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';
        $_ENV['QUEUE_CONNECTION'] = 'sync';
        $_ENV['SESSION_DRIVER'] = 'array';

        $_SERVER['APP_ENV'] = 'testing';
        $_SERVER['CACHE_STORE'] = 'array';
        $_SERVER['DB_CONNECTION'] = 'sqlite';
        $_SERVER['DB_DATABASE'] = ':memory:';
        $_SERVER['QUEUE_CONNECTION'] = 'sync';
        $_SERVER['SESSION_DRIVER'] = 'array';

        return parent::createApplication();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $connection = config('database.default');
        $database = (string) config("database.connections.{$connection}.database");

        if ($connection !== 'sqlite' || $database !== ':memory:') {
            throw new RuntimeException(
                "Unsafe test database configuration detected: connection={$connection}, database={$database}. ".
                'Expected sqlite/:memory: to protect non-test data.',
            );
        }
    }

    protected function createUser(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    protected function createSuperAdmin(array $attributes = []): User
    {
        return $this->createUser(array_merge(['role' => User::ROLE_SUPER_ADMIN], $attributes));
    }

    protected function createAuthenticatedUser(array $attributes = []): array
    {
        $user = $this->createUser($attributes);
        $token = $user->createToken('admin')->plainTextToken;

        return [$user, $token];
    }

    protected function createAuthenticatedSuperAdmin(array $attributes = []): array
    {
        return $this->createAuthenticatedUser(array_merge(['role' => User::ROLE_SUPER_ADMIN], $attributes));
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
        $site = Site::factory()->create($attributes);
        $site->admins()->syncWithoutDetaching([$user->id]);

        return $site;
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
