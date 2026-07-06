<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'public_id',
        'user_id',
        'contact_id',
        'delivery_id',
        'delivery_branch_id',
        'payment_id',
        'status',
        'paid_amount',
        'txid',
        'tracking_number',
    ];

    protected function casts(): array {
        return [
            'paid_amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void {
        static::creating(function (Order $order) {
            $order->public_id ??= (string) Str::ulid();
            $order->txid ??= (string) Str::uuid();
        });
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function contact(): BelongsTo {
        return $this->belongsTo(OrderContact::class, 'contact_id');
    }

    public function delivery(): BelongsTo {
        return $this->belongsTo(DeliveryService::class, 'delivery_id');
    }

    public function branch(): BelongsTo {
        return $this->belongsTo(DeliveryBranch::class, 'delivery_branch_id');
    }

    public function payment(): BelongsTo {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function items(): HasMany {
        return $this->hasMany(OrderItem::class);
    }
}
