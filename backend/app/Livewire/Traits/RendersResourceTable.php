<?php

namespace App\Livewire\Traits;

trait RendersResourceTable
{
    public function mount(): void {
        $this->guardView();
    }

    protected function renderResourceTable(
        string $pageTitle,
        string $tableComponent,
        ?string $createRoute = null,
        ?string $createLabel = null,
        ?string $extra = null,
        array $extraData = [],
    ) {
        return view('livewire.admin.partials.resource-table', [
            'page_title' => $pageTitle,
            'table_component' => $tableComponent,
            'create_route' => $createRoute,
            'create_label' => $createLabel,
            'extra' => $extra,
            'extra_data' => $extraData,
        ]);
    }
}
