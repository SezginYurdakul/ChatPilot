# ChatPilot

AI-powered live chat backend for websites. Drop a widget on any site and let visitors chat with your team or an AI assistant in real time.

Built with Laravel 12, PostgreSQL, Redis, and Laravel Reverb for WebSocket support.

## Features

- **Multi-site support** — Manage multiple websites from a single installation
- **AI auto-reply** — Pluggable AI providers (Gemini, OpenAI) respond when admins are offline
- **Real-time messaging** — WebSocket-powered via Laravel Reverb (typing indicators, instant delivery)
- **Auto-translation** — Messages translated between admin and visitor languages via Google Translate
- **Admin presence** — Real-time online/offline status with heartbeat; AI only responds when admin is offline
- **Admin panel** — Built-in SPA at `/admin` with Alpine.js (dashboard, conversations, sites)
- **Schema-driven settings** — Add new site settings without code changes
- **Rate limiting** — Per-site configurable message throttling
- **Usage tracking** — Token counts, response times, and error logs for every AI call
- **Role-based access** — Super admin and admin roles with middleware-protected endpoints
- **Test suite** — 99 tests with 249 assertions covering all endpoints and services

## Architecture

```
┌─────────────┐     ┌─────────┐     ┌──────────┐
│  Widget JS  │────▶│  Nginx  │────▶│  Laravel  │
└─────────────┘     └─────────┘     └────┬─────┘
                                         │
                    ┌────────────────┬────┴────┬──────────────┐
                    ▼                ▼         ▼              ▼
              ┌──────────┐   ┌───────────┐  ┌───────┐  ┌──────────┐
              │ PostgreSQL│   │   Redis   │  │Reverb │  │  Queue   │
              │          │   │  (cache/  │  │(WS)   │  │ Worker   │
              │          │   │   queue)  │  │       │  │          │
              └──────────┘   └───────────┘  └───────┘  └────┬─────┘
                                                            │
                                                     ┌──────┴──────┐
                                                     │  AI Provider │
                                                     │ Gemini/GPT  │
                                                     └─────────────┘
```

## Tech Stack

| Component | Technology |
|-----------|-----------|
| Framework | Laravel 12, PHP 8.2+ |
| Database | PostgreSQL 16 |
| Cache / Queue | Redis 7 |
| WebSocket | Laravel Reverb |
| Auth | Laravel Sanctum (token-based) |
| AI Providers | Google Gemini, OpenAI (pluggable) |
| Translation | Google Translate (`stichoza/google-translate-php`) |
| Admin Frontend | Alpine.js + Tailwind CSS |
| Containerization | Docker Compose |
| Testing | PHPUnit 11, SQLite in-memory |

## Quick Start

### Prerequisites

- Docker & Docker Compose
- A Gemini or OpenAI API key (optional, for AI features)

### Setup

```bash
# Clone the repository
git clone <repo-url> ChatPilot && cd ChatPilot

# Copy environment file
cp .env.example .env

# Start all services
docker compose up -d

# Install dependencies & set up database
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed

# Verify
curl http://localhost:8080/api/health
# → {"status":"ok","version":"1.0.0"}
```

### Services

| Service | Port | Description |
|---------|------|-------------|
| Nginx | `8080` | HTTP API gateway |
| PostgreSQL | `5432` | Primary database |
| Redis | `6379` | Queue broker & cache |
| Reverb | `9090` | WebSocket server |
| Queue Worker | — | Processes AI responses asynchronously |

## API Reference

Base URL: `http://localhost:8080/api`

### Health Check

```
GET /health → {"status":"ok","version":"1.0.0"}
```

### Error Envelope

API errors return a stable envelope:

```json
{
  "error": "validation_failed",
  "code": "CP-REQ-001",
  "message": "The email field is required."
}
```

`error` is machine-friendly category, `code` is the stable application error code.

### Widget API

Used by the chat widget embedded on customer websites. Authenticated via `X-Site-Key` header.

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/v1/site/config` | Get widget configuration |
| `POST` | `/v1/conversations` | Start a new conversation |
| `GET` | `/v1/conversations/{id}/messages` | Get message history |
| `POST` | `/v1/conversations/{id}/messages` | Send a visitor message |

**Required headers:**
```
X-Site-Key: sk_your_site_api_key
X-Visitor-Token: token_from_create_conversation    # message endpoints only
```

### Admin Auth

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/v1/auth/login` | Login, returns Bearer token |
| `GET` | `/v1/auth/user` | Get current user (includes `role`) |
| `POST` | `/v1/auth/logout` | Invalidate token |

