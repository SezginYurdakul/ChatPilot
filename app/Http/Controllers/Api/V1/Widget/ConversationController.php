<?php

namespace App\Http\Controllers\Api\V1\Widget;

use App\Events\NewConversation;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ConversationController extends Controller
{
    /**
     * Create a new conversation for a visitor.
     * Generates a unique visitor token that acts as the visitor's session key.
     * The visitor must include this token in subsequent requests.
     *
     * POST /api/v1/conversations
     * Header: X-Site-Key
     */
    public function store(Request $request): JsonResponse
    {
        $site = $request->attributes->get('site');

        $conversation = Conversation::create([
            'site_id' => $site->id,
            'visitor_token' => Str::random(64),
            'visitor_name' => $request->input('visitor_name'),
            'metadata' => [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'page_url' => $request->input('metadata.page_url'),
                'language' => $request->input('metadata.language'),
            ],
        ]);

        // Notify admin panel about the new conversation
        NewConversation::dispatch($conversation);

        return response()->json([
            'id' => $conversation->id,
            'visitor_token' => $conversation->visitor_token,
        ], 201);
    }
}
