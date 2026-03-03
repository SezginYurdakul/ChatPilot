<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    |
    | Fallback AI provider and key used when a site doesn't have its own
    | AI configuration. Can be overridden per site in the admin panel.
    |
    */
    'default_ai_provider' => env('CHATPILOT_DEFAULT_AI_PROVIDER', 'none'),
    'default_ai_key' => env('CHATPILOT_DEFAULT_AI_KEY'),

    /*
    |--------------------------------------------------------------------------
    | CORS Allowed Origins
    |--------------------------------------------------------------------------
    |
    | Origins allowed to make requests to the widget API.
    | Use "*" during development, restrict to specific domains in production.
    |
    */
    'allowed_origins' => env('CHATPILOT_ALLOWED_ORIGINS', '*'),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Default rate limits for visitor messages.
    | These can be overridden per site via the settings JSON column.
    |
    */
    'rate_limit' => [
        'cooldown_seconds' => 3,
        'max_messages_per_hour' => 50,
    ],

    /*
    |--------------------------------------------------------------------------
    | Settings Schema
    |--------------------------------------------------------------------------
    |
    | Defines all available site settings with their types, defaults, and
    | validation rules. Used by the admin panel to dynamically render
    | settings forms. Adding a new entry here automatically exposes it
    | in the API and frontend — no code changes needed.
    |
    | Bump the version whenever the schema changes so the frontend
    | knows to refresh its cached form structure.
    |
    */
    'settings_schema_version' => '1.0.0',
    'settings_schema' => [
        'rate_limit' => [
            'label' => 'Rate Limiting',
            'fields' => [
                'cooldown_seconds' => [
                    'label' => 'Cooldown between messages (seconds)',
                    'type' => 'number',
                    'default' => 3,
                    'min' => 1,
                    'max' => 60,
                ],
                'max_messages_per_hour' => [
                    'label' => 'Max messages per hour',
                    'type' => 'number',
                    'default' => 50,
                    'min' => 1,
                    'max' => 1000,
                ],
            ],
        ],
        'widget' => [
            'label' => 'Widget Appearance',
            'fields' => [
                'theme' => [
                    'label' => 'Color theme',
                    'type' => 'select',
                    'default' => 'light',
                    'options' => ['light', 'dark'],
                ],
                'position' => [
                    'label' => 'Widget position',
                    'type' => 'select',
                    'default' => 'bottom-right',
                    'options' => ['bottom-right', 'bottom-left'],
                ],
                'greeting' => [
                    'label' => 'Welcome message',
                    'type' => 'text',
                    'default' => 'Hi! How can we help you?',
                ],
            ],
        ],
        'ai' => [
            'label' => 'AI Behavior',
            'fields' => [
                'respond_when_offline' => [
                    'label' => 'AI responds when admin is offline',
                    'type' => 'boolean',
                    'default' => true,
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Response Settings
    |--------------------------------------------------------------------------
    |
    | Controls how AI responses are generated.
    | max_history_messages: how many past messages are sent as context to the AI.
    | timeout: max seconds to wait for an AI provider response.
    |
    */
    'ai' => [
        'max_history_messages' => 10,
        'timeout' => 30,
        'max_requests_per_minute' => 10,

        /*
        |----------------------------------------------------------------------
        | Provider Registry
        |----------------------------------------------------------------------
        |
        | Maps provider names to their class implementations.
        | To add a new provider: create the class and add it here.
        | No need to modify AiService.
        |
        */
        'providers' => [
            'gemini' => \App\Services\Ai\GeminiProvider::class,
            'openai' => \App\Services\Ai\OpenAiProvider::class,
            // 'claude' => \App\Services\Ai\ClaudeProvider::class,
        ],
    ],

];
