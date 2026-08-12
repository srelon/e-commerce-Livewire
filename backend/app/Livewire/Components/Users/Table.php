<?php

namespace App\Livewire\Components\Users;

use App\Livewire\Traits\HasAccessControl;
use App\Livewire\Traits\HasPowerGridBehavior;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class Table extends PowerGridComponent
{
    use HasAccessControl, HasPowerGridBehavior;

    protected string $accessKey = 'users';

    public string $tableName = 'users-table';

    public string $sortField = 'id';

    public string $sortDirection = 'desc';

    public function setUp(): array
    {
        return [
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return User::query();
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('avatar_cell', fn (User $model) => view('livewire.admin.partials.avatar-cell', [
                'avatar' => $model->avatar,
                'name' => $model->name,
            ])->render())
            ->add('name')
            ->add('email')
            ->add('email_verified', fn (User $model) => view('livewire.admin.partials.boolean-icon', [
                'value' => ! is_null($model->email_verified_at),
            ])->render())
            ->add('registered_at', fn (User $model) => $model->created_at->format('d.m.Y H:i'));
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')
                ->sortable(),

            Column::make('Avatar', 'avatar_cell')
                ->template(),

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

    public function filters(): array
    {
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

    public function actions(User $row): array
    {
        $actions = [
            $this->editIconButton('admin.users.edit', ['user' => $row->id]),
        ];

        if ($this->hasAccess('edit')) {
            $actions[] = $this->deleteIconButton('deleteUser', ['userId' => $row->id], "Delete user \"{$row->name}\"?");
        }

        return $actions;
    }

    #[On('deleteUser')]
    public function deleteUser(int $userId): void
    {
        if (! $this->guardSave()) {
            return;
        }

        User::whereKey($userId)->delete();

        $this->dispatch('notify', type: 'success', message: 'User deleted.');
    }
}