### Admin Conversations

All endpoints require `Authorization: Bearer <token>`.

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/v1/admin/conversations` | List conversations (`?status=active&search=john`) |
| `GET` | `/v1/admin/conversations/{id}` | Conversation detail with messages |
| `PATCH` | `/v1/admin/conversations/{id}` | Update status (`active` / `closed` / `archived`) |
| `POST` | `/v1/admin/conversations/{id}/messages` | Send message as admin |
| `POST` | `/v1/admin/conversations/{id}/read` | Mark visitor messages as read |

### Admin Sites

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/v1/admin/sites` | List all sites |
| `GET` | `/v1/admin/sites/settings-schema` | Get dynamic settings schema |
| `POST` | `/v1/admin/sites` | Create a new site |
| `PATCH` | `/v1/admin/sites/{id}` | Update site settings |
| `POST` | `/v1/admin/sites/{id}/regenerate-key` | Regenerate API key |

### Admin Presence

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/v1/admin/presence/heartbeat` | Mark admin online (45s TTL, call every 30s) |
| `POST` | `/v1/admin/presence/offline` | Mark admin offline (on logout/tab close) |

### Admin User Management (Super Admin Only)

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/v1/admin/users` | List all users |
| `POST` | `/v1/admin/users` | Create a new user (`role`: `admin` or `super_admin`) |
| `DELETE` | `/v1/admin/users/{id}` | Delete a user (cannot delete self) |

### Admin Analytics

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/v1/admin/stats?period=7d` | Dashboard statistics (`7d`, `30d`, `90d`) |

**Stats response includes:** total conversations, total messages, AI message count, average AI response time, conversations-by-day chart data, 5xx error rate, and queue job failure rate.

## AI Providers

ChatPilot supports multiple AI providers through a pluggable interface. Each site can have its own provider and API key.

### Supported Providers

| Provider | Default Model | Config Value |
|----------|---------------|-------------|
| Google Gemini | gemini-2.5-flash-lite | `gemini` |
| OpenAI | gpt-4o-mini | `openai` |
| None (manual only) | — | `none` |

### Configuring AI for a Site

```bash
curl -X PATCH http://localhost:8080/api/v1/admin/sites/{site_id} \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <token>" \
  -d '{
    "ai_provider": "gemini",
    "ai_api_key": "your-gemini-api-key",
    "ai_system_prompt": "You are a helpful support assistant. Be concise and friendly."
  }'
```

### How AI Responses Work

```
Visitor sends message
  → MessageObserver detects new visitor message
  → Dispatches TranslateMessage job (visitor → admin language)
  → Checks: AI provider configured? respond_when_offline enabled? Admin offline?
  → Dispatches ProcessAiResponse job to Redis queue
  → Queue worker picks up the job
    → Broadcasts "AI is typing..." indicator
    → Builds conversation history (last 10 messages)
    → Calls AI provider API with message + history + system prompt
    → Saves AI response as message (sender_type: "ai")
    → Broadcasts message via WebSocket
    → Logs token usage and response time to ai_logs table
  → On failure: sends fallback message + logs error

Admin sends message
  → MessageObserver detects new admin message
  → Dispatches TranslateMessage job (admin → visitor language)
```

### Adding a New Provider

1. Create a class implementing `AiProviderInterface`:

```php
// app/Services/Ai/ClaudeProvider.php
class ClaudeProvider implements AiProviderInterface
{
    public function __construct(private string $apiKey, private string $model = 'claude-sonnet-4-6') {}

