<?php

namespace App\Livewire\Components\Catalog\Authors;

use App\Livewire\Traits\HasAccessControl;
use App\Livewire\Traits\HasPowerGridBehavior;
use App\Models\ProductsAuthor;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class Table extends PowerGridComponent
{
    use HasAccessControl, HasPowerGridBehavior;

    protected string $accessKey = 'authors';

    protected string $modelClass = ProductsAuthor::class;

    protected string $itemNoun = 'Author';

    public string $deleteEvent = 'deleteAuthor';

    public string $tableName = 'authors-table';

    public string $sortField = 'id';

    public string $sortDirection = 'desc';

    public function datasource(): Builder {
        return ProductsAuthor::query()->withCount('products');
    }

    public function fields(): PowerGridFields {
        return PowerGrid::fields()
            ->add('id')
            ->add('photo_cell', $this->imageCellField(fn (ProductsAuthor $model) => $model->photo))
            ->add('name')
            ->add('products_count');
    }

    public function columns(): array {
        return [
            $this->idColumn(),

            $this->photoColumn('Photo', 'photo_cell'),

            Column::make('Name', 'name')
                ->searchable()
                ->sortable(),

            Column::make('Books', 'products_count'),

            Column::action('Actions'),
        ];
    }

    public function actions(ProductsAuthor $row): array {
        $actions = [
            $this->editModalButton('open-author-form', ['authorId' => $row->id]),
        ];

        if ($this->hasAccess('edit')) {
            $actions[] = $this->deleteIconButton($this->deleteEvent, ['id' => $row->id], "Delete author \"{$row->name}\"?");
        }

        return $actions;
    }
}
