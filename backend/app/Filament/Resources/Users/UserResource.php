<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UserResource extends BaseResource
{
    protected static ?string $model = User::class;

    protected static string $accessKey = 'users';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = 'Users';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema {
        $can_edit = auth('admins')->user()?->hasAccess('users.edit');

        return $schema
            ->disabled(! $can_edit)
            ->components([
                FileUpload::make('avatar')
                    ->image()
                    ->imageEditor()
                    ->circleCropper()
                    ->disk('public')
                    ->directory('avatars/users')
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
                IconColumn::make('email_verified_at')
                    ->label('Verified')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->email_verified_at !== null),
                TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                TernaryFilter::make('email_verified_at')
                    ->label('Verified')
                    ->nullable(),
            ])
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
