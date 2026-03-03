<?php

namespace App\Http\Middleware;

use App\Models\Conversation;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateVisitorToken
{
    /**
     * Validate the X-Visitor-Token header against the conversation in the URL.
     * Ensures the visitor owns the conversation they're trying to access.
     * Attaches the Conversation model to the request for downstream use.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $visitorToken = $request->header('X-Visitor-Token');

        if (! $visitorToken) {
            return response()->json([
                'error' => 'missing_visitor_token',
                'message' => 'X-Visitor-Token header is required.',
            ], 401);
        }

        // Check if token matches the conversation from the URL
        $conversationId = $request->route('conversation');

        $conversation = Conversation::where('id', $conversationId)
            ->where('visitor_token', $visitorToken)
            ->first();

        if (! $conversation) {
            return response()->json([
                'error' => 'invalid_visitor_token',
                'message' => 'Invalid visitor token for this conversation.',
            ], 403);
        }

        $request->attributes->set('conversation', $conversation);

        return $next($request);
    }
}
