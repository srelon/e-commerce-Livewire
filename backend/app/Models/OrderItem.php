<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_stock_id',
        'price',
        'quantity',
        'fact_quantity',
        'paid_amount',
        'status',
    ];

    protected function casts(): array {
        return [
            'price' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo {
        return $this->belongsTo(Product::class);
    }

    public function stock(): BelongsTo {
        return $this->belongsTo(ProductStock::class, 'product_stock_id');
    }
}
