<?php

namespace App\Livewire\Traits;

use Illuminate\Support\Facades\Blade;
use PowerComponents\LivewirePowerGrid\Button;

trait HasPowerGridBehavior
{
    private ?string $editIconSvg = null;

    private ?string $deleteIconSvg = null;

    public function sortBy(string $field, string $direction = 'asc'): void
    {
        parent::sortBy($field, $direction);

        $this->resetPage();
    }

    protected function editIconButton(string $route, array $params): Button
    {
        $this->editIconSvg ??= Blade::render('<x-heroicon-o-pencil-square class="w-5 text-blue-600 dark:text-blue-400" />');

        return Button::add('edit')
            ->slot($this->editIconSvg)
            ->class('btn btn-sm')
            ->tooltip('Edit')
            ->route($route, $params);
    }

    protected function deleteIconButton(string $event, array $params, string $confirmMessage = 'Are you sure you want to delete this record?'): Button
    {
        $this->deleteIconSvg ??= Blade::render('<x-heroicon-o-trash class="w-5 text-red-600 dark:text-red-400" />');

        return Button::add('delete')
            ->slot($this->deleteIconSvg)
            ->class('btn btn-sm')
            ->tooltip('Delete')
            ->dispatch('confirm-action', [
                'event' => $event,
                'params' => $params,
                'message' => $confirmMessage,
                'heading' => 'Confirm deletion',
                'label' => 'Delete',
                'variant' => 'danger',
            ]);
    }
}
