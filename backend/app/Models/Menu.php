<?php

namespace App\Models;

use App\Models\Concerns\FlushesCacheOnWrite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use FlushesCacheOnWrite;

    protected static string $cacheFlushTrigger = 'menu';

    protected $fillable = [
        'name',
        'route',
        'parent_id',
        'type',
        'params',
        'sort_order',
        'location',
    ];

    protected function casts(): array {
        return [
            'parent_id' => 'integer',
            'params' => 'array',
        ];
    }

    public function children(): HasMany {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }
}
