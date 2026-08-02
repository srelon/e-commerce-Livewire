<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    public static function remember(string $area, string $key, Closure $callback): mixed {
        $config = config("cacheable.areas.{$area}");

        return Cache::tags([$config['tag']])->remember($key, $config['ttl'], $callback);
    }

    public static function flush(string $trigger): void {
        $areas = config("cacheable.flush_triggers.{$trigger}", []);
        $tags = array_map(fn (string $area) => config("cacheable.areas.{$area}.tag"), $areas);

        Cache::tags($tags)->flush();
    }
}