    public function chat(string $message, array $history, string $systemPrompt): array
    {
        // Call the API and return:
        return [
            'text'              => $responseText,
            'prompt_tokens'     => $inputTokens,
            'completion_tokens' => $outputTokens,
            'model'             => $this->model,
        ];
    }
}
```

2. Register it in `config/chatpilot.php`:

```php
'providers' => [
    'gemini'  => GeminiProvider::class,
    'openai'  => OpenAiProvider::class,
    'claude'  => ClaudeProvider::class,  // ← add this
],
```

That's it. The provider is immediately available via the admin API.

## Real-time Events

ChatPilot uses Laravel Reverb for WebSocket broadcasting on two channel families.

### Conversation Channel: `conversation.{id}`

| Event | Payload | Trigger |
|-------|---------|---------|
| `MessageSent` | `{message: {...}}` | Any new message (visitor, admin, or AI) |
| `MessageTranslated` | `{message_id, translations}` | Translation ready for a message |
| `TypingStarted` | `{sender_type: "ai"\|"admin"\|"visitor"}` | Someone started typing |
| `MessageRead` | `{read_at: "ISO8601"}` | Admin marks messages as read |

### Admin Channel: `admin.site.{siteId}`

| Event | Payload | Trigger |
|-------|---------|---------|
| `NewConversation` | `{conversation: {...}}` | Visitor starts a new conversation |
| `AdminStatusChanged` | `{online: bool}` | Admin goes online/offline |

### Connecting from the Widget

```javascript
Echo.channel(`conversation.${conversationId}`)
  .listen('MessageSent', (e) => appendMessage(e.message))
  .listen('MessageTranslated', (e) => updateTranslation(e.message_id, e.translations))
  .listen('TypingStarted', (e) => showTypingIndicator(e.sender_type));
```

## Auto-Translation

Messages are automatically translated between admin and visitor languages using Google Translate.

- **Visitor messages** are translated to the admin's language (`site.settings.language`, default: `en`)
- **Admin messages** are translated to the visitor's language (`conversation.metadata.language`)
- Translations are stored in the `translations` JSON column — the original text is never overwritten
- Only languages in the allowlist are translated (configured in `config/chatpilot.php`):

```php
'translation' => [
    'allowed_languages' => ['en', 'nl', 'de', 'fr', 'es', 'pt', 'tr', 'zh', 'ja', 'ko', 'ar', 'ru', 'hi'],
],
```

Translation runs asynchronously via the `TranslateMessage` queue job. When complete, a `MessageTranslated` event is broadcast so the widget/admin panel can update in real time.

## Schema-Driven Settings

Site settings are defined in `config/chatpilot.php` and validated automatically. Add a new field to the schema — it appears in the API with no migrations or code changes.

```php
// config/chatpilot.php → settings_schema
'widget' => [
    'label'  => 'Widget Appearance',
    'fields' => [
        'theme'    => ['type' => 'select',  'default' => 'light',        'options' => ['light', 'dark']],
        'position' => ['type' => 'select',  'default' => 'bottom-right', 'options' => ['bottom-right', 'bottom-left']],
        'greeting' => ['type' => 'text',    'default' => 'Hi! How can we help you?'],
    ],
],
'ai' => [
    'label'  => 'AI Behavior',
    'fields' => [
        'respond_when_offline' => ['type' => 'boolean', 'default' => true],
    ],
],
```

The frontend fetches the schema via `GET /v1/admin/sites/settings-schema` and renders forms dynamically.

## Project Structure

```
app/
├── Console/
│   └── Commands/
│       └── CreateAdminCommand.php   # php artisan chatpilot:create-admin
├── Events/                          # WebSocket broadcast events
│   ├── AdminStatusChanged.php       # Admin online/offline status
│   ├── MessageRead.php
│   ├── MessageSent.php
│   ├── MessageTranslated.php        # Translation ready notification
│   ├── NewConversation.php          # New visitor conversation
│   └── TypingStarted.php
├── Http/
│   ├── Controllers/Api/V1/
│   │   ├── Admin/                   # Admin panel endpoints
│   │   │   ├── AuthController.php
│   │   │   ├── ConversationController.php
│   │   │   ├── PresenceController.php   # Admin online heartbeat
│   │   │   ├── SiteController.php
│   │   │   ├── StatsController.php
│   │   │   └── UserController.php     # User management (super admin)
│   │   └── Widget/                  # Chat widget endpoints
│   │       ├── ConfigController.php
│   │       ├── ConversationController.php
│   │       └── MessageController.php
│   └── Middleware/
│       ├── EnsureSuperAdmin.php       # Role-based access control
│       ├── RateLimitChat.php
│       ├── ValidateSiteKey.php
│       └── ValidateVisitorToken.php
├── Jobs/
│   ├── ProcessAiResponse.php        # Async AI response generation
│   └── TranslateMessage.php         # Async message translation
├── Models/
│   ├── AiLog.php                    # AI usage tracking
│   ├── Conversation.php
│   ├── Message.php
│   ├── Site.php
│   └── User.php
├── Observers/
│   └── MessageObserver.php          # Triggers AI + translation on messages
├── Support/
│   └── AdminPresence.php            # Redis-backed admin online status
└── Services/
    ├── AiService.php                # Provider factory & history builder
    ├── SettingsValidator.php         # Dynamic validation from schema
    └── Ai/
        ├── AiProviderInterface.php
        ├── GeminiProvider.php
        └── OpenAiProvider.php

