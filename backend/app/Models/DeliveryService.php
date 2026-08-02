<?php

namespace App\Models;

use App\Models\Concerns\FlushesCacheOnWrite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryService extends Model
{
    use FlushesCacheOnWrite, SoftDeletes;

    protected static string $cacheFlushTrigger = 'delivery';

    protected $fillable = [
        'name',
        'key',
        'status',
        'price',
    ];

    protected function casts(): array {
        return [
            'price' => 'decimal:2',
        ];
    }

    public function branches(): HasMany {
        return $this->hasMany(DeliveryBranch::class, 'delivery_id');
    }

    public function orders(): HasMany {
        return $this->hasMany(Order::class, 'delivery_id');
    }

    public function requiresBranch(): bool {
        return $this->branches()->where('status', 1)->exists();
    }
}
