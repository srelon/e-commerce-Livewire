<?php

namespace App\Models;

use App\Models\Concerns\FlushesCacheOnWrite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use FlushesCacheOnWrite, SoftDeletes;

    protected static string $cacheFlushMethod = 'flushOnPageWrite';

    protected $fillable = [
        'slug',
        'title',
        'content',
        'image',
        'excerpt',
        'status',
        'sort_order',
    ];

    protected function casts(): array {
        return [
            'image' => 'array',
        ];
    }

    public function seo(): MorphOne {
        return $this->morphOne(SeoMeta::class, 'seo', 'type', 'record_id');
    }
}
