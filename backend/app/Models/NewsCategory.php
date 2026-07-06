<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class NewsCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'status',
    ];

    public function newsPosts(): HasMany {
        return $this->hasMany(NewsPost::class, 'category_id');
    }
}
