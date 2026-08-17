<?php

namespace App\Livewire\Traits;

use Closure;
use Illuminate\Support\Facades\Blade;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;

trait HasPowerGridBehavior
{
    private ?string $editIconSvg = null;

    private ?string $deleteIconSvg = null;

    public function setUp(): array {
        return [
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    protected function avatarCellField(): Closure {
        return fn ($model) => view('livewire.admin.partials.avatar-cell', [
            'avatar' => $model->avatar,
            'name' => $model->name,
        ])->render();
    }

    protected function imageCellField(Closure $imageResolver): Closure {
        return fn ($model) => view('livewire.admin.partials.image-cell', [
            'image' => $imageResolver($model),
        ])->render();
    }

    protected function booleanIconField(Closure $valueResolver): Closure {
        return fn ($model) => view('livewire.admin.partials.boolean-icon', [
            'value' => $valueResolver($model),
        ])->render();
    }

    public function sortBy(string $field, string $direction = 'asc'): void {
        parent::sortBy($field, $direction);

        $this->resetPage();
    }

    protected function idColumn(): Column {
        return Column::make('ID', 'id')
            ->sortable()
            ->headerAttribute('w-16')
            ->bodyAttribute('w-16');
    }

    protected function photoColumn(string $title, string $field): Column {
        return Column::make($title, $field)
            ->template()
            ->headerAttribute('w-16')
            ->bodyAttribute('w-16');
    }

    protected function editIconButton(string $route, array $params): Button {
        $this->editIconSvg ??= Blade::render('<x-heroicon-o-pencil-square class="w-5 text-blue-600 dark:text-blue-400" />');

        return Button::add('edit')
            ->slot($this->editIconSvg)
            ->class('btn btn-sm')
            ->tooltip('Edit')
            ->route($route, $params);
    }

    protected function editModalButton(string $event, array $params): Button {
        $this->editIconSvg ??= Blade::render('<x-heroicon-o-pencil-square class="w-5 text-blue-600 dark:text-blue-400" />');

        return Button::add('edit')
            ->slot($this->editIconSvg)
            ->class('btn btn-sm')
            ->tooltip('Edit')
            ->dispatch($event, $params);
    }

    protected function deleteIconButton(string $event, array $params, string $confirmMessage = 'Are you sure you want to delete this record?'): Button {
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

    public function mountHasPowerGridBehavior(): void {
        $this->clampPageToValidRange();
    }

    public function updatedHasPowerGridBehavior(string $property, mixed $value): void {
        if ($property === 'setUp.footer.perPage') {
            $this->gotoPage(1, (string) data_get($this->setUp, 'footer.pageName', 'page'));
            unset($this->records);
        }
    }

    protected function clampPageToValidRange(): void {
        $perPage = (int) data_get($this->setUp, 'footer.perPage');

        if ($perPage <= 0) {
            return;
        }

        $lastPage = max((int) ceil($this->total() / $perPage), 1);

        if ($this->getPage() > $lastPage) {
            $this->gotoPage($lastPage);
            unset($this->records);
        }
    }

    #[On('{deleteEvent}')]
    public function performDelete(int $id): void {
        if (! $this->guardSave()) {
            return;
        }

        if ($this->beforeDelete($id) === false) {
            return;
        }

        $this->modelClass::whereKey($id)->delete();
        $this->clampPageToValidRange();

        $this->afterDelete();

        $this->dispatch('notify', type: 'success', message: "{$this->itemNoun} deleted.");
    }

    protected function beforeDelete(int $id): bool {
        return true;
    }

    protected function afterDelete(): void {
        //
    }
}
