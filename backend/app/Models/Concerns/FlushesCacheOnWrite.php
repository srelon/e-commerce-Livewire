<?php

namespace App\Models\Concerns;

use App\Services\CacheService;

trait FlushesCacheOnWrite
{
    protected static function bootFlushesCacheOnWrite(): void {
        static::saved(fn () => CacheService::flush(static::$cacheFlushTrigger));
        static::deleted(fn () => CacheService::flush(static::$cacheFlushTrigger));
    }
}
