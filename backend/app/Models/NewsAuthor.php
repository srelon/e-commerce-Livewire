<?php

namespace App\Models;

use App\Models\Concerns\HasNumericPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class NewsAuthor extends Model
{
    use HasNumericPublicId, SoftDeletes;

    protected $fillable = [
        'public_id',
        'name',
        'avatar',
        'status',
    ];

    public function posts(): HasMany {
        return $this->hasMany(NewsPost::class, 'author_id');
    }
}
