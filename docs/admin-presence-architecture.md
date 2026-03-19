# Building Efficient Admin Presence and Realtime Messaging with Laravel Reverb, Redis, and WebSockets in ChatPilot

Real-time presence changes how a support product feels. If the system knows when an admin is available, it can route conversations more intelligently, suppress unnecessary AI replies, and keep the widget UI honest.

In ChatPilot, I built admin presence and admin-side realtime messaging around three simple responsibilities:

- Redis is the source of truth for online/offline presence.
- Laravel Reverb is the real-time transport layer.
- Laravel application logic decides how presence affects chat behavior.

This combination keeps presence fast, lightweight, and operationally simple.

## Why This Architecture Works

A common mistake in presence systems is trying to store online state in the database. That usually creates stale records, cleanup problems, and extra write load.

Instead, ChatPilot uses a better model:

- Presence is ephemeral, so it belongs in Redis.
- UI updates are real-time, so they belong in WebSockets.
- Durable chat history belongs in the database.
- Business rules such as "AI should only answer when no admin is online" belong in backend application logic.

That separation gives three major benefits:

- Low latency: Redis writes and reads are extremely fast.
- Automatic cleanup: Presence expires naturally via TTL.
- Clear behavior: The backend still owns the final decision.

## The Core Presence Flow

The admin presence flow in ChatPilot works like this:

1. The admin dashboard sends a periodic heartbeat.
2. The backend writes a Redis key with a TTL.
3. The backend broadcasts an `AdminStatusChanged` event through Reverb.
4. The widget listens for this event and updates the UI in real time.
5. When a visitor sends a message, the backend checks Redis.
6. If admin is online, AI does not respond.
7. If admin is offline, AI fallback is allowed.

This means Redis is not just for display state. It directly affects runtime behavior.

Conversation messages now follow a similar realtime path:

1. A visitor or admin sends a message.
2. The backend persists the message.
3. The backend broadcasts `MessageSent` immediately through Reverb.
4. The widget and admin panel receive the message over WebSockets.
5. Lightweight polling remains as a fallback, not the primary transport.

## Redis as the Presence Source of Truth

The presence implementation is intentionally small.

```php
<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class AdminPresence
{
    private const TTL_SECONDS = 30;

    private static function key(string $siteId): string
    {
        return "chatpilot:admin_online:{$siteId}";
    }

    public static function heartbeat(string $siteId): void
    {
        Cache::put(self::key($siteId), true, now()->addSeconds(self::TTL_SECONDS));
    }

    public static function markOffline(string $siteId): void
    {
        Cache::forget(self::key($siteId));
    }

    public static function isOnline(string $siteId): bool
    {
        return (bool) Cache::get(self::key($siteId), false);
    }
}
```

### Why this is efficient

- `heartbeat()` refreshes a short-lived key.
- `markOffline()` removes it immediately.
- `isOnline()` becomes a constant-time cache lookup.
- If the browser disappears unexpectedly, Redis TTL cleans up stale presence automatically.

No database writes are required.

## Heartbeat Endpoints

The admin client sends heartbeat and offline signals to dedicated API endpoints.

```php
<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Events\AdminStatusChanged;
use App\Http\Controllers\Controller;
use App\Support\AdminPresence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PresenceController extends Controller
{
    public function heartbeat(Request $request): JsonResponse
    {
        if ($request->user()->isSuperAdmin()) {
            return response()->json(['online' => false]);
        }

        $siteIds = $request->user()->accessibleSiteIds();

        foreach ($siteIds as $siteId) {
            AdminPresence::heartbeat($siteId);
            AdminStatusChanged::dispatch((string) $siteId, true);
        }

        return response()->json(['online' => true]);
    }

    public function offline(Request $request): JsonResponse
    {
        if ($request->user()->isSuperAdmin()) {
            return response()->json(['online' => false]);
        }

        $siteIds = $request->user()->accessibleSiteIds();

        foreach ($siteIds as $siteId) {
            AdminPresence::markOffline($siteId);
            AdminStatusChanged::dispatch((string) $siteId, false);
        }

        return response()->json(['online' => false]);
    }
}
```

### Important design detail

The controller does two things per state change:

- updates Redis
- broadcasts the change immediately

That means UI and backend state stay aligned.

## Reverb as the Real-Time Delivery Layer

Redis alone is not enough. Redis tells the backend whether an admin is online, but it does not update browser UI by itself.

That is where Laravel Reverb comes in.

The presence event is defined like this:

```php
<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $siteId,
        public bool $online,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel("admin.site.{$this->siteId}");
    }

    public function broadcastWith(): array
    {
        return [
            'online' => $this->online,
        ];
    }
}
```

### Why `ShouldBroadcastNow` matters

Presence is a real-time concern. I do not want online/offline updates waiting behind queue jobs. Using `ShouldBroadcastNow` makes the state transition immediate.

