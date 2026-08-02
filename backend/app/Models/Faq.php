<?php

namespace App\Models;

use App\Models\Concerns\FlushesCacheOnWrite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Faq extends Model
{
    use FlushesCacheOnWrite, SoftDeletes;

    protected static string $cacheFlushTrigger = 'faq';

    protected $fillable = [
        'title',
        'content',
        'type',
        'status',
        'sort_order',
    ];
}
