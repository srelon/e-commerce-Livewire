<?php

namespace App\Livewire\Components\Roles;

use App\Livewire\Traits\HasAccessControl;
use App\Livewire\Traits\HasPowerGridBehavior;
use App\Models\AdminRole;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class Table extends PowerGridComponent
{
    use HasAccessControl, HasPowerGridBehavior;

    protected string $accessKey = 'roles';

    public string $tableName = 'roles-table';

    public string $sortField = 'id';

    public string $sortDirection = 'asc';

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
        return AdminRole::query()->withCount(['accesses', 'admins']);
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('label')
            ->add('name')
            ->add('accesses_count')
            ->add('admins_count');
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')
                ->sortable(),

            Column::make('Label', 'label')
                ->searchable()
                ->sortable(),

            Column::make('Name', 'name')
                ->searchable()
                ->sortable(),

            Column::make('Accesses', 'accesses_count')
                ->sortable(),

            Column::make('Admins', 'admins_count')
                ->sortable(),

            Column::action('Actions'),
        ];
    }

    public function actions(AdminRole $row): array
    {
        $actions = [
            $this->editIconButton('admin.roles.edit', ['role' => $row->id]),
        ];

        if ($this->hasAccess('edit')) {
            $actions[] = $this->deleteIconButton('deleteRole', ['roleId' => $row->id], "Delete role \"{$row->label}\"?");
        }

        return $actions;
    }

    #[On('deleteRole')]
    public function deleteRole(int $roleId): void
    {
        if (! $this->guardSave()) {
            return;
        }

        AdminRole::whereKey($roleId)->delete();

        $this->dispatch('notify', type: 'success', message: 'Role deleted.');
    }
}