That means:

- admin logs in and the widget sees it immediately
- admin logs out and the widget switches to AI mode immediately

## Frontend Heartbeat Strategy

On the admin side, the frontend sends a heartbeat every 10 seconds while Redis TTL is 30 seconds.

That ratio is deliberate.

- The heartbeat is frequent enough to keep presence fresh.
- The TTL is long enough to tolerate short network jitter.
- Presence disappears automatically if the browser crashes or disconnects.

The client-side logic looks like this:

```js
export function layoutComponent() {
    return {
        sidebarOpen: false,
        _heartbeatInterval: null,
        _beforeUnloadHandler: null,
        _initialized: false,
        _offlineSent: false,

        init() {
            if (this._initialized) return

            this._initialized = true
            this._offlineSent = false
            this.startHeartbeat()

            this._beforeUnloadHandler = () => this.goOffline()
            window.addEventListener('beforeunload', this._beforeUnloadHandler)
        },

        startHeartbeat() {
            if (this._heartbeatInterval) return

            this.sendHeartbeat()
            this._heartbeatInterval = setInterval(() => this.sendHeartbeat(), 10000)
        },

        stopHeartbeat() {
            if (this._heartbeatInterval) {
                clearInterval(this._heartbeatInterval)
                this._heartbeatInterval = null
            }
        },

        async sendHeartbeat() {
            const token = window.__chatpilot_api.getToken()
            if (!token) return

            try {
                await window.__chatpilot_api.post('/v1/admin/presence/heartbeat')
            } catch (error) {
                if (error.message === 'Unauthorized') {
                    this.stopHeartbeat()
                }
            }
        },

        async logout() {
            this.stopHeartbeat()
            await this.goOffline()

            if (window.__chatpilot_app && typeof window.__chatpilot_app.logout === 'function') {
                window.__chatpilot_app.logout({ skipOffline: true })
            }
        },

        async goOffline() {
            if (this._offlineSent) return

            try {
                const token = window.__chatpilot_api.getToken()
                if (!token) return

                this._offlineSent = true

                await fetch(window.location.origin + '/api/v1/admin/presence/offline', {
                    method: 'POST',
                    keepalive: true,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${token}`,
                    },
                    body: '{}',
                })
            } catch {
                this._offlineSent = false
            }
        }
    }
}
```

## Preventing Duplicate Presence Events

One subtle issue in presence systems is duplicate heartbeats or duplicate offline calls.

This can happen when:

- a component initializes twice
- multiple intervals are started
- logout and `beforeunload` both trigger offline logic
- stale tabs keep sending requests

I hardened the frontend logic with a few protections:

- `_initialized` prevents duplicate setup
- `_heartbeatInterval` prevents multiple timers
- `_offlineSent` prevents multiple offline requests
- unauthorized heartbeat stops the timer immediately

These changes removed duplicate Redis `SETEX` and `DEL` operations and made the flow deterministic.

## Realtime Message Delivery

Presence alone is not enough for a support product. Once the admin is online, message delivery also needs to feel immediate.

The first version of ChatPilot used queued broadcasts for `MessageSent`. That kept the HTTP request lighter, but it introduced visible delay in the support flow. In a customer service product, that tradeoff felt wrong. The current implementation broadcasts new messages immediately:

```php
<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Message $message,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel("conversation.{$this->message->conversation_id}");
    }

    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'conversation_id' => $this->message->conversation_id,
                'sender_type' => $this->message->sender_type,
                'sender_id' => $this->message->sender_id,
                'text' => $this->message->text,
                'language' => $this->message->language,
                'created_at' => $this->message->created_at?->toISOString(),
            ],
        ];
    }
}
```

This makes the visitor-side widget and the admin-side panel behave like an actual chat product instead of a background-synced inbox.

## Admin Panel: WebSocket First, Polling Fallback

One practical lesson in this project was that broadcasting messages is not enough by itself. The admin panel also needs to consume those events directly.

The admin SPA now imports Echo/Reverb in its own bundle:

```js
import '../bootstrap'
import Alpine from 'alpinejs'
```

And the conversations page binds to both conversation channels and admin site channels:

```js
export function conversationsPage() {
    const CONVERSATION_POLL_MS = 30000
    const MESSAGE_POLL_MS = 20000

    return {
        _boundConversationIds: new Set(),
        _boundSiteIds: new Set(),

        bindRealtimeChannels() {
            if (!window.Echo) return

            const nextConversationIds = new Set(this.conversations.map(conv => String(conv.id)))
            const nextSiteIds = new Set(
                this.conversations
                    .map(conv => conv.site_id ? String(conv.site_id) : null)
                    .filter(Boolean)
            )

            for (const conversationId of nextConversationIds) {
                if (this._boundConversationIds.has(conversationId)) continue

                window.Echo.channel(`conversation.${conversationId}`)
                    .listen('MessageSent', (event) => this.handleRealtimeMessage(event?.message))
                    .listen('MessageRead', () => this.handleRealtimeRead(conversationId))
                    .listen('VisitorStatusChanged', (event) => this.handleVisitorStatus(conversationId, event?.online))

                this._boundConversationIds.add(conversationId)
            }

            for (const siteId of nextSiteIds) {
                if (this._boundSiteIds.has(siteId)) continue

                window.Echo.channel(`admin.site.${siteId}`)
                    .listen('NewConversation', () => this.loadConversations(true))

                this._boundSiteIds.add(siteId)
            }
        },
    }
}
```

This changed the admin panel from a polling-first UI into a websocket-first UI.

Polling still exists, but now it plays a smaller role:

- conversation list refresh: every 30 seconds
- active conversation message refresh: every 20 seconds

That means:

- normal operation is realtime
- if a broadcast is missed, the UI eventually self-heals
- the system does not depend entirely on perfect WebSocket delivery

## How Redis and Reverb Work Together

This is the key idea of the whole design.

### Redis answers: "What is true right now?"
Redis stores whether an admin is online.

### Reverb answers: "Who needs to know right now?"
Reverb pushes that change to connected clients.

Those are different jobs.

If I only used Reverb:

- the UI could update
- but backend business rules would have no short-lived source of truth

If I only used Redis:

- backend decisions would be correct
- but the frontend would need polling and feel less real-time

Using both gives the best of both.

For presence:

- Redis is the authoritative state store
- Reverb distributes online/offline changes instantly

For messages:

- the database is the durable message store
- Reverb distributes new messages instantly
- polling remains a safety net

That split is deliberate. Presence is ephemeral and belongs in Redis. Messages are durable and belong in the database.

## Presence-Driven AI Fallback

The most important business rule in ChatPilot is that AI should only respond when no admin is online.

That logic lives in the message observer.

```php
<?php

