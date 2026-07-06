<?php

namespace App\Models;

use App\Models\Concerns\FlushesCacheOnWrite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class NewsPost extends Model
{
    use FlushesCacheOnWrite, SoftDeletes;

    protected static string $cacheFlushMethod = 'flushOnNewsWrite';

    protected $fillable = [
        'title',
        'slug',
        'category_id',
        'excerpt',
        'content',
        'image',
        'author_id',
        'published_at',
        'status',
    ];

    protected function casts(): array {
        return [
            'image' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo {
        return $this->belongsTo(NewsCategory::class, 'category_id');
    }

    public function author(): BelongsTo {
        return $this->belongsTo(Admin::class, 'author_id');
    }

    public function seo(): MorphOne {
        return $this->morphOne(SeoMeta::class, 'seo', 'type', 'record_id');
    }

    public function reviews(): MorphMany {
        return $this->morphMany(Review::class, 'reviewable', 'type', 'record_id');
    }
}
