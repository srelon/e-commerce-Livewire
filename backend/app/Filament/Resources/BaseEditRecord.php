<?php

namespace App\Filament\Resources;

use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

abstract class BaseEditRecord extends EditRecord
{
    protected function beforeSave(): void {
        if (! static::getResource()::canEdit($this->record)) {
            Notification::make()
                ->title('Access denied')
                ->danger()
                ->send();

            $this->halt();
        }
    }

    protected function getFormActions(): array {
        if (! static::getResource()::canEdit($this->record)) {
            return [];
        }

        return parent::getFormActions();
    }
}
