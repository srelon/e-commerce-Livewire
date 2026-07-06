<?php

namespace App\Filament\Resources\Admins;

use App\Filament\Resources\Admins\Pages\CreateAdmin;
use App\Filament\Resources\Admins\Pages\EditAdmin;
use App\Filament\Resources\Admins\Pages\ListAdmins;
use App\Filament\Resources\BaseResource;
use App\Models\Admin;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdminResource extends BaseResource
{
    protected static ?string $model = Admin::class;

    protected static string $accessKey = 'admins';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static ?string $navigationLabel = 'Admins';

    protected static \UnitEnum|string|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema {
        return $schema
            ->disabled(! static::hasAccess('edit'))
            ->components([
                FileUpload::make('avatar')
                    ->image()
                    ->imageEditor()
                    ->circleCropper()
                    ->disk('public')
                    ->directory('avatars/admins')
                    ->maxSize(2048)
                    ->columnSpanFull(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => bcrypt($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $operation) => $operation === 'create')
                    ->placeholder(fn (string $operation) => $operation === 'edit' ? 'Leave blank to keep current' : null)
                    ->maxLength(255),
                Select::make('role_id')
                    ->label('Role')
                    ->relationship('role', 'label')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('role.label')
                    ->label('Role')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
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
            'index' => ListAdmins::route('/'),
            'create' => CreateAdmin::route('/create'),
            'edit' => EditAdmin::route('/{record}/edit'),
        ];
    }
}
