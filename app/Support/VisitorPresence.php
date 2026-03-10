<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class VisitorPresence
{
    private const TTL_SECONDS = 30;

    private static function key(string $conversationId): string
    {
        return "chatpilot:visitor_online:{$conversationId}";
    }

    public static function heartbeat(string $conversationId): void
    {
        Cache::put(self::key($conversationId), true, now()->addSeconds(self::TTL_SECONDS));
    }

    public static function markOffline(string $conversationId): void
    {
        Cache::forget(self::key($conversationId));
    }

    public static function isOnline(string $conversationId): bool
    {
        return (bool) Cache::get(self::key($conversationId), false);
    }
}
