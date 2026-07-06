<?php

namespace App\Models;

use App\Models\Concerns\FlushesCacheOnWrite;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\FilamentTree\Concern\ModelTree;

class Menu extends Model
{
    use FlushesCacheOnWrite, ModelTree;

    protected static string $cacheFlushMethod = 'flushOnMenuWrite';

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
}
