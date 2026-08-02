<?php

namespace App\Models;

use App\Models\Concerns\FlushesCacheOnWrite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductStock extends Model
{
    use FlushesCacheOnWrite, SoftDeletes;

    protected static string $cacheFlushTrigger = 'product';

    protected $fillable = [
        'product_id',
        'quantity',
        'price',
        'before_price',
        'real_price',
        'sort_order',
        'status',
    ];

    protected function casts(): array {
        return [
            'price' => 'decimal:2',
            'before_price' => 'decimal:2',
            'real_price' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo {
        return $this->belongsTo(Product::class);
    }

    public function orderItems(): HasMany {
        return $this->hasMany(OrderItem::class);
    }

    public function availableQuantity(): int {
        $pending = $this->pending_quantity ?? $this->orderItems()->where('status', 0)->sum('quantity');
        $fulfilled = $this->fulfilled_quantity ?? $this->orderItems()->whereIn('status', [1, 2, 3])->sum('fact_quantity');

        return max(0, $this->quantity - $pending - $fulfilled);
    }
}
