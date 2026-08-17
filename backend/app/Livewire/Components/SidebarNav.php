<?php

namespace App\Livewire\Components;

use App\Livewire\Navigation\Navigation;
use Livewire\Attributes\On;
use Livewire\Component;

class SidebarNav extends Component
{
    #[On('admin-access-updated')]
    public function refresh(): void {
        //
    }

    public function render() {
        return view('livewire.components.layouts.admin.sidebar-nav', [
            'items' => Navigation::visible(auth('admins')->user()),
        ]);
    }
}
