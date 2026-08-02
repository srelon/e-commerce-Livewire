<?php

namespace App\Models\Concerns;

trait HasNumericPublicId
{
    protected static function bootHasNumericPublicId(): void {
        static::creating(function ($model) {
            $model->public_id ??= static::generatePublicId();
        });
    }

    protected static function generatePublicId(): string {
        do {
            $id = (string) random_int(10000000, 99999999);
        } while (static::where('public_id', $id)->exists());

        return $id;
    }
}
