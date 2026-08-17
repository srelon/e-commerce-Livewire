<?php

namespace App\Livewire\Components\Admins;

use App\Livewire\Traits\ConfirmsAction;
use App\Livewire\Traits\HasAccessControl;
use App\Livewire\Traits\RendersResourceTable;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('livewire.components.layouts.admin.app')]
class Index extends Component
{
    use ConfirmsAction, HasAccessControl, RendersResourceTable;

    protected string $accessKey = 'admins';

    public function render() {
        return $this->renderResourceTable(
            pageTitle: 'Admins',
            tableComponent: 'components.admins.table',
            createRoute: 'admin.admins.create',
            createLabel: 'New admin',
        );
    }
}
