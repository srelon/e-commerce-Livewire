<?php

namespace App\Livewire\Components\Roles;

use App\Livewire\Traits\ConfirmsAction;
use App\Livewire\Traits\HasAccessControl;
use App\Livewire\Traits\RendersResourceTable;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('livewire.components.layouts.admin.app')]
class Index extends Component
{
    use ConfirmsAction, HasAccessControl, RendersResourceTable;

    protected string $accessKey = 'roles';

    public function render() {
        return $this->renderResourceTable(
            pageTitle: 'Roles',
            tableComponent: 'components.roles.table',
            createRoute: 'admin.roles.create',
            createLabel: 'New role',
        );
    }
}