namespace App\Observers;

use App\Jobs\ProcessAiResponse;
use App\Models\Message;
use App\Support\AdminPresence;

class MessageObserver
{
    public function created(Message $message): void
    {
        $conversation = $message->conversation;
        $site = $conversation->site;

        if ($message->sender_type !== 'visitor') {
            return;
        }

        $effectiveAiKey = $site->ai_api_key
            ?: config("chatpilot.default_ai_keys.{$site->ai_provider}")
            ?: config('chatpilot.default_ai_key');

        if ($site->ai_provider === 'none' || ! $effectiveAiKey) {
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
```

This is where the architecture really pays off.

The AI decision is not based on frontend state. It is not based on the last broadcast event. It is based on Redis.

That is exactly what you want.

## Widget-Side Presence Awareness

The widget also needs to know whether the admin is available.

On initial load, it reads presence from config:

```php
return response()->json([
    'site_id' => (string) $site->id,
    'settings' => $site->settings,
    'admin_online' => AdminPresence::isOnline((string) $site->id),
]);
```

Then it subscribes to real-time status updates through Reverb and updates the UI immediately when an `AdminStatusChanged` event arrives.

The widget also has a small polling fallback so that silent disconnects or missed presence events eventually correct themselves without adding new backend infrastructure.

So the widget gets:

- initial truth from the API
- live updates from WebSockets
- periodic correction from lightweight polling

## Why This Is More Scalable Than Database Presence

A database-backed presence table would require:

- frequent writes
- stale row cleanup
- more complicated disconnect handling
- more contention under load

With Redis + Reverb:

- heartbeat writes are cheap
- disconnect cleanup is automatic via TTL
- online checks are instant
- UI stays live without heavy polling

That is a better fit for a real-time support product.

## Operational Notes

There are still a few practical details worth mentioning.

### 1. Browser close is best effort
The `beforeunload` offline request helps, but it is not guaranteed.

That is why the TTL matters.

### 2. TTL is your safety net
If the browser crashes or the connection dies, Redis automatically expires the key and presence self-heals.

### 3. Reverb is for user experience, not truth
Even if a WebSocket event is missed, the backend still has the correct presence state in Redis.

### 4. Polling fallback is a pragmatic compromise
For a mid-sized support product, a websocket-first plus polling-fallback model is often a better tradeoff than adding Redis keyspace notification workers. It keeps the operational model simpler while still correcting stale UI state.

## Final Thoughts

The admin presence system in ChatPilot works well because it avoids overengineering and gives each component one job:

- Redis stores ephemeral presence state
- Reverb broadcasts state changes and new messages instantly
- Laravel logic enforces business behavior

That makes the system:

- fast
- resilient
- low-maintenance
- easy to reason about

Most importantly, it makes presence more than a visual indicator. It becomes part of the product's operational logic. In ChatPilot, that means admins get priority when online, AI only takes over when they are actually unavailable, and both the widget and admin panel behave like a real-time support tool rather than a periodically refreshed dashboard.
