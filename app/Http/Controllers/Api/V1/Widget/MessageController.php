<?php

namespace App\Http\Controllers\Api\V1\Widget;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
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

        $query = $conversation->messages()->orderBy('created_at');

        // Incremental fetch: only return messages after the given ID
        if ($afterId = $request->query('after')) {
            $afterMessage = Message::find($afterId);
            if ($afterMessage) {
                $query->where('created_at', '>', $afterMessage->created_at);
            }
        }

        $messages = $query->get();

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

        $conversation->update(['last_message_at' => now()]);

        // Broadcast to all listeners on this conversation's channel
        MessageSent::dispatch($message);

        return response()->json([
            'message' => $message,
        ], 201);
    }
}
