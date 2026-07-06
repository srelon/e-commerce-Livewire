<?php

namespace App\Filament\Resources\Roles;

use App\Filament\Forms\Components\AccessList;
use App\Filament\Resources\BaseResource;
use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Models\AdminRole;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RoleResource extends BaseResource
{
    protected static ?string $model = AdminRole::class;

    protected static string $accessKey = 'roles';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $navigationLabel = 'Roles';

    protected static \UnitEnum|string|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 11;

    public static function form(Schema $schema): Schema {
        return $schema
            ->disabled(! static::hasAccess('edit'))
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('label')
                    ->required()
                    ->maxLength(255),
                AccessList::make('accesses')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('label')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('accesses_count')
                    ->label('Accesses')
                    ->counts('accesses')
                    ->sortable(),
                TextColumn::make('admins_count')
                    ->label('Admins')
                    ->counts('admins')
                    ->sortable(),
            ])
            ->defaultSort('id', 'asc')
            ->recordActions([
                Action::make('open')
                    ->label(fn ($record) => static::canEdit($record) ? 'Edit' : 'View')
                    ->icon(fn ($record) => static::canEdit($record) ? Heroicon::OutlinedPencilSquare : Heroicon::OutlinedEye)
                    ->url(fn ($record) => static::getUrl('edit', ['record' => $record])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array {
        return [];
    }

    public static function getPages(): array {
        return [
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }
}
