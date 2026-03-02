# ChatPilot — Chat + AI Chatbot Microservice

## Project Summary

A plug-and-play, AI-powered live chat microservice that can be integrated into any website with a single script tag. Site owners manage conversations via an admin panel; when the admin is offline, AI responds automatically.

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────────┐
│  Any website                                            │
│  <script src="chat.sezginyurdakul.com/widget.js"        │
│          data-site-key="sk_xxx"></script>                │
└──────────────┬──────────────────────────────────────────┘
               │ WebSocket + REST API
               ▼
┌─────────────────────────────────────────────────────────┐
│  ChatPilot API  (chat.sezginyurdakul.com)               │
│                                                         │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐              │
│  │ Laravel  │  │ Reverb   │  │ Queue    │              │
│  │ API      │  │ WebSocket│  │ Worker   │              │
│  │ (PHP-FPM)│  │ Server   │  │          │              │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘              │
│       │              │              │                    │
│  ┌────▼──────────────▼──────────────▼─────┐             │
│  │         PostgreSQL + Redis              │             │
│  └─────────────────────────────────────────┘             │
│       │                                                  │
│  ┌────▼─────┐                                           │
│  │ AI Layer │ Gemini / OpenAI / Claude                  │
│  └──────────┘                                           │
└─────────────────────────────────────────────────────────┘
```

---

## Tech Stack

| Layer | Technology | Why |
|-------|-----------|-----|
| **Backend** | Laravel 12 + PHP 8.4 | Proven stack, Sanctum, queue, broadcasting |
| **Realtime** | Laravel Reverb | Free, self-hosted WebSocket, Laravel native |
| **Auth** | Laravel Sanctum | Token auth for site owners, API key for widget |
| **Database** | PostgreSQL 16 | JSON columns, full-text search, multi-tenant |
| **Cache/Queue** | Redis 7 | Rate limiting, queue jobs, broadcasting |
| **AI** | Gemini / OpenAI / Claude | Configurable per site |
| **Widget** | Vanilla JS + Shadow DOM | Framework-agnostic, single script tag |
| **Admin Panel** | React SPA (or Inertia) | Conversation management, settings, analytics |

---

## Database Design

### `users` — Site owners / admins

```
id              UUID PRIMARY KEY
name            VARCHAR(255)
email           VARCHAR(255) UNIQUE
password        VARCHAR(255)
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

### `sites` — Registered websites

```
id              UUID PRIMARY KEY
owner_id        UUID → users
name            VARCHAR(255)           -- "Sezgin Portfolio"
domain          VARCHAR(255)           -- "sezginyurdakul.com"
api_key         VARCHAR(64) UNIQUE     -- "sk_abc123..."
ai_provider     ENUM(gemini, openai, claude, none) DEFAULT none
ai_api_key      TEXT (encrypted)       -- Site owner's own AI key
ai_system_prompt TEXT                  -- AI personality prompt
settings        JSONB                  -- Widget settings (see below)
is_active       BOOLEAN DEFAULT true
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

**`settings` JSON structure:**

```json
{
  "widget": {
    "position": "bottom-right",
    "primary_color": "#7c3aed",
    "greeting": "Hi! How can I help you?",
    "placeholder": "Type a message...",
    "avatar_url": null
  },
  "languages": ["en", "nl"],
  "auto_translate": true,
  "require_name": true,
  "rate_limit": {
    "cooldown_seconds": 3,
    "max_messages_per_hour": 50
  },
  "ai": {
    "respond_when_offline": true,
    "max_history_messages": 10
  }
}
```

### `conversations` — Chat conversations

```
id              UUID PRIMARY KEY
site_id         UUID → sites
visitor_token   VARCHAR(64)            -- Anonymous session token
visitor_name    VARCHAR(255) NULLABLE
status          ENUM(active, closed, archived) DEFAULT active
metadata        JSONB                  -- ip, user_agent, page_url, language
last_message_at TIMESTAMP NULLABLE
created_at      TIMESTAMP
updated_at      TIMESTAMP

INDEX: (site_id, status, last_message_at DESC)
INDEX: (visitor_token)
```

### `messages` — Messages

```
id              UUID PRIMARY KEY
conversation_id UUID → conversations
sender_type     ENUM(visitor, admin, ai)
sender_id       UUID NULLABLE          -- Admin user id (null for visitor/ai)
text            TEXT
language        VARCHAR(5)             -- "en", "nl", "tr"
translations    JSONB                  -- {"en": "Hello", "nl": "Hallo"}
read_at         TIMESTAMP NULLABLE
created_at      TIMESTAMP