resources/js/admin/                  # Admin SPA (Alpine.js)
├── app.js                           # Main Alpine application
├── api.js                           # API client
├── components/layout.js             # Layout component
└── pages/
    ├── login.js
    ├── dashboard.js
    ├── conversations.js
    └── sites.js

config/
└── chatpilot.php                    # Providers, rate limits, settings schema, translation

docker/
├── nginx/
│   ├── default.conf               # Development nginx config
│   └── prod.conf                  # Production nginx config (Cloudflare)
└── php/
    ├── Dockerfile                 # Development Dockerfile
    └── Dockerfile.prod            # Production Dockerfile (multi-stage)

tests/
├── Feature/
│   ├── Auth/                      # Login, logout, user tests
│   ├── Widget/                    # Config, conversation, message tests
│   ├── Admin/                     # Conversations, sites, stats, presence, user management tests
│   └── HealthTest.php
└── Unit/
    ├── Jobs/                      # TranslateMessage tests
    ├── Middleware/                 # EnsureSuperAdmin tests
    ├── Services/                  # AiService, SettingsValidator tests
    └── Observers/                 # MessageObserver tests
```

## Environment Variables

```bash
# Application
APP_KEY=                             # Generated by php artisan key:generate
APP_URL=http://localhost:8080

# Database
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_DATABASE=chatpilot
DB_USERNAME=chatpilot
DB_PASSWORD=secret

# Queue & Cache
QUEUE_CONNECTION=redis
CACHE_STORE=redis
REDIS_HOST=redis

# WebSocket
BROADCAST_CONNECTION=reverb
REVERB_HOST=reverb
REVERB_PORT=9090

# AI (optional — can also be configured per site via the admin API)
CHATPILOT_DEFAULT_AI_PROVIDER=none   # gemini, openai, or none
CHATPILOT_DEFAULT_AI_KEY=            # fallback API key if site has none
GEMINI_API_KEY=                      # for app-level Gemini access

# Observability (optional)
SENTRY_DSN=                          # enables Sentry capture when sentry-laravel is installed
CHATPILOT_ALERT_WEBHOOK_URL=         # webhook for critical alerts (queue failures, unhandled 5xx)
```

## Admin Panel

ChatPilot includes a built-in admin panel at `/admin`, built with Alpine.js and Tailwind CSS.

**Pages:**
- **Login** — Email/password authentication
- **Dashboard** — Conversation stats, message counts, AI usage (7d/30d/90d)
- **Conversations** — Real-time messaging with visitors, translation support, search & filters
- **Sites** — Site CRUD, AI provider config, widget settings, API key management, embed snippet

Access it at `http://localhost:8080/admin` (development) or `https://your-domain/admin` (production).

## User Roles

ChatPilot has two user roles:

| Role | Description | Can manage users? |
|------|-------------|-------------------|
| `super_admin` | Full access. Created via artisan command. | Yes |
| `admin` | Site & conversation management only. Created by super admin. | No |

### Initial Setup (First Super Admin)

```bash
# Interactive mode
docker compose exec app php artisan chatpilot:create-admin

# Non-interactive
docker compose exec app php artisan chatpilot:create-admin \
  --name="Admin" --email="admin@example.com" --password="secret123"
```

### Managing Users via API (Super Admin)

```bash
# List all users
curl http://localhost:8080/api/v1/admin/users \
  -H "Authorization: Bearer <super_admin_token>"

# Create a new admin
curl -X POST http://localhost:8080/api/v1/admin/users \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <super_admin_token>" \
  -d '{"name":"John","email":"john@example.com","password":"secret123","role":"admin"}'

# Delete a user
curl -X DELETE http://localhost:8080/api/v1/admin/users/{user_id} \
  -H "Authorization: Bearer <super_admin_token>"
```

### Via Database Seeder

```bash
docker compose exec app php artisan db:seed
# Creates super admin: admin@chatpilot.com / password
```

## Production Deployment

### Prerequisites

