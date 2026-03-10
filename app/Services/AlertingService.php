<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AlertingService
{
    public function send(string $title, array $context = []): void
    {
        $webhookUrl = config('chatpilot.observability.alert_webhook_url');
        if (! $webhookUrl) {
            return;
        }

        try {
            Http::timeout(5)->post($webhookUrl, [
                'title' => $title,
                'context' => $context,
                'app' => config('app.name'),
                'env' => config('app.env'),
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to send alert webhook', [
                'title' => $title,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
