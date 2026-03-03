<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class RateLimitChat
{
    /**
     * Rate limit chat messages per visitor using Redis.
     * Rule 1: Max 1 message per 3 seconds (cooldown).
     * Rule 2: Max 50 messages per hour (hourly cap).
     * Counters are only incremented on successful 201 responses.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $visitorToken = $request->header('X-Visitor-Token');

        if (! $visitorToken) {
            return $next($request);
        }

        // Rule 1: Cooldown — max 1 message per 3 seconds
        $cooldownKey = "visitor:{$visitorToken}:cooldown";
        if (Redis::exists($cooldownKey)) {
            $ttl = Redis::ttl($cooldownKey);

            return response()->json([
                'error' => 'rate_limit_exceeded',
                'retry_after' => $ttl,
                'message' => 'Please wait before sending another message.',
            ], 429);
        }

        // Rule 2: Hourly cap — max 50 messages per hour
        $hourlyKey = "visitor:{$visitorToken}:hourly";
        $hourlyCount = (int) Redis::get($hourlyKey);

        if ($hourlyCount >= 50) {
            return response()->json([
                'error' => 'rate_limit_exceeded',
                'retry_after' => Redis::ttl($hourlyKey),
                'message' => 'Hourly message limit reached.',
            ], 429);
        }

        $response = $next($request);

        // Update counters only on successful message creation
        if ($response->getStatusCode() === 201) {
            Redis::setex($cooldownKey, 3, 1);

            if (Redis::exists($hourlyKey)) {
                Redis::incr($hourlyKey);
            } else {
                Redis::setex($hourlyKey, 3600, 1);
            }
        }

        return $response;
    }
}
