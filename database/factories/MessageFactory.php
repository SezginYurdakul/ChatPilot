<?php

namespace Database\Factories;

use App\Models\Conversation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'sender_type' => 'visitor',
            'sender_id' => null,
            'text' => fake()->sentence(),
            'language' => null,
            'translations' => null,
            'read_at' => null,
            'created_at' => now(),
        ];
    }

    public function fromAdmin(string $userId): static
    {
        return $this->state(fn () => [
            'sender_type' => 'admin',
            'sender_id' => $userId,
        ]);
    }

    public function fromAi(): static
    {
        return $this->state(fn () => [
            'sender_type' => 'ai',
        ]);
    }

    public function read(): static
    {
        return $this->state(fn () => [
            'read_at' => now(),
        ]);
    }
}
