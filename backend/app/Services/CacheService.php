<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    const TTL_LAYOUT = 900;

    const TTL_HOME = 600;

    const TTL_CONTACT = 900;

    const TTL_ABOUT = 900;

    const TAG_LAYOUT = 'layout';

    const TAG_HOME = 'home';

    const TAG_CONTACT = 'contact';

    const TAG_ABOUT = 'about';

    public static function flushOnMenuWrite(): void {
        Cache::tags([self::TAG_LAYOUT])->flush();
    }

    public static function flushOnContactWrite(): void {
        Cache::tags([self::TAG_LAYOUT])->flush();
    }

    public static function flushOnFaqWrite(): void {
        Cache::tags([self::TAG_CONTACT])->flush();
    }

    public static function flushOnCategoryWrite(): void {
        Cache::tags([self::TAG_LAYOUT, self::TAG_HOME])->flush();
    }

    public static function flushOnProductWrite(): void {
        Cache::tags([self::TAG_HOME, self::TAG_LAYOUT])->flush();
    }

    public static function flushOnNewsWrite(): void {
        Cache::tags([self::TAG_HOME])->flush();
    }

    public static function flushOnPageWrite(): void {
        Cache::tags([self::TAG_HOME, self::TAG_CONTACT, self::TAG_ABOUT])->flush();
    }

    public static function flushOnTeamWrite(): void {
        Cache::tags([self::TAG_ABOUT])->flush();
    }

    public static function flushOnPerkWrite(): void {
        Cache::tags([self::TAG_ABOUT])->flush();
    }
}
