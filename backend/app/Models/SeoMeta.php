<?php

namespace App\Models;

use App\Models\Concerns\FlushesCacheOnWrite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SeoMeta extends Model
{
    use FlushesCacheOnWrite, SoftDeletes;

    protected static string $cacheFlushTrigger = 'page';

    protected $table = 'seo_meta';

    protected $fillable = [
        'type',
        'record_id',
        'seo_title',
        'seo_description',
        'seo_keywords',
    ];
}
