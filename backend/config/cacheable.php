<?php

return [

    /*
     * Cacheable "page bundle" read endpoints. Key is the area name passed to
     * CacheService::remember(). Add a new cached endpoint here — CacheService
     * itself never needs to change.
     */
    'areas' => [
        'layout' => [
            'ttl' => 900,
            'tag' => 'layout',
        ],
        'home' => [
            'ttl' => 600,
            'tag' => 'home',
        ],
        'contact' => [
            'ttl' => 900,
            'tag' => 'contact',
        ],
        'about' => [
            'ttl' => 900,
            'tag' => 'about',
        ],
        'delivery' => [
            'ttl' => 3600,
            'tag' => 'delivery',
        ],
        'payment' => [
            'ttl' => 3600,
            'tag' => 'payment',
        ],
    ],

    /*
     * Write triggers. Key is the value a model's $cacheFlushTrigger points at,
     * value is the list of area names above to flush. Add a new write source
     * (or point an existing model at an existing trigger) here — CacheService
     * itself never needs to change.
     */
    'flush_triggers' => [
        'menu' => ['layout'],
        'contact' => ['layout'],
        'faq' => ['contact'],
        'category' => ['layout', 'home'],
        'product' => ['home', 'layout'],
        'news' => ['home'],
        'page' => ['home', 'contact', 'about'],
        'team' => ['about'],
        'perk' => ['about'],
        'delivery' => ['delivery'],
        'payment' => ['payment'],
    ],

];