INDEX: (conversation_id, created_at)
```

### `ai_logs` — AI usage tracking

```
id              UUID PRIMARY KEY
conversation_id UUID → conversations
site_id         UUID → sites
provider        VARCHAR(20)            -- "gemini", "openai"
model           VARCHAR(50)            -- "gemini-2.5-flash-lite"
prompt_tokens   INTEGER
completion_tokens INTEGER
response_time_ms INTEGER
error           TEXT NULLABLE
created_at      TIMESTAMP

INDEX: (site_id, created_at)
```

---

## API Endpoints

### Public — Widget ↔ API

Used by the embeddable widget. Auth: `X-Site-Key` header.

```
POST   /api/v1/conversations
       Body: { visitor_name?: string, metadata?: object }
       Response: { id, visitor_token }

GET    /api/v1/conversations/{id}/messages
       Header: X-Visitor-Token: {token}
       Query: ?after={message_id}  (incremental fetch)
       Response: { messages: [...] }

POST   /api/v1/conversations/{id}/messages
       Header: X-Visitor-Token: {token}
       Body: { text: string, language?: string }
       Response: { message: {...} }

GET    /api/v1/site/config
       Response: { settings: {...}, admin_online: bool }
```

### WebSocket — Laravel Reverb

```
Channel: conversation.{id}
  Events:
    - MessageSent       { message }
    - MessageRead       { message_id, read_at }
    - TypingStarted     { sender_type }
    - TypingStopped     { sender_type }

Channel: admin.site.{siteId}
  Events:
    - AdminStatusChanged  { online: bool }
    - NewConversation     { conversation }
    - ConversationUpdated { conversation }
```

### Admin API — Sanctum Auth

```
-- Auth --
POST   /api/v1/auth/login          { email, password } → { token }
POST   /api/v1/auth/logout
GET    /api/v1/auth/user

-- Conversations --
GET    /api/v1/admin/conversations
       Query: ?status=active&search=john&page=1
       Response: { data: [...], meta: { total, per_page } }

GET    /api/v1/admin/conversations/{id}
       Response: { conversation, messages: [...] }

POST   /api/v1/admin/conversations/{id}/messages
       Body: { text: string }

PATCH  /api/v1/admin/conversations/{id}
       Body: { status: "closed" | "archived" }

POST   /api/v1/admin/conversations/{id}/read
       (Mark all messages as read)

-- Site Management --
GET    /api/v1/admin/sites
POST   /api/v1/admin/sites
       Body: { name, domain, ai_provider?, ai_api_key?, settings? }

PATCH  /api/v1/admin/sites/{id}
POST   /api/v1/admin/sites/{id}/regenerate-key

-- Analytics --
GET    /api/v1/admin/stats
       Query: ?period=7d
       Response: {
         total_conversations,
         total_messages,
         avg_response_time,
         ai_messages_count,
         ai_cost_estimate,
         conversations_by_day: [...]
       }

-- Health --
GET    /api/health
       Response: { status: "ok", version: "1.0.0" }
```

---

## Widget Integration

### Usage (End User)

```html
<!-- Single line integration -->
<script
  src="https://chat.sezginyurdakul.com/widget.js"
  data-site-key="sk_abc123"
></script>
```

### Optional Overrides

```html
<script
  src="https://chat.sezginyurdakul.com/widget.js"
  data-site-key="sk_abc123"
  data-position="bottom-left"
  data-language="nl"
  data-greeting="Hello! How can I help you?"
