<?php

namespace App\Http\Middleware;

use App\Models\Site;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RateLimitChat
{
    /**
     * Rate limit chat messages per visitor using configured cache store.
     *
     * Limits are resolved in this order:
     *   1. Site-specific settings (sites.settings JSON → rate_limit)
     *   2. Global defaults from config/chatpilot.php
     *
     * Redis keys are site-isolated to prevent cross-site interference:
     *   site:{siteId}:visitor:{token}:cooldown
     *   site:{siteId}:visitor:{token}:hourly
     */
    public function handle(Request $request, Closure $next): Response
    {
        $visitorToken = $request->header('X-Visitor-Token');

        if (! $visitorToken) {
            return $next($request);
        }

        /** @var Site|null $site */
        $site = $request->attributes->get('site');
        [$cooldownSeconds, $maxPerHour] = $this->resolveLimits($site);

        $siteId = $site?->id ?? 'unknown';
        $cooldownKey = "site:{$siteId}:visitor:{$visitorToken}:cooldown";
        $hourlyKey = "site:{$siteId}:visitor:{$visitorToken}:hourly";

        // Rule 1: Cooldown — max 1 message per N seconds
        if (Cache::has($cooldownKey)) {
            return response()->json([
                'error' => 'rate_limit_exceeded',
                'retry_after' => $cooldownSeconds,
                'message' => 'Please wait before sending another message.',
            ], 429);
        }

        // Rule 2: Hourly cap — max N messages per hour
        $hourlyCount = (int) Cache::get($hourlyKey, 0);

        if ($hourlyCount >= $maxPerHour) {
            return response()->json([
                'error' => 'rate_limit_exceeded',
                'retry_after' => 3600,
                'message' => 'Hourly message limit reached.',
            ], 429);
        }

        $response = $next($request);

        // Update counters only on successful message creation
        if ($response->getStatusCode() === 201) {
            Cache::put($cooldownKey, 1, now()->addSeconds($cooldownSeconds));

            if (! Cache::add($hourlyKey, 1, now()->addHour())) {
                Cache::increment($hourlyKey);
            }
        }

        return $response;
    }

    /**
     * Resolve rate limit values from site settings, falling back to config defaults.
     *
     * @return array{0: int, 1: int} [cooldownSeconds, maxMessagesPerHour]
     */
    private function resolveLimits(?Site $site): array
    {
        $siteSettings = $site?->settings['rate_limit'] ?? [];

        $cooldown = $siteSettings['cooldown_seconds']
            ?? config('chatpilot.rate_limit.cooldown_seconds', 3);

        $maxPerHour = $siteSettings['max_messages_per_hour']
            ?? config('chatpilot.rate_limit.max_messages_per_hour', 50);

        return [(int) $cooldown, (int) $maxPerHour];
    }
}
