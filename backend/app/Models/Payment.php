<?php

namespace App\Models;

use App\Models\Concerns\FlushesCacheOnWrite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use FlushesCacheOnWrite, SoftDeletes;

    protected static string $cacheFlushTrigger = 'payment';

    protected $fillable = [
        'name',
        'key',
        'status',
    ];

    public function orders(): HasMany {
        return $this->hasMany(Order::class);
    }
}