></script>
```

### Widget Technical Specs

- **Shadow DOM** — Isolated from host site CSS
- **Lazy load** — Loads async after page load (~30KB gzipped)
- **Auto-reconnect** — Exponential backoff on WebSocket disconnect
- **Offline queue** — Stores messages in localStorage when offline
- **Theme inheritance** — Colors/style from admin panel settings
- **i18n** — Auto-detect via `navigator.language`
- **Responsive** — Mobile/tablet/desktop compatible
- **Accessibility** — ARIA labels, keyboard navigation

### Widget Build Structure

```
chat-widget/
├── src/
│   ├── widget.ts          -- Entry point, script tag parser
│   ├── ChatWidget.ts      -- Main widget class
│   ├── WebSocketClient.ts -- Reverb connection
│   ├── ApiClient.ts       -- REST API client
│   ├── styles.css         -- Shadow DOM styles
│   └── i18n/
│       ├── en.ts
│       ├── nl.ts
│       └── tr.ts
├── vite.config.ts         -- IIFE build, single file output
└── package.json
```

Build output: `widget.js` (single file, IIFE format)

---

## Laravel Project Structure

```
chatpilot/
├── app/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Site.php
│   │   ├── Conversation.php
│   │   ├── Message.php
│   │   └── AiLog.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/V1/
│   │   │       ├── Widget/
│   │   │       │   ├── ConversationController.php
│   │   │       │   ├── MessageController.php
│   │   │       │   └── ConfigController.php
│   │   │       └── Admin/
│   │   │           ├── AuthController.php
│   │   │           ├── ConversationController.php
│   │   │           ├── SiteController.php
│   │   │           └── StatsController.php
│   │   ├── Middleware/
│   │   │   ├── ValidateSiteKey.php
│   │   │   ├── ValidateVisitorToken.php
│   │   │   └── RateLimitChat.php
│   │   └── Requests/
│   │       ├── SendMessageRequest.php
│   │       ├── CreateSiteRequest.php
│   │       └── UpdateSiteRequest.php
│   ├── Services/
│   │   ├── AiService.php              -- AI provider factory
│   │   ├── Ai/
│   │   │   ├── GeminiProvider.php
│   │   │   ├── OpenAiProvider.php
│   │   │   └── AiProviderInterface.php
│   │   ├── TranslationService.php
│   │   └── ChatService.php
│   ├── Jobs/
│   │   ├── ProcessAiResponse.php      -- Async AI response
│   │   └── TranslateMessage.php       -- Async translation
│   ├── Events/
│   │   ├── MessageSent.php
│   │   ├── MessageRead.php
│   │   ├── TypingStarted.php
│   │   ├── AdminStatusChanged.php
│   │   └── NewConversation.php
│   ├── Policies/
│   │   ├── ConversationPolicy.php
│   │   └── SitePolicy.php
│   └── Observers/
│       └── MessageObserver.php        -- AI trigger on new visitor message
├── database/
│   └── migrations/
│       ├── create_sites_table.php
│       ├── create_conversations_table.php
│       ├── create_messages_table.php
│       └── create_ai_logs_table.php
├── routes/
│   ├── api.php                        -- All API routes
│   └── channels.php                   -- Reverb channel auth
├── config/
│   ├── reverb.php
│   └── chatpilot.php                  -- App config
├── docker/
│   ├── php/Dockerfile
│   ├── nginx/default.conf
│   └── reverb/Dockerfile
├── widget/                            -- Embeddable widget source
│   ├── src/
│   ├── dist/widget.js
│   └── vite.config.ts
├── docker-compose.yml
├── docker-compose.prod.yml
└── .env.example
```

---

## Docker Compose (Production)

```yaml
services:
  app:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    volumes:
      - .:/var/www/html
    depends_on:
      - postgres
      - redis
    networks:
      - chatpilot

  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
    volumes:
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf:ro
      - ./widget/dist:/var/www/widget:ro
    depends_on:
      - app
      - reverb
    networks:
      - chatpilot

  reverb:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    command: php artisan reverb:start --host=0.0.0.0 --port=8080
    expose:
      - "8080"
    depends_on:
      - postgres
      - redis
    networks:
      - chatpilot

  queue:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    command: php artisan queue:work redis --sleep=3 --tries=3
    depends_on:
      - postgres
      - redis
    networks:
      - chatpilot

  scheduler:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    command: php artisan schedule:work
    depends_on:
      - postgres
      - redis
    networks:
      - chatpilot

  postgres:
    image: postgres:16-alpine
    environment:
      POSTGRES_DB: chatpilot
      POSTGRES_USER: chatpilot
      POSTGRES_PASSWORD: ${DB_PASSWORD}
    volumes:
      - postgres_data:/var/lib/postgresql/data
    networks:
      - chatpilot

  redis:
    image: redis:7-alpine
    volumes:
      - redis_data:/data
    networks:
      - chatpilot

volumes:
  postgres_data:
  redis_data:

networks:
  chatpilot:
    driver: bridge
