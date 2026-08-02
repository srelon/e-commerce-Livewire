<?php

namespace App\Models;

use App\Models\Concerns\FlushesCacheOnWrite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeamMember extends Model
{
    use FlushesCacheOnWrite, SoftDeletes;

    protected static string $cacheFlushTrigger = 'team';

    protected $fillable = [
        'name',
        'role',
        'initials',
        'color',
        'bio',
        'status',
        'sort_order',
    ];
}