- Docker & Docker Compose on the production server
- A domain or subdomain (e.g., `chatpilot.sezginyurdakul.com`)
- Cloudflare Tunnel (or any reverse proxy) pointing to the server

### Setup

```bash
# Clone and enter the project
git clone <repo-url> ChatPilot && cd ChatPilot

# Create production .env from example
cp .env.example .env

# Edit .env — set these at minimum:
#   COMPOSE_PROJECT_NAME=chatpilot_prod
#   PHP_DOCKERFILE=docker/php/Dockerfile.prod
#   NGINX_CONF=prod.conf
#   NGINX_PORT=8090
#   POSTGRES_PORT=5433
#   POSTGRES_VOLUME_NAME=chatpilot_postgres_data
#   DB_PASSWORD=<strong-password>
#   APP_URL=https://your-domain.com
#   REVERB_APP_KEY=<random-string>
#   REVERB_APP_SECRET=<random-string>
nano .env

# Deploy (builds, starts containers, runs migrations)
./deploy.sh

# Verify
curl http://localhost:8090/api/health
# → {"status":"ok","version":"1.0.0"}
```

### Production Services

| Service | Port | Description |
|---------|------|-------------|
| Nginx | `8090` | HTTP gateway (Cloudflare Tunnel target) |
| PHP-FPM | — | Laravel application |
| PostgreSQL | `5433` | Database (persistent external volume) |
| Redis | `6379` | Queue, cache, sessions |
| Reverb | `9090` | WebSocket server |
| Queue | — | Async job processing |

### Cloudflare Tunnel

1. Go to Cloudflare Dashboard → Zero Trust → Tunnels
2. Add a public hostname to your existing tunnel:
   - Subdomain: `chatpilot`, Domain: `sezginyurdakul.com`
   - Service: `http://localhost:8090`
3. Save — `https://chatpilot.sezginyurdakul.com` is now live

### Useful Commands

```bash
# View logs
docker compose -f docker-compose.yml -f docker-compose.prod.yml logs -f

# Restart all services
docker compose -f docker-compose.yml -f docker-compose.prod.yml restart

# Run migrations
docker compose -f docker-compose.yml -f docker-compose.prod.yml exec app php artisan migrate --force

# Open tinker
docker compose -f docker-compose.yml -f docker-compose.prod.yml exec app php artisan tinker

# Rebuild (after code changes)
docker compose -f docker-compose.yml -f docker-compose.prod.yml build --no-cache
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

### Widget Integration

After deploying, embed the widget on any website:

```html
<script src="https://chatpilot.sezginyurdakul.com/chatpilot-widget.js"></script>
<script>
  ChatPilotWidget.init({
    siteKey: 'sk_your_site_api_key',
    apiUrl: 'https://chatpilot.sezginyurdakul.com'
  });
</script>
```

Get the site key from the admin panel at `/admin#sites`.

## Testing

```bash
# Check code style
docker compose exec app vendor/bin/pint --test

# Run all tests safely (isolated test container + SQLite in-memory)
docker compose -f docker-compose.yml -f docker-compose.test.yml run --rm test

# Run a specific test file
docker compose -f docker-compose.yml -f docker-compose.test.yml run --rm test php artisan test --filter=LoginTest

# Run only unit or feature tests
docker compose -f docker-compose.yml -f docker-compose.test.yml run --rm test php artisan test --testsuite=Unit
docker compose -f docker-compose.yml -f docker-compose.test.yml run --rm test php artisan test --testsuite=Feature
```

Do not run tests with `docker compose exec app php artisan test` on live/dev data. Use the dedicated `test` service above.

CI runs both style checks and the full PHPUnit suite on every push and pull request via GitHub Actions (`.github/workflows/ci.yml`).

Tests use SQLite in-memory database with `RefreshDatabase` trait. The suite includes:
- **Feature tests (72):** Auth (login, logout, user), Widget API, Admin Conversations, Admin Sites, Admin Stats, Admin Presence, Admin User Management, Health
- **Unit tests (27):** AiService, SettingsValidator, MessageObserver, TranslateMessage, EnsureSuperAdmin

## Postman Collection

Import the included files into Postman for interactive API testing:

- `ChatPilot.postman_collection.json` — All 24 endpoints with auto-tests
- `ChatPilot.postman_environment.json` — Local environment with auto-populated variables

**Quick start:** Run **Login** → **List Sites** → all variables auto-populate. Then test any endpoint.

## License

MIT
