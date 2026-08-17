<?php

namespace App\Livewire\Components\Catalog\Products;

use App\Livewire\Traits\ConfirmsAction;
use App\Livewire\Traits\HasAccessControl;
use App\Livewire\Traits\RendersResourceTable;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('livewire.components.layouts.admin.app')]
class Index extends Component
{
    use ConfirmsAction, HasAccessControl, RendersResourceTable;

    protected string $accessKey = 'products';

    public function render() {
        return $this->renderResourceTable(
            pageTitle: 'Products',
            tableComponent: 'components.catalog.products.table',
            createRoute: 'admin.products.create',
            createLabel: 'New product',
        );
    }
}
