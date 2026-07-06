<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\BaseEditRecord;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;

class EditUser extends BaseEditRecord
{
    protected static string $resource = UserResource::class;

    protected function authorizeAccess(): void {
        abort_unless(auth('admins')->user()?->hasAccess('users.view'), 403);
    }

    protected function getHeaderActions(): array {
        return [
            DeleteAction::make()->visible(fn () => static::getResource()::canEdit($this->record)),
        ];
    }
}
