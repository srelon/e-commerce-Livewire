<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\BaseEditRecord;
use App\Filament\Resources\Roles\RoleResource;
use App\Models\AdminAccess;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;

class EditRole extends BaseEditRecord
{
    protected static string $resource = RoleResource::class;

    protected function authorizeAccess(): void {
        abort_unless(auth('admins')->user()?->hasAccess('roles.view'), 403);
    }

    protected function getHeaderActions(): array {
        return [
            Action::make('create_access')
                ->label('Add Access')
                ->icon('heroicon-o-plus')
                ->color('gray')
                ->modalWidth('sm')
                ->form([
                    TextInput::make('key')
                        ->required()
                        ->maxLength(255)
                        ->unique(AdminAccess::class, 'key')
                        ->helperText('e.g. posts, news, settings'),
                    TextInput::make('title')
                        ->required()
                        ->maxLength(255),
                ])
                ->action(fn (array $data) => AdminAccess::create($data))
                ->visible(fn () => static::getResource()::canEdit($this->record)),
            DeleteAction::make()->visible(fn () => static::getResource()::canEdit($this->record)),
        ];
    }
}
