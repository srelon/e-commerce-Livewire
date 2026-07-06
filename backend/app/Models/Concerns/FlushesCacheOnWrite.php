<?php

namespace App\Models\Concerns;

use App\Services\CacheService;

trait FlushesCacheOnWrite
{
    protected static function bootFlushesCacheOnWrite(): void {
        static::saved(fn () => CacheService::{static::$cacheFlushMethod}());
        static::deleted(fn () => CacheService::{static::$cacheFlushMethod}());
    }
}
