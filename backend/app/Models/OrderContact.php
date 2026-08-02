<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderContact extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'email',
        'delivery_id',
        'delivery_branch_id',
        'last_ordered_at',
    ];

    protected function casts(): array {
        return [
            'last_ordered_at' => 'datetime',
        ];
    }

    public function orders(): HasMany {
        return $this->hasMany(Order::class, 'contact_id');
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function delivery(): BelongsTo {
        return $this->belongsTo(DeliveryService::class, 'delivery_id');
    }

    public function branch(): BelongsTo {
        return $this->belongsTo(DeliveryBranch::class, 'delivery_branch_id');
    }
}
