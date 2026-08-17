<?php

namespace App\Models\Concerns;

use App\Services\CacheService;

trait FlushesCacheOnWrite
{
    protected static function bootFlushesCacheOnWrite(): void {
        static::saved(fn () => CacheService::flush(static::$cacheFlushTrigger));
        static::deleted(fn () => CacheService::flush(static::$cacheFlushTrigger));
    }

    public static function bulkUpdateAndFlush(iterable $rows, callable $attributesFor): void {
        foreach ($rows as $index => $row) {
            static::whereKey((int) $row['id'])->update($attributesFor($row, $index));
        }

        CacheService::flush(static::$cacheFlushTrigger);
    }
}
