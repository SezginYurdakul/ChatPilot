<?php

namespace App\Services;

use App\Models\Site;
use App\Services\Ai\AiProviderInterface;
use RuntimeException;

/**
 * Factory that resolves the correct AI provider for a given site.
 * Reads the site's ai_provider setting and returns the matching implementation.
 */
class AiService
{
    /**
     * Return the AI provider instance configured for this site.
     * Falls back to the app-level default if the site has no AI key.
     *
     * @throws RuntimeException if no AI provider is configured
     */
    public function resolve(Site $site): AiProviderInterface
    {
        $provider = $site->ai_provider;
        $apiKey = $site->ai_api_key
            ?? config("chatpilot.default_ai_keys.{$provider}")
            ?? config('chatpilot.default_ai_key');

        if ($provider === 'none' || ! $apiKey) {
            throw new RuntimeException('No AI provider configured for this site.');
        }

        // Look up the provider class from config (no hard-coded list)
        $providerClass = config("chatpilot.ai.providers.{$provider}");

        if (! $providerClass) {
            throw new RuntimeException("Unknown AI provider: {$provider}");
        }

        return new $providerClass($apiKey);
    }

    /**
     * Build conversation history array from the last N messages.
     * Converts Message models to the generic {role, content} format
     * that all providers understand.
     */
    public function buildHistory(Site $site, \App\Models\Conversation $conversation): array
    {
        $maxMessages = $site->settings['ai']['max_history_messages']
            ?? config('chatpilot.ai.max_history_messages');

        $messages = $conversation->messages()
            ->orderByDesc('created_at')
            ->limit($maxMessages)
            ->get()
            ->reverse();

        $history = [];
        foreach ($messages as $msg) {
            $history[] = [
                'role' => $msg->sender_type === 'visitor' ? 'user' : 'assistant',
                'content' => $msg->text,
            ];
        }

        return $history;
    }
}
