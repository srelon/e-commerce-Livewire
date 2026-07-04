<?php

namespace App\Models;

use App\Models\Concerns\FlushesCacheOnWrite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactInfo extends Model
{
    use FlushesCacheOnWrite, SoftDeletes;

    protected static string $cacheFlushMethod = 'flushOnContactWrite';

    protected $table = 'contact_info';

    protected $fillable = [
        'name',
        'content',
        'icon',
        'key',
        'status',
        'sort_order',
    ];
}
