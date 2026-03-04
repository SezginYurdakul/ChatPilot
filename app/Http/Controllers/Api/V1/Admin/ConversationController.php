<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Jobs\TranslateMessage;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    /**
     * List conversations for the admin's sites.
     * Supports filtering by status and search by visitor name.
     * Results are paginated and ordered by last activity.
     *
     * GET /api/v1/admin/conversations?status=active&search=john&page=1
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Conversation::whereIn('site_id', $user->sites()->pluck('id'))
            ->with('site:id,name')
            ->orderByDesc('last_message_at');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->query('search')) {
            $query->where('visitor_name', 'ilike', "%{$search}%");
        }

        $conversations = $query->paginate(20);

        return response()->json($conversations);
    }

    /**
     * Show a single conversation with its messages.
     * Used when the admin clicks on a conversation in the panel.
     *
     * GET /api/v1/admin/conversations/{conversation}
     */
    public function show(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorizeSiteOwnership($request, $conversation);

        $preferredLanguage = strtolower(trim((string) $request->query('language', '')));
        $allowedLanguages = config('chatpilot.translation.allowed_languages', []);

        if ($preferredLanguage !== '' && in_array($preferredLanguage, $allowedLanguages, true)) {
            $metadata = $conversation->metadata ?? [];
            $metadata['admin_language'] = $preferredLanguage;
            $conversation->update(['metadata' => $metadata]);
        }

        $conversation->load('messages');

        // Backfill missing visitor -> admin translations for the currently selected admin language.
        if ($preferredLanguage !== '' && in_array($preferredLanguage, $allowedLanguages, true)) {
            foreach ($conversation->messages as $message) {
                if ($message->sender_type !== 'visitor') {
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
            'conversation' => $conversation,
            'messages' => $conversation->messages,
        ]);
    }

    /**
     * Update conversation status (close or archive).
     *
     * PATCH /api/v1/admin/conversations/{conversation}
     */
    public function update(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorizeSiteOwnership($request, $conversation);

        $request->validate([
            'status' => 'required|in:active,closed,archived',
        ]);

        $conversation->update([
            'status' => $request->input('status'),
        ]);

        return response()->json([
            'conversation' => $conversation,
        ]);
    }

    /**
     * Send a message as admin in a conversation.
     *
     * POST /api/v1/admin/conversations/{conversation}/messages
     */
    public function sendMessage(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorizeSiteOwnership($request, $conversation);

        $request->validate([
            'text' => 'required|string|max:2000',
            'language' => 'nullable|string|max:5',
        ]);

        $message = $conversation->messages()->create([
            'sender_type' => 'admin',
            'sender_id' => $request->user()->id,
            'text' => $request->input('text'),
            'language' => $request->input('language'),
            'created_at' => now(),
        ]);

        if ($request->filled('language')) {
            $metadata = $conversation->metadata ?? [];
            $metadata['admin_language'] = strtolower($request->input('language'));
            $conversation->update([
                'last_message_at' => now(),
                'metadata' => $metadata,
            ]);
        } else {
            $conversation->update(['last_message_at' => now()]);
        }

        // Broadcast admin's message to the visitor's widget
        MessageSent::dispatch($message);

        return response()->json([
            'message' => $message,
        ], 201);
    }

    /**
     * Mark all unread messages in a conversation as read.
     *
     * POST /api/v1/admin/conversations/{conversation}/read
     */
    public function markAsRead(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorizeSiteOwnership($request, $conversation);

        $readAt = now();

        $conversation->messages()
            ->whereNull('read_at')
            ->where('sender_type', 'visitor')
            ->update(['read_at' => $readAt]);

        // Notify the visitor's widget that messages were read (blue tick)
        MessageRead::dispatch($conversation->id, $readAt->toIso8601String());

        return response()->json([
            'message' => 'Messages marked as read.',
        ]);
    }

    /**
     * Verify that the authenticated user owns the site this conversation belongs to.
     * Aborts with 403 if the user doesn't own the site.
     */
    private function authorizeSiteOwnership(Request $request, Conversation $conversation): void
    {
        $userSiteIds = $request->user()->sites()->pluck('id');

        if (! $userSiteIds->contains($conversation->site_id)) {
            abort(403, 'You do not own this conversation.');
        }
    }
}
