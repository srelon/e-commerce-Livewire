<?php

namespace App\Livewire\Components\Admins;

use App\Livewire\Traits\ConfirmsAction;
use App\Livewire\Traits\HasAccessControl;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin.app')]
class Index extends Component
{
    use ConfirmsAction, HasAccessControl;

    protected string $accessKey = 'admins';

    public function mount(): void
    {
        $this->guardView();
    }

    public function render()
    {
        return view('livewire.admin.admins.index');
    }
}
