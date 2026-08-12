<?php

namespace App\Livewire\Admin\Users;

use App\Livewire\Traits\ConfirmsAction;
use App\Livewire\Traits\HasAccessControl;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin.app')]
class Index extends Component
{
    use ConfirmsAction, HasAccessControl;

    protected string $accessKey = 'users';

    public array $chart_data = [];

    public function mount(): void
    {
        $this->guardView();

        $rows = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $this->chart_data = [
            'labels' => $rows->pluck('date')->toArray(),
            'values' => $rows->pluck('count')->toArray(),
        ];
    }

    public function render()
    {
        return view('livewire.admin.users.index');
    }
}
