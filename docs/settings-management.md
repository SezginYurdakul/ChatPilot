# Settings Management

## Overview

ChatPilot uses a **schema-driven settings system** that allows each site to have its own configuration stored in a single JSONB column (`sites.settings`). The system is designed so that adding a new setting requires changes in only one file (`config/chatpilot.php`), with validation, filtering, and frontend form rendering handled automatically.

---

## Architecture

```
config/chatpilot.php          ← Single source of truth (schema + defaults)
        │
        ├── SettingsValidator  ← Generates validation rules & filters unknown keys
        │       │
        │       └── SiteController (store / update)
        │               │
        │               └── sites.settings (JSONB column in DB)
        │
        └── settings-schema endpoint → Frontend renders dynamic forms
```

### Key Components

| Component | File | Responsibility |
|---|---|---|
| Schema Definition | `config/chatpilot.php` | Declares all available settings with types, defaults, and constraints |
| Settings Validator | `app/Services/SettingsValidator.php` | Generates Laravel validation rules from schema; filters unknown keys |
| Site Controller | `app/Http/Controllers/Api/V1/Admin/SiteController.php` | Validates and persists settings via store/update endpoints |
| Rate Limit Middleware | `app/Http/Middleware/RateLimitChat.php` | Reads settings at runtime with config fallback |
| Schema Endpoint | `GET /api/v1/admin/sites/settings-schema` | Serves schema to frontend for dynamic form generation |

---

## Schema Structure

The schema lives in `config/chatpilot.php` under the `settings_schema` key. Each entry represents a **group** of related settings:

```php
'settings_schema_version' => '1.0.0',
'settings_schema' => [
    'group_name' => [
        'label' => 'Human-readable group title',
        'fields' => [
            'field_name' => [
                'label'   => 'Human-readable field label',
                'type'    => 'number|text|boolean|select',
                'default' => <default value>,
                // Type-specific constraints:
                'min'     => 1,           // number only
                'max'     => 60,          // number only
                'options' => ['a', 'b'],  // select only
            ],
        ],
    ],
],
```

### Supported Field Types

| Type | Validation Rule | Frontend Element | Extra Properties |
|---|---|---|---|
| `number` | `integer\|min:N\|max:N` | `<input type="number">` | `min`, `max` |
| `text` | `string\|max:1000` | `<input type="text">` | — |
| `boolean` | `boolean` | `<input type="checkbox">` | — |
| `select` | `in:opt1,opt2,...` | `<select>` | `options` (array) |

### Current Schema Groups

**Rate Limiting** (`rate_limit`)
- `cooldown_seconds` — Minimum seconds between messages (1–60, default: 3)
- `max_messages_per_hour` — Maximum messages a visitor can send per hour (1–1000, default: 50)

**Widget Appearance** (`widget`)
- `theme` — Color theme: `light` or `dark` (default: light)
- `position` — Widget position: `bottom-right` or `bottom-left` (default: bottom-right)
- `greeting` — Welcome message shown to visitors (default: "Hi! How can we help you?")

**AI Behavior** (`ai`)
- `respond_when_offline` — Whether AI auto-responds when admin is offline (default: true)

---

## How Settings Are Stored

Settings are stored in the `sites.settings` JSONB column. No additional migrations are needed to add new settings — the JSONB column accepts any valid JSON structure.

Example stored value:

```json
{
  "rate_limit": {
    "cooldown_seconds": 5,
    "max_messages_per_hour": 100
  },
  "widget": {
    "theme": "dark",
    "position": "bottom-right",
    "greeting": "Welcome! Ask us anything."
  },
  "ai": {
    "respond_when_offline": true
  }
}
```

---

## API Endpoints

### Get Settings Schema

```
GET /api/v1/admin/sites/settings-schema
Authorization: Bearer {token}
```

Response:

```json
{
  "version": "1.0.0",
  "schema": {
    "rate_limit": {
      "label": "Rate Limiting",
      "fields": {
        "cooldown_seconds": {
          "label": "Cooldown between messages (seconds)",
          "type": "number",
          "default": 3,
          "min": 1,
          "max": 60
        }
      }
    }
  }
}
```

The `version` field changes whenever the schema is modified. Frontend clients should use this to invalidate cached form structures.

### Create Site with Settings

```
POST /api/v1/admin/sites
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "My Store",
  "domain": "mystore.com",
  "settings": {
    "rate_limit": {
      "cooldown_seconds": 5,
      "max_messages_per_hour": 100
    },
    "widget": {
      "theme": "dark"
    }
  }
}
```

### Update Settings (Partial)

```
PATCH /api/v1/admin/sites/{siteId}
Authorization: Bearer {token}
Content-Type: application/json

{
  "settings": {
    "rate_limit": {
      "cooldown_seconds": 10
    }
  }
}
```

