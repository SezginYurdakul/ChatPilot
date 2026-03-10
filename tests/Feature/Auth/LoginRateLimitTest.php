<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class LoginRateLimitTest extends TestCase
{
    public function test_login_is_rate_limited_after_too_many_attempts(): void
    {
        User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('secret123'),
        ]);

        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson('/api/v1/auth/login', [
                'email' => 'admin@test.com',
                'password' => 'wrong-password',
            ]);

            $response->assertStatus(401);
        }

        $throttledResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'wrong-password',
        ]);

        $throttledResponse->assertStatus(429)
            ->assertJsonPath('error', 'too_many_requests');

        RateLimiter::clear('admin@test.com|127.0.0.1');
    }
}
