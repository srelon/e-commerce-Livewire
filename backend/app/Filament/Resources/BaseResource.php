<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;

abstract class BaseResource extends Resource
{
    protected static string $accessKey = '';

    protected static function hasAccess(string $type): bool {
        return auth('admins')->user()?->hasAccess(static::$accessKey.'.'.$type) ?? false;
    }

    public static function canViewAny(): bool {
        return static::hasAccess('view');
    }

    public static function canView(Model $record): bool {
        return static::hasAccess('view');
    }

    public static function canCreate(): bool {
        return static::hasAccess('edit');
    }

    public static function canEdit(Model $record): bool {
        return static::hasAccess('edit');
    }
}
