# ChatPilot Project Plan (Current State)

Last updated: 2026-03-10

## 1) Product Goal
ChatPilot is a multi-site live chat backend with optional AI auto-reply for offline admins, real-time messaging, translation, and an admin SPA.

## 2) Current Stack
- Backend: Laravel 12, PHP 8.5
- Realtime: Laravel Reverb
- Data: PostgreSQL 16
- Queue/Cache: Redis 7
- Auth: Sanctum
- Admin UI: Alpine.js + Tailwind
- Infra: Docker Compose (base + prod override + test override)

## 3) Runtime Model
The codebase is identical across environments. Differences are controlled by `.env`.

- `docker-compose.yml`: base services for local/dev
- `docker-compose.prod.yml`: production overrides (Dockerfile, ports, restart, persistent public assets)
- `docker-compose.test.yml`: isolated test runner (SQLite in-memory)

### Standard commands
- Dev up: `docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d`
- Prod up: `docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build`
- Tests: `docker compose -f docker-compose.yml -f docker-compose.test.yml run --rm test`
- Deploy script: `./deploy.sh`

## 4) Implemented Functional Scope
- Widget API: config, conversation creation, message list/send, visitor token validation
- Admin Auth: login/logout/current user
- Admin Conversations: list/detail/update/delete/send/read
- Admin Presence: heartbeat/offline
- Admin Sites: CRUD, key regeneration, settings schema
- Admin Users (super admin only): list/create/delete
- Analytics: period-based stats endpoint
- AI pipeline: provider abstraction (`gemini`, `openai`, `none`), queued generation, logging
- Translation pipeline: queued translation with per-message translation storage
- Real-time events: message sent/read/translated, typing, new conversation, admin status
- Access model: `super_admin` + `admin`, user-to-site boundaries via site ownership/assignment
- Error taxonomy: stable API error envelope with machine error key + app error code (`code`)
- Observability: request-level correlation (`X-Request-Id`) and operational counters (API requests, 5xx, queue processed/failed)
- Alerting: queue failure and unhandled 5xx webhook alerts, optional Sentry capture when bound

## 5) Data Model (Current)
- `users`
- `sites`
- `conversations`
- `messages`
- `ai_logs`
- `site_user` (many-to-many user-site assignment)
- Laravel defaults: cache/jobs/tokens/sessions/password reset

## 6) Security & Operational Rules
- `.env` is local-only, never committed
- Production DB uses external named volume (`chatpilot_postgres_data`)
- Tests must run through `docker-compose.test.yml` to avoid touching live/dev DB
- Admin brute-force protection enabled via rate limiter (`admin-login`)
- Production logs are routed to `stderr` via compose override

## 7) Known Operational Decisions
- This repository is source-of-truth from dev branch/worktree
- Production worktree pulls from the same remote, no manual code divergence
- Environment-specific behavior is controlled through `.env` only

## 8) Next Improvements
- Replace conversation polling in admin UI with full WebSocket-driven updates
- Add backup/restore scripts for PostgreSQL and Redis with retention policy
- Add smoke test in deploy script (health + DB connectivity + queue ping)
- Add role/site assignment UI enhancements (bulk assign/remove)
- Add CI job for `docker compose config` validation (base/prod/test)
- Add Sentry Laravel package + release tagging for full exception tracing
- Add alert deduplication/rate limiting for noisy failure scenarios

## 9) Definition of Done (Release)
- Migrations run cleanly on empty DB
- Core test suite passes via isolated test service
- `deploy.sh` completes without manual steps
- `/api/health` responds `200` post-deploy
- Admin login, conversation send/receive, and AI fallback work end-to-end
