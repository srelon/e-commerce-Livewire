<?php

namespace App\Models;

use App\Models\Concerns\FlushesCacheOnWrite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use FlushesCacheOnWrite, SoftDeletes;

    protected static string $cacheFlushMethod = 'flushOnProductWrite';

    protected $fillable = [
        'category_id',
        'author_id',
        'title',
        'slug',
        'short_description',
        'description',
        'rating_avg',
        'reviews_count',
        'bestseller',
        'status',
        'published_at',
    ];

    protected function casts(): array {
        return [
            'rating_avg' => 'decimal:1',
            'published_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo {
        return $this->belongsTo(ProductsCategory::class, 'category_id');
    }

    public function author(): BelongsTo {
        return $this->belongsTo(ProductsAuthor::class, 'author_id');
    }

    public function images(): HasMany {
        return $this->hasMany(ProductImage::class);
    }

    public function stocks(): HasMany {
        return $this->hasMany(ProductStock::class);
    }

    public function activeStock(): HasOne {
        return $this->hasOne(ProductStock::class)->where('status', 1)->latest('sort_order');
    }

    public function primaryImage(): HasOne {
        return $this->hasOne(ProductImage::class)->orderBy('sort_order');
    }

    public function seo(): MorphOne {
        return $this->morphOne(SeoMeta::class, 'seo', 'type', 'record_id');
    }

    public function reviews(): MorphMany {
        return $this->morphMany(Review::class, 'reviewable', 'type', 'record_id');
    }

    public function cartItems(): HasMany {
        return $this->hasMany(CartItem::class);
    }

    public function favorites(): HasMany {
        return $this->hasMany(ProductsFavorite::class);
    }

    public function orderItems(): HasMany {
        return $this->hasMany(OrderItem::class);
    }
}
