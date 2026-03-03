<?php

namespace App\Observers;

use App\Jobs\ProcessAiResponse;
use App\Models\Message;

/**
 * Observes Message model events.
 * When a visitor sends a message and the admin is offline,
 * dispatches an AI response job to auto-reply.
 */
class MessageObserver
{
    /**
     * Handle the "created" event for a message.
     * Only triggers AI for visitor messages when:
     * - The site has an AI provider configured
     * - The site's settings enable AI responses when offline
     * - The admin is currently offline
     */
    public function created(Message $message): void
    {
        // Only respond to visitor messages
        if ($message->sender_type !== 'visitor') {
            return;
        }

        $conversation = $message->conversation;
        $site = $conversation->site;

        // Check if AI is configured for this site
        if ($site->ai_provider === 'none' || ! $site->ai_api_key) {
            return;
        }

        // Check if AI auto-response is enabled in site settings
        $respondWhenOffline = $site->settings['ai']['respond_when_offline'] ?? true;
        if (! $respondWhenOffline) {
            return;
        }

        // TODO: Check if admin is actually online (Phase 2 - Reverb presence)
        // For now, always dispatch AI response
        ProcessAiResponse::dispatch($conversation);
    }
}
