<?php

namespace Database\Factories;

use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Conversation>
 */
class ConversationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'visitor_token' => Str::random(64),
            'visitor_name' => fake()->name(),
            'status' => 'active',
            'metadata' => [
                'ip' => fake()->ipv4(),
                'user_agent' => 'PHPUnit',
                'page_url' => null,
                'language' => null,
            ],
            'last_message_at' => null,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn () => ['status' => 'closed']);
    }

    public function archived(): static
    {
        return $this->state(fn () => ['status' => 'archived']);
    }
}
