<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Filament\Resources\Users\Widgets\RegistrationsChartWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array {
        return [
            CreateAction::make()->visible(fn () => static::getResource()::canCreate()),
        ];
    }

    protected function getHeaderWidgets(): array {
        return [
            RegistrationsChartWidget::class,
        ];
    }
}
