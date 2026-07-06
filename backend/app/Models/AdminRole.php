<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdminRole extends Model
{
    protected $fillable = ['name', 'label'];

    public function accesses(): BelongsToMany {
        return $this->belongsToMany(AdminAccess::class, 'admin_role_access', 'role_id', 'access_id')
            ->distinct();
    }

    public function admins(): HasMany {
        return $this->hasMany(Admin::class, 'role_id');
    }
}
