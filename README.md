# ChatPilot

AI-powered live chat backend for websites. Drop a widget on any site and let visitors chat with your team or an AI assistant in real time.

Built with Laravel 12, PostgreSQL, Redis, and Laravel Reverb for WebSocket support.

## Features

- **Multi-site support** — Manage multiple websites from a single installation
- **AI auto-reply** — Pluggable AI providers (Gemini, OpenAI) respond when admins are offline
- **Real-time messaging** — WebSocket-powered via Laravel Reverb (typing indicators, instant delivery)
- **Admin panel API** — Full conversation management, site settings, and analytics
- **Schema-driven settings** — Add new site settings without code changes
- **Rate limiting** — Per-site configurable message throttling
- **Usage tracking** — Token counts, response times, and error logs for every AI call

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
| Containerization | Docker Compose |

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
| `GET` | `/v1/auth/user` | Get current user |
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

### Admin Analytics

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/v1/admin/stats?period=7d` | Dashboard statistics (`7d`, `30d`, `90d`) |

**Stats response includes:** total conversations, total messages, AI message count, average AI response time, and conversations-by-day chart data.

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
  → Checks: AI provider configured? respond_when_offline enabled?
  → Dispatches ProcessAiResponse job to Redis queue
  → Queue worker picks up the job
    → Broadcasts "AI is typing..." indicator
    → Builds conversation history (last 10 messages)
    → Calls AI provider API with message + history + system prompt
    → Saves AI response as message (sender_type: "ai")
    → Broadcasts message via WebSocket
    → Logs token usage and response time to ai_logs table
  → On failure: sends fallback message + logs error
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

ChatPilot uses Laravel Reverb for WebSocket broadcasting. All events are sent on the `conversation.{id}` channel.

| Event | Payload | Trigger |
|-------|---------|---------|
| `MessageSent` | `{message: {...}}` | Any new message (visitor, admin, or AI) |
| `TypingStarted` | `{sender_type: "ai"\|"admin"\|"visitor"}` | Someone started typing |
| `MessageRead` | `{read_at: "ISO8601"}` | Admin marks messages as read |

### Connecting from the Widget

```javascript
Echo.channel(`conversation.${conversationId}`)
  .listen('MessageSent', (e) => {
    appendMessage(e.message);
  })
  .listen('TypingStarted', (e) => {
    showTypingIndicator(e.sender_type);
  });
```

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
├── Events/                          # WebSocket broadcast events
│   ├── MessageSent.php
│   ├── MessageRead.php
│   └── TypingStarted.php
├── Http/
│   ├── Controllers/Api/V1/
│   │   ├── Admin/                   # Admin panel endpoints
│   │   │   ├── AuthController.php
│   │   │   ├── ConversationController.php
│   │   │   ├── SiteController.php
│   │   │   └── StatsController.php
│   │   └── Widget/                  # Chat widget endpoints
│   │       ├── ConfigController.php
│   │       ├── ConversationController.php
│   │       └── MessageController.php
│   └── Middleware/
│       ├── RateLimitChat.php
│       ├── ValidateSiteKey.php
│       └── ValidateVisitorToken.php
├── Jobs/
│   └── ProcessAiResponse.php        # Async AI response generation
├── Models/
│   ├── AiLog.php                    # AI usage tracking
│   ├── Conversation.php
│   ├── Message.php
│   ├── Site.php
│   └── User.php
├── Observers/
│   └── MessageObserver.php          # Triggers AI on new visitor messages
└── Services/
    ├── AiService.php                # Provider factory & history builder
    ├── SettingsValidator.php         # Dynamic validation from schema
    └── Ai/
        ├── AiProviderInterface.php
        ├── GeminiProvider.php
        └── OpenAiProvider.php

config/
└── chatpilot.php                    # Providers, rate limits, settings schema

docker/
├── nginx/default.conf
└── php/Dockerfile
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
```

## Postman Collection

Import the included files into Postman for interactive API testing:

- `ChatPilot.postman_collection.json` — All 19 endpoints with auto-tests
- `ChatPilot.postman_environment.json` — Local environment with auto-populated variables

**Quick start:** Run **Login** → **List Sites** → all variables auto-populate. Then test any endpoint.

## License

MIT
