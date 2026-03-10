<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class OperationalMetrics
{
    private const TTL_DAYS = 120;

    public static function increment(string $metric, int $by = 1): void
    {
        $key = self::key($metric, Carbon::now()->toDateString());

        // initialize key so increment works across stores
        if (! Cache::has($key)) {
            Cache::put($key, 0, now()->addDays(self::TTL_DAYS));
        }

        Cache::increment($key, $by);
    }

    public static function sum(string $metric, int $days): int
    {
        $sum = 0;
        for ($i = 0; $i < $days; $i++) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $sum += (int) Cache::get(self::key($metric, $date), 0);
        }

        return $sum;
    }

    private static function key(string $metric, string $date): string
    {
        return "metrics:{$metric}:{$date}";
    }
}
