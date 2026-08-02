<?php

namespace App\Models;

use App\Models\Concerns\FlushesCacheOnWrite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductImage extends Model
{
    use FlushesCacheOnWrite, SoftDeletes;

    protected static string $cacheFlushTrigger = 'product';

    protected $fillable = [
        'product_id',
        'image',
        'sort_order',
    ];

    protected function casts(): array {
        return [
            'image' => 'array',
        ];
    }

    public function product(): BelongsTo {
        return $this->belongsTo(Product::class);
    }
}
