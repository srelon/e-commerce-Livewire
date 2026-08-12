<?php

namespace App\Livewire\Components\Admins;

use App\Livewire\Traits\HasAccessControl;
use App\Livewire\Traits\HasPowerGridBehavior;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class Table extends PowerGridComponent
{
    use HasAccessControl, HasPowerGridBehavior;

    protected string $accessKey = 'admins';

    public string $tableName = 'admins-table';

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
        return Admin::query()->with('role');
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('avatar_cell', fn (Admin $model) => view('livewire.admin.partials.avatar-cell', [
                'avatar' => $model->avatar,
                'name' => $model->name,
            ])->render())
            ->add('name')
            ->add('email')
            ->add('role_badge', fn (Admin $model) => view('livewire.admin.partials.role-badge', [
                'role' => $model->role,
            ])->render())
            ->add('registered_at', fn (Admin $model) => $model->created_at->format('d.m.Y H:i'));
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

            Column::make('Role', 'role_badge')
                ->template(),

            Column::make('Created', 'registered_at', 'created_at')
                ->sortable(),

            Column::action('Actions'),
        ];
    }

    public function actions(Admin $row): array
    {
        $actions = [
            $this->editIconButton('admin.admins.edit', ['admin' => $row->id]),
        ];

        if ($this->hasAccess('edit') && $row->id !== auth('admins')->id()) {
            $actions[] = $this->deleteIconButton('deleteAdmin', ['adminId' => $row->id], "Delete admin \"{$row->name}\"?");
        }

        return $actions;
    }

    #[On('deleteAdmin')]
    public function deleteAdmin(int $adminId): void
    {
        if (! $this->guardSave()) {
            return;
        }

        if ($adminId === auth('admins')->id()) {
            return;
        }

        Admin::whereKey($adminId)->delete();

        $this->dispatch('notify', type: 'success', message: 'Admin deleted.');
    }
}
