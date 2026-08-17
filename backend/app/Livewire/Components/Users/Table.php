<?php

namespace App\Livewire\Components\Users;

use App\Livewire\Traits\HasAccessControl;
use App\Livewire\Traits\HasPowerGridBehavior;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class Table extends PowerGridComponent
{
    use HasAccessControl, HasPowerGridBehavior;

    protected string $accessKey = 'users';

    protected string $modelClass = User::class;

    protected string $itemNoun = 'User';

    public string $deleteEvent = 'deleteUser';

    public string $tableName = 'users-table';

    public string $sortField = 'id';

    public string $sortDirection = 'desc';

    public function datasource(): Builder {
        return User::query();
    }

    public function fields(): PowerGridFields {
        return PowerGrid::fields()
            ->add('id')
            ->add('avatar_cell', $this->avatarCellField())
            ->add('name')
            ->add('email')
            ->add('email_verified', $this->booleanIconField(fn (User $model) => ! is_null($model->email_verified_at)))
            ->add('registered_at', fn (User $model) => $model->created_at->format('d.m.Y H:i'));
    }

    public function columns(): array {
        return [
            $this->idColumn(),

            $this->photoColumn('Avatar', 'avatar_cell'),

            Column::make('Name', 'name')
                ->searchable()
                ->sortable(),

            Column::make('Email', 'email')
                ->searchable()
                ->sortable(),

            Column::make('Verified', 'email_verified', 'email_verified_at')
                ->sortable()
                ->template(),

            Column::make('Registered', 'registered_at', 'created_at')
                ->sortable(),

            Column::action('Actions'),
        ];
    }

    public function filters(): array {
        return [
            Filter::boolean('email_verified')
                ->label('Verified', 'Not verified')
                ->builder(function (Builder $builder, string|array|int|null $values) {
                    if (blank($values) || $values === 'all') {
                        return;
                    }

                    $isVerified = $values === 'true' || $values === '1';

                    $builder->when(
                        $isVerified,
                        fn (Builder $query) => $query->whereNotNull('email_verified_at'),
                        fn (Builder $query) => $query->whereNull('email_verified_at'),
                    );
                }),
        ];
    }

    public function actions(User $row): array {
        $actions = [
            $this->editIconButton('admin.users.edit', ['user' => $row->id]),
        ];

        if ($this->hasAccess('edit')) {
            $actions[] = $this->deleteIconButton($this->deleteEvent, ['id' => $row->id], "Delete user \"{$row->name}\"?");
        }

        return $actions;
    }
}
