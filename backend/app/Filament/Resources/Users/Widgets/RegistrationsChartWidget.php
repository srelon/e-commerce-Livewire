<?php

namespace App\Filament\Resources\Users\Widgets;

use App\Models\User;
use Filament\Widgets\Widget;

class RegistrationsChartWidget extends Widget
{
    protected string $view = 'filament.widgets.registrations-chart-widget';

    protected int|string|array $columnSpan = 'full';

    public array $chart_data = [];

    public function mount(): void {
        $this->loadData();
    }

    private function loadData(): void {
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
}
