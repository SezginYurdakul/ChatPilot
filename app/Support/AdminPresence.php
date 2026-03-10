<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class AdminPresence
{
    private const TTL_SECONDS = 45;

    private static function key(string $siteId): string
    {
        return "chatpilot:admin_online:{$siteId}";
    }

    public static function heartbeat(string $siteId): void
    {
        Cache::put(self::key($siteId), true, now()->addSeconds(self::TTL_SECONDS));
    }

    public static function markOffline(string $siteId): void
    {
        Cache::forget(self::key($siteId));
    }

    public static function isOnline(string $siteId): bool
    {
        return (bool) Cache::get(self::key($siteId), false);
    }
}