```

---

## Nginx Configuration

```nginx
server {
    listen 80;
    server_name chat.sezginyurdakul.com;

    # Widget JS (CDN-like, long cache)
    location = /widget.js {
        alias /var/www/widget/widget.js;
        add_header Cache-Control "public, max-age=3600";
        add_header Access-Control-Allow-Origin "*";
    }

    # WebSocket (Reverb)
    location /app {
        proxy_pass http://reverb:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }

    # API
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_param SCRIPT_FILENAME /var/www/html/public/$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

## Cloudflare Tunnel Integration

Add to `config.yml`:

```yaml
ingress:
  - hostname: chat.sezginyurdakul.com
    service: http://chatpilot_nginx:80
```

---

## Message Flow Diagram

```
Visitor Widget                    ChatPilot API                    Admin Panel
     │                                │                                │
     │  POST /conversations           │                                │
     │ ─────────────────────────────► │                                │
     │  ◄──── { id, visitor_token }   │                                │
     │                                │                                │
     │  WS: subscribe                 │                                │
     │    conversation.{id}           │                                │
     │ ─────────────────────────────► │  Event: NewConversation        │
     │                                │ ─────────────────────────────► │
     │                                │                                │
     │  POST /conversations/{id}/     │                                │
     │       messages                 │                                │
     │  { text: "Hello" }            │                                │
     │ ─────────────────────────────► │                                │
     │                                │  Event: MessageSent            │
     │                                │ ─────────────────────────────► │
     │                                │                                │
     │                                │  Admin offline?                │
     │                                │  ───► Queue: ProcessAiResponse │
     │                                │                                │
     │  Event: TypingStarted (ai)     │  ◄── AI processes...           │
     │ ◄───────────────────────────── │                                │
     │                                │                                │
     │  Event: MessageSent (ai)       │  AI reply saved                │
     │ ◄───────────────────────────── │                                │
     │                                │                                │
     │                                │         Admin sends reply      │
     │                                │ ◄───────────────────────────── │
     │  Event: MessageSent (admin)    │                                │
     │ ◄───────────────────────────── │                                │
```

---

## Rate Limiting Strategy

### Widget (Visitor) Side — Redis

```
Middleware: RateLimitChat

Rules:
- visitor:{token}:cooldown  → 1 message / 3 seconds (Redis TTL)
- visitor:{token}:hourly    → 50 messages / hour (Redis counter + TTL)
- site:{siteId}:daily       → Configurable daily limit

HTTP 429 Response:
{
  "error": "rate_limit_exceeded",
  "retry_after": 3,
  "message": "Please wait before sending another message"
}
```

### AI Side — Queue Rate Limit

```
Queue: ProcessAiResponse

- Redis rate limiter: max 10 AI requests/minute per site
- Retry: 3 attempts with exponential backoff
- Timeout: 30 seconds per AI call
- Fallback: "AI is currently unavailable" message
```

---

## AI Provider Architecture

```php
interface AiProviderInterface
{
    public function chat(string $message, array $history, string $systemPrompt): string;
    public function getTokenCount(string $text): int;
}

// Factory pattern
class AiService
{
    public function resolve(Site $site): AiProviderInterface
    {
        return match($site->ai_provider) {
            'gemini'  => new GeminiProvider($site->ai_api_key),
            'openai'  => new OpenAiProvider($site->ai_api_key),
            'claude'  => new ClaudeProvider($site->ai_api_key),
            default   => throw new AiProviderNotConfigured(),
        };
    }
}
```

### AI Response Queue Job

```php
class ProcessAiResponse implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;

    public function handle(AiService $aiService): void
    {
        // 1. Get conversation history
        // 2. Call AI provider
        // 3. Save AI message
        // 4. Log usage (tokens, cost)
        // 5. Broadcast MessageSent event
    }

    public function middleware(): array
    {
        return [
            new RateLimited('ai-response'),
        ];
    }
}
```

---

## Implementation Phases

### Phase 1 — MVP Foundation (1-2 weeks)

**Goal:** Core API working, widget can send and receive messages (polling).

- [ ] Create Laravel 12 project + Docker setup
- [ ] PostgreSQL + Redis connection
- [ ] Database migrations (sites, conversations, messages)
- [ ] Site model + API key generation
- [ ] ValidateSiteKey middleware
- [ ] Widget API endpoints (conversations, messages)
- [ ] RateLimitChat middleware (Redis)
- [ ] Admin auth endpoints (Sanctum)
- [ ] Admin conversation endpoints
- [ ] Basic widget (polling, Shadow DOM)
- [ ] Integrate with portfolio site (test)
- [ ] Health endpoint

### Phase 2 — Realtime (1 week)

**Goal:** WebSocket-based instant messaging.

- [ ] Laravel Reverb setup + config
- [ ] Broadcasting events (MessageSent, MessageRead, Typing)
- [ ] Channel authorization (conversation.{id}, admin.site.{siteId})
- [ ] Widget WebSocket client (Reverb JS)
- [ ] Admin panel WebSocket integration
- [ ] Admin online/offline status
- [ ] Typing indicator (visitor + admin)
- [ ] Auto-reconnect + offline queue

### Phase 3 — AI Integration (1 week)

**Goal:** AI auto-responds when admin is offline.

- [ ] AiProviderInterface + GeminiProvider
- [ ] OpenAiProvider
- [ ] AiService factory
- [ ] ProcessAiResponse queue job
- [ ] MessageObserver — trigger AI when admin offline
- [ ] ai_logs table + logging
- [ ] Admin panel AI settings (provider, key, prompt)
- [ ] Admin panel AI usage stats
- [ ] Rate limiting for AI calls

### Phase 4 — Production Polish (1 week)

**Goal:** Production-ready, deployed.

- [ ] Message translation support (TranslateMessage job)
- [ ] Widget theme customization (from admin panel)
- [ ] Analytics dashboard (conversations/day, response times)
- [ ] widget.js CDN build + versioning
- [ ] Cloudflare Tunnel integration
- [ ] Production Docker Compose
- [ ] Deploy to chat.sezginyurdakul.com
- [ ] Remove Firebase chat from portfolio, switch to widget
- [ ] Error tracking (Sentry or similar)
- [ ] API documentation

---

## Migration from Current System

| Current (Portfolio) | New (ChatPilot) |
|---------------------|-----------------|
| Firebase Realtime DB | PostgreSQL + Reverb WebSocket |
| Firebase Auth (anonymous) | Anonymous visitor token (UUID) |
| Express.js AI backend | Laravel queue job + AI service |
| ChatContext.jsx (React) | widget.js (standalone, Shadow DOM) |
| ChatWidget.jsx (React) | widget.js embed |
| database.rules.json | Laravel middleware + policy |
| In-memory rate limit | Redis rate limiter |
| `sender: 'ai'` flag | `sender_type` ENUM column |
| Google Translate API | TranslateMessage job (same API) |
| nginx-docker.conf proxy | ChatPilot nginx config |

### Firebase → PostgreSQL Data Migration

```
Firebase: chats/{visitorId}/messages/{msgId}
    ↓
PostgreSQL: conversations + messages tables

Migration script:
1. Export all chats from Firebase (JSON)
2. Each visitorId → conversations row
3. Each message → messages row
4. Timestamp mapping
```

---

## Environment Variables (.env.example)

```env
APP_NAME=ChatPilot
APP_ENV=production
APP_URL=https://chat.sezginyurdakul.com

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=chatpilot
DB_USERNAME=chatpilot
DB_PASSWORD=secret

REDIS_HOST=redis
REDIS_PORT=6379

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

BROADCAST_CONNECTION=reverb
REVERB_APP_ID=chatpilot
REVERB_APP_KEY=your-reverb-key
REVERB_APP_SECRET=your-reverb-secret
REVERB_HOST=0.0.0.0
REVERB_PORT=8080
REVERB_SCHEME=http

# Widget CORS
CHATPILOT_ALLOWED_ORIGINS=*

# Default AI (optional, overridden per site)
CHATPILOT_DEFAULT_AI_PROVIDER=gemini
CHATPILOT_DEFAULT_AI_KEY=your-gemini-key
```

---

## Security Checklist

- [ ] API keys stored hashed (bcrypt)
- [ ] AI API keys encrypted (Laravel encrypt)
- [ ] CORS origin restricted to site domain
- [ ] Rate limiting on all public endpoints
- [ ] Input validation (max 1000 chars, XSS sanitize)
- [ ] SQL injection protection (Eloquent ORM)
- [ ] Visitor token UUID v4 (unpredictable)
- [ ] Admin endpoints protected by Sanctum
- [ ] WebSocket channel authorization
- [ ] Sensitive data excluded from logs
- [ ] HTTPS only (Cloudflare)

---

## Notes

- **Why PostgreSQL over Firebase?** Multi-tenant, SQL query flexibility, analytics, backup, Laravel native.
- **Why Reverb?** Free, self-hosted, Laravel native, Pusher protocol compatible.
- **Why Shadow DOM?** Prevents CSS conflicts with host site, works in isolation.
- **Why Queue for AI?** AI response should be non-blocking and async. Visitor sees "AI typing" immediately.
- **Scalability:** Horizontal scaling via app + queue worker replicas. Redis and PostgreSQL single instance sufficient at start.
