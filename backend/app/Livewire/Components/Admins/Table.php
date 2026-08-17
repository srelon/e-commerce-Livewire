<?php

namespace App\Livewire\Components\Admins;

use App\Livewire\Traits\HasAccessControl;
use App\Livewire\Traits\HasPowerGridBehavior;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class Table extends PowerGridComponent
{
    use HasAccessControl, HasPowerGridBehavior;

    protected string $accessKey = 'admins';

    protected string $modelClass = Admin::class;

    protected string $itemNoun = 'Admin';

    public string $deleteEvent = 'deleteAdmin';

    public string $tableName = 'admins-table';

    public string $sortField = 'id';

    public string $sortDirection = 'desc';

    public function datasource(): Builder {
        return Admin::query()->with('role');
    }

    public function fields(): PowerGridFields {
        return PowerGrid::fields()
            ->add('id')
            ->add('avatar_cell', $this->avatarCellField())
            ->add('name')
            ->add('email')
            ->add('role_badge', fn (Admin $model) => view('livewire.admin.partials.role-badge', [
                'role' => $model->role,
            ])->render())
            ->add('registered_at', fn (Admin $model) => $model->created_at->format('d.m.Y H:i'));
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

            Column::make('Role', 'role_badge')
                ->template(),

            Column::make('Created', 'registered_at', 'created_at')
                ->sortable(),

            Column::action('Actions'),
        ];
    }

    public function actions(Admin $row): array {
        $actions = [
            $this->editIconButton('admin.admins.edit', ['admin' => $row->id]),
        ];

        if ($this->hasAccess('edit') && $row->id !== auth('admins')->id()) {
            $actions[] = $this->deleteIconButton($this->deleteEvent, ['id' => $row->id], "Delete admin \"{$row->name}\"?");
        }

        return $actions;
    }

    protected function beforeDelete(int $id): bool {
        return $id !== auth('admins')->id();
    }
}