**Important:** Updates use deep merge (`array_replace_recursive`). Sending only `rate_limit.cooldown_seconds` will not erase other existing settings like `widget.theme` or `rate_limit.max_messages_per_hour`.

---

## Validation

### Schema-Driven Validation

`SettingsValidator::rules()` reads the schema and automatically generates Laravel validation rules. No manual rule maintenance is needed.

```php
// Input (from schema):
'cooldown_seconds' => ['type' => 'number', 'min' => 1, 'max' => 60]

// Output (generated rule):
'settings.rate_limit.cooldown_seconds' => 'sometimes|integer|min:1|max:60'
```

All rules use `sometimes`, meaning fields are optional — only validated when present in the request.

### Unknown Key Filtering

`SettingsValidator::filterUnknownKeys()` strips any keys that are not defined in the schema before saving to the database.

```php
// Request input:
{
  "rate_limit": { "cooldown_seconds": 5 },
  "malicious_field": "evil_value"
}

// After filtering:
{
  "rate_limit": { "cooldown_seconds": 5 }
}
// "malicious_field" is silently removed
```

This prevents the settings JSON from accumulating garbage data over time.

---

## Runtime Resolution (Fallback Chain)

When the application reads a setting at runtime (e.g., in middleware), it follows this priority:

```
1. site.settings.{group}.{field}     → Site-specific value from DB
2. config('chatpilot.{group}.{field}') → Global default from config file
3. Hard-coded fallback                → Last resort (in code)
```

Example from `RateLimitChat` middleware:

```php
private function resolveLimits(?Site $site): array
{
    $siteSettings = $site?->settings['rate_limit'] ?? [];

    $cooldown = $siteSettings['cooldown_seconds']
        ?? config('chatpilot.rate_limit.cooldown_seconds', 3);

    $maxPerHour = $siteSettings['max_messages_per_hour']
        ?? config('chatpilot.rate_limit.max_messages_per_hour', 50);

    return [(int) $cooldown, (int) $maxPerHour];
}
```

This ensures the system remains stable even when a site has no custom settings configured.

---

## Site Isolation

Redis keys used by the rate limiter are prefixed with the site ID to prevent cross-site interference:

```
site:{siteId}:visitor:{visitorToken}:cooldown
site:{siteId}:visitor:{visitorToken}:hourly
```

This means the same visitor interacting with two different sites has independent rate limit counters for each.

---

## Adding a New Setting

To add a new setting, only modify `config/chatpilot.php`:

### Step 1: Add to Schema

```php
'settings_schema' => [
    // ... existing groups ...

    'notifications' => [
        'label' => 'Notifications',
        'fields' => [
            'sound_enabled' => [
                'label'   => 'Play sound on new message',
                'type'    => 'boolean',
                'default' => true,
            ],
            'email_digest' => [
                'label'   => 'Email digest frequency',
                'type'    => 'select',
                'default' => 'daily',
                'options' => ['off', 'hourly', 'daily', 'weekly'],
            ],
        ],
    ],
],
```

### Step 2: Bump Version

```php
'settings_schema_version' => '1.1.0',  // was '1.0.0'
```

### Step 3: Done

No other files need to change:

- **Validation** — `SettingsValidator::rules()` automatically generates `'settings.notifications.sound_enabled' => 'sometimes|boolean'`
- **Filtering** — `SettingsValidator::filterUnknownKeys()` now allows `notifications.sound_enabled` and `notifications.email_digest`
- **Frontend** — Fetches the updated schema, sees the new "Notifications" group, renders a checkbox and a dropdown
- **Storage** — JSONB column accepts the new keys without migration
- **API** — `PATCH /sites/{id}` accepts `{ "settings": { "notifications": { "sound_enabled": false } } }`

### Step 4: Read in Application Code

Where you need the setting value:

```php
$soundEnabled = $site->settings['notifications']['sound_enabled']
    ?? config('chatpilot.notifications.sound_enabled', true);
```

---

## Frontend Integration Guide

### Rendering the Form

1. Fetch schema from `GET /api/v1/admin/sites/settings-schema`
2. Cache the response keyed by `version`
3. For each group in `schema`, render a section with the group `label`
4. For each field in `fields`, render the appropriate input based on `type`:

```
type: "number"  → <input type="number" min={min} max={max} />
type: "text"    → <input type="text" />
type: "boolean" → <input type="checkbox" />
type: "select"  → <select> with <option> for each item in options
```

5. Pre-fill values from the site's current `settings` object
6. For empty fields, show `default` as placeholder

### Saving Settings

Send only the changed values via `PATCH /api/v1/admin/sites/{id}`. The backend deep-merges with existing settings, so partial updates are safe.

```javascript
// Only sending the changed field
await fetch(`/api/v1/admin/sites/${siteId}`, {
  method: 'PATCH',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    settings: {
      rate_limit: {
        cooldown_seconds: 10,  // only this changed
      },
    },
  }),
});
```
