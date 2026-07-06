<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserNotification extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'from_user_id',
        'type',
        'data',
        'product_id',
        'review_id',
        'parent_id',
        'read_at',
    ];

    protected function casts(): array {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function fromUser(): BelongsTo {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function product(): BelongsTo {
        return $this->belongsTo(Product::class);
    }

    public function review(): BelongsTo {
        return $this->belongsTo(Review::class, 'review_id');
    }
}
