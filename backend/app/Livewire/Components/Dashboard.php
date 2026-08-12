<?php

namespace App\Livewire\Components;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin.app')]
class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}
