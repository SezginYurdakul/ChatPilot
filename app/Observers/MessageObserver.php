<?php

namespace App\Observers;

use App\Jobs\ProcessAiResponse;
use App\Jobs\TranslateMessage;
use App\Models\Message;
use App\Support\AdminPresence;

/**
 * Observes Message model events.
 * Keeps translation lightweight: max 1 target language per message.
 */
class MessageObserver
{
    public function created(Message $message): void
    {
        $conversation = $message->conversation;
        $site = $conversation->site;

        // Admin -> Visitor: translate to visitor's language, fallback to site default
        if ($message->sender_type === 'admin') {
            $visitorLang = strtolower(trim((string) ($conversation->metadata['language'] ?? '')));
            if ($visitorLang === '') {
                $visitorLang = strtolower(trim((string) ($site->settings['language'] ?? 'en')));
            }

            $messageLang = strtolower(trim((string) ($message->language ?? '')));
            if ($visitorLang !== '' && $visitorLang !== $messageLang) {
                TranslateMessage::dispatch($message, $visitorLang);
            }
            return;
        }

        if ($message->sender_type !== 'visitor') {
            return;
        }

        // Visitor -> Admin: translate to conversation-specific admin language first, fallback to site default.
        $conversationAdminLang = strtolower(trim((string) ($conversation->metadata['admin_language'] ?? '')));
        $siteDefaultLang = strtolower(trim((string) ($site->settings['language'] ?? 'en')));
        $adminLang = $conversationAdminLang !== '' ? $conversationAdminLang : $siteDefaultLang;

        if ($adminLang !== '') {
            TranslateMessage::dispatch($message, $adminLang);
        }

        if ($site->ai_provider === 'none' || ! $site->ai_api_key) {
            return;
        }

        $respondWhenOffline = $site->settings['ai']['respond_when_offline'] ?? true;
        if (! $respondWhenOffline) {
            return;
        }

        if (AdminPresence::isOnline((string) $site->id)) {
            return;
        }

        ProcessAiResponse::dispatch($conversation);
    }
}
