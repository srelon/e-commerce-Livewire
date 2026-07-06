<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['public_id', 'name', 'email', 'password', 'avatar'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void {
        static::creating(function (User $user) {
            $user->public_id ??= static::generatePublicId();
        });
    }

    protected static function generatePublicId(): string {
        do {
            $id = (string) random_int(10000000, 99999999);
        } while (static::where('public_id', $id)->exists());

        return $id;
    }

    public function cart(): HasOne {
        return $this->hasOne(Cart::class);
    }

    public function orders(): HasMany {
        return $this->hasMany(Order::class);
    }

    public function favorites(): HasMany {
        return $this->hasMany(ProductsFavorite::class);
    }

    public function reviews(): HasMany {
        return $this->hasMany(Review::class);
    }

    public function userNotifications(): HasMany {
        return $this->hasMany(UserNotification::class);
    }
}
