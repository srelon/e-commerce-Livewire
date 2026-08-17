<?php

namespace App\Models;

use App\Models\Concerns\FlushesCacheOnWrite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductsAuthor extends Model
{
    use FlushesCacheOnWrite, SoftDeletes;

    protected static string $cacheFlushTrigger = 'product';

    protected $fillable = [
        'name',
        'avatar_color',
        'photo',
        'content',
    ];

    protected function casts(): array {
        return [
            'photo' => 'array',
        ];
    }

    public function products(): HasMany {
        return $this->hasMany(Product::class, 'author_id');
    }
}
