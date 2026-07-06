<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryBranch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'delivery_id',
        'city',
        'branch',
        'status',
        'hash',
    ];

    public function delivery(): BelongsTo {
        return $this->belongsTo(DeliveryService::class, 'delivery_id');
    }

    public function orders(): HasMany {
        return $this->hasMany(Order::class, 'delivery_branch_id');
    }
}
