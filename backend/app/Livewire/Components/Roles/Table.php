<?php

namespace App\Livewire\Components\Roles;

use App\Livewire\Traits\HasAccessControl;
use App\Livewire\Traits\HasPowerGridBehavior;
use App\Models\AdminRole;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class Table extends PowerGridComponent
{
    use HasAccessControl, HasPowerGridBehavior;

    protected string $accessKey = 'roles';

    protected string $modelClass = AdminRole::class;

    protected string $itemNoun = 'Role';

    public string $deleteEvent = 'deleteRole';

    public string $tableName = 'roles-table';

    public string $sortField = 'id';

    public string $sortDirection = 'asc';

    public function datasource(): Builder {
        return AdminRole::query()->withCount(['accesses', 'admins']);
    }

    public function fields(): PowerGridFields {
        return PowerGrid::fields()
            ->add('id')
            ->add('label')
            ->add('name')
            ->add('accesses_count')
            ->add('admins_count');
    }

    public function columns(): array {
        return [
            $this->idColumn(),

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

    public function actions(AdminRole $row): array {
        $actions = [
            $this->editIconButton('admin.roles.edit', ['role' => $row->id]),
        ];

        if ($this->hasAccess('edit')) {
            $actions[] = $this->deleteIconButton($this->deleteEvent, ['id' => $row->id], "Delete role \"{$row->label}\"?");
        }

        return $actions;
    }

    protected function afterDelete(): void {
        $this->dispatch('admin-access-updated');
    }
}
