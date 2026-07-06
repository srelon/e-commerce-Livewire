<?php

namespace App\Filament\Resources\Admins\Pages;

use App\Filament\Resources\Admins\AdminResource;
use App\Filament\Resources\BaseEditRecord;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\Auth;

class EditAdmin extends BaseEditRecord
{
    protected static string $resource = AdminResource::class;

    protected function authorizeAccess(): void {
        abort_unless(auth('admins')->user()?->hasAccess('admins.view'), 403);
    }

    protected function getHeaderActions(): array {
        return [
            DeleteAction::make()->visible(fn () => static::getResource()::canEdit($this->record)),
        ];
    }

    protected function afterSave(): void {
        if (Auth::guard('admins')->id() === $this->record->id) {
            $fresh = $this->record->fresh();
            Auth::guard('admins')->setUser($fresh);
            session()->put('password_hash_admins', $fresh->getAuthPassword());
            $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));
        }
    }
}
