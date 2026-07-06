<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Admin extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $with = ['role'];

    protected function casts(): array {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo {
        return $this->belongsTo(AdminRole::class, 'role_id');
    }

    public function newsPosts(): HasMany {
        return $this->hasMany(NewsPost::class, 'author_id');
    }

    public function hasAccess(string $slug): bool {
        if (! $this->role_id) {
            return false;
        }

        [$key, $action] = explode('.', $slug, 2);
        $type = match ($action) {
            'view' => 1,
            'edit' => 2,
            default => 0,
        };

        if ($type === 0) {
            return false;
        }

        return $this->loadAccessCache()->contains($key.':'.$type);
    }

    private function loadAccessCache(): Collection {
        return once(fn () => DB::table('admin_role_access')
            ->join('admin_accesses', 'admin_accesses.id', '=', 'admin_role_access.access_id')
            ->where('admin_role_access.role_id', $this->role_id)
            ->select('admin_accesses.key', 'admin_role_access.type')
            ->get()
            ->map(fn ($row) => $row->key.':'.$row->type)
        );
    }

    public function canAccessPanel(Panel $panel): bool {
        return true;
    }

    public function getFilamentAvatarUrl(): ?string {
        return $this->avatar ? Storage::disk('public')->url($this->avatar) : null;
    }
}
