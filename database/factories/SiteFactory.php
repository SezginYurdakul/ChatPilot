<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Site>
 */
class SiteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'name' => fake()->company(),
            'domain' => fake()->domainName(),
            'api_key' => 'sk_'.Str::random(60),
            'ai_provider' => 'none',
            'ai_api_key' => null,
            'ai_system_prompt' => null,
            'settings' => null,
            'is_active' => true,
        ];
    }

    public function withAi(string $provider = 'gemini'): static
    {
        return $this->state(fn () => [
            'ai_provider' => $provider,
            'ai_api_key' => 'test-api-key-'.Str::random(20),
            'ai_system_prompt' => 'You are a helpful assistant.',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }
}
