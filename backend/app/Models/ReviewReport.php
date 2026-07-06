<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReviewReport extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'review_id',
        'user_id',
        'reason',
    ];

    public function review(): BelongsTo {
        return $this->belongsTo(Review::class, 'review_id');
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
