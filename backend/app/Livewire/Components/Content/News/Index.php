<?php

namespace App\Livewire\Components\Content\News;

use App\Livewire\Traits\ConfirmsAction;
use App\Livewire\Traits\HasAccessControl;
use App\Livewire\Traits\RendersResourceTable;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('livewire.components.layouts.admin.app')]
class Index extends Component
{
    use ConfirmsAction, HasAccessControl, RendersResourceTable;

    protected string $accessKey = 'news';

    public function render() {
        return $this->renderResourceTable(
            pageTitle: 'News',
            tableComponent: 'components.content.news.table',
            createRoute: 'admin.news.create',
            createLabel: 'New post',
        );
    }
}
