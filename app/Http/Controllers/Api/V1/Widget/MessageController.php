<?php

namespace App\Http\Controllers\Api\V1\Widget;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Jobs\TranslateMessage;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * List messages for a conversation.
     * Supports incremental fetch via ?after={message_id} query param,
     * so the widget only fetches new messages since last poll.
     *
     * GET /api/v1/conversations/{conversation}/messages
     * Header: X-Visitor-Token
     */
    public function index(Request $request): JsonResponse
    {
        $conversation = $request->attributes->get('conversation');

        $preferredLanguage = strtolower(trim((string) $request->query('language', '')));
        $allowedLanguages = config('chatpilot.translation.allowed_languages', []);

        if ($preferredLanguage !== '' && in_array($preferredLanguage, $allowedLanguages, true)) {
            $metadata = $conversation->metadata ?? [];
            $metadata['language'] = $preferredLanguage;
            $conversation->update(['metadata' => $metadata]);
        }

        $query = $conversation->messages()->orderBy('created_at');

        // Incremental fetch: only return messages after the given ID
        if ($afterId = $request->query('after')) {
            $afterMessage = Message::find($afterId);
            if ($afterMessage) {
                $query->where('created_at', '>', $afterMessage->created_at);
            }
        }

        $messages = $query->get();

        // Backfill missing admin -> visitor translations for currently selected visitor language.
        if ($preferredLanguage !== '' && in_array($preferredLanguage, $allowedLanguages, true)) {
            foreach ($messages as $message) {
                if ($message->sender_type !== 'admin') {
                    continue;
                }

                $sourceLanguage = strtolower(trim((string) ($message->language ?? '')));
                if ($sourceLanguage === '' || $sourceLanguage === $preferredLanguage) {
                    continue;
                }

                $translations = $message->translations ?? [];
                if (! empty($translations[$preferredLanguage])) {
                    continue;
                }

                TranslateMessage::dispatch($message, $preferredLanguage);
            }
        }

        return response()->json([
            'messages' => $messages,
        ]);
    }

    /**
     * Store a new message from the visitor.
     * Saves the message and updates the conversation's last_message_at timestamp.
     * AI response is triggered separately via MessageObserver (Phase 3).
     *
     * POST /api/v1/conversations/{conversation}/messages
     * Header: X-Visitor-Token
     */
    public function store(Request $request): JsonResponse
    {
        $conversation = $request->attributes->get('conversation');

        $request->validate([
            'text' => 'required|string|max:1000',
            'language' => 'nullable|string|max:5',
        ]);

        $message = $conversation->messages()->create([
            'sender_type' => 'visitor',
            'text' => $request->input('text'),
            'language' => $request->input('language'),
            'created_at' => now(),
        ]);

        $metadata = $conversation->metadata ?? [];

        if ($request->filled('language')) {
            $metadata['language'] = $request->input('language');
        }

        $conversation->update([
            'last_message_at' => now(),
            'metadata' => $metadata,
        ]);

        // Broadcast to all listeners on this conversation's channel
        MessageSent::dispatch($message);

        return response()->json([
            'message' => $message,
        ], 201);
    }
}
