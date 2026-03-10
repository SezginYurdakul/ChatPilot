# Production DB Password Rotation (PostgreSQL)

This runbook rotates the `chatpilot` database user password safely in production.

Use it when:

- You changed `DB_PASSWORD` in `.env`
- App containers show `SQLSTATE[08006] [7] ... password authentication failed`

## Preconditions

- Working directory: production project folder (for example `ChatPilotProd`)
- Stack is running with `docker-compose.yml` + `docker-compose.prod.yml`
- You can execute commands as Docker user on the host

## 1. Read password from `.env`

```bash
cd "/path/to/ChatPilotProd"
DB_PASS="$(grep '^DB_PASSWORD=' .env | sed 's/^DB_PASSWORD=//')"
```

Optional sanity check (prints length only):

```bash
echo "DB_PASSWORD length: ${#DB_PASS}"
```

## 2. Rotate password in PostgreSQL

Run `ALTER USER` inside the postgres container and set the same password as `.env`.

```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml exec -T postgres \
psql -U postgres -d postgres -v ON_ERROR_STOP=1 -v dbpass="$DB_PASS" \
-c "ALTER USER chatpilot WITH PASSWORD :'dbpass';"
```

If `postgres` superuser name is different, replace `-U postgres` with your superuser.

## 3. Clear config cache and recreate app services

```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml exec -T app php artisan config:clear
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --force-recreate app queue reverb
```

## 4. Verify connectivity

```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml exec -T app php artisan tinker --execute='dump(DB::connection()->getPdo() !== null);'
docker compose -f docker-compose.yml -f docker-compose.prod.yml exec -T app php artisan tinker --execute='dump(["users" => App\Models\User::count(), "sites" => App\Models\Site::count()]);'
curl -sS -i http://localhost:8090/api/health
```

Expected:

- Tinker DB check prints `true`
- Counts query returns values (no SQLSTATE auth error)
- Health endpoint returns `200`

## 5. Post-rotation check

Run a real command that touches DB auth path:

```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml exec app php artisan chatpilot:create-admin
```

You can abort after prompt if only validating connectivity.

## Rollback

If there is an outage after rotation:

1. Set previous known-good value back in `.env` (`DB_PASSWORD=...`)
2. Run the same `ALTER USER` command with previous password
3. Recreate `app`, `queue`, and `reverb` again

## Notes

- Changing only `.env` does not change credentials inside an existing PostgreSQL volume.
- `POSTGRES_PASSWORD` is applied only on initial database bootstrap.
- Always treat `.env` as secret material; do not commit it.
