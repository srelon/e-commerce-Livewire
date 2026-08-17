<?php

namespace App\Livewire\Components\Catalog\Products;

use App\Livewire\Traits\HasAccessControl;
use App\Livewire\Traits\HasPowerGridBehavior;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class Table extends PowerGridComponent
{
    use HasAccessControl, HasPowerGridBehavior;

    protected string $accessKey = 'products';

    protected string $modelClass = Product::class;

    protected string $itemNoun = 'Product';

    public string $deleteEvent = 'deleteProduct';

    public string $tableName = 'products-table';

    public string $sortField = 'id';

    public string $sortDirection = 'desc';

    public function datasource(): Builder {
        return Product::query()->with(['category', 'author', 'primaryImage']);
    }

    public function fields(): PowerGridFields {
        return PowerGrid::fields()
            ->add('id')
            ->add('image_cell', $this->imageCellField(fn (Product $model) => $model->primaryImage?->image))
            ->add('title')
            ->add('category_name', fn (Product $model) => $model->category?->name ?? '—')
            ->add('author_name', fn (Product $model) => $model->author?->name ?? '—')
            ->add('bestseller_icon', $this->booleanIconField(fn (Product $model) => (bool) $model->bestseller))
            ->add('status_label', fn (Product $model) => match ($model->status) {
                0 => 'Created',
                1 => 'Active',
                2 => 'Archived',
                3 => 'Reserved',
                default => 'Unknown',
            });
    }

    public function columns(): array {
        return [
            $this->idColumn(),

            $this->photoColumn('Image', 'image_cell'),

            Column::make('Title', 'title')
                ->searchable()
                ->sortable(),

            Column::make('Category', 'category_name'),

            Column::make('Author', 'author_name'),

            Column::make('Bestseller', 'bestseller_icon', 'bestseller')
                ->sortable()
                ->template(),

            Column::make('Status', 'status_label', 'status')
                ->sortable(),

            Column::action('Actions'),
        ];
    }

    public function actions(Product $row): array {
        $actions = [
            $this->editIconButton('admin.products.edit', ['product' => $row->id]),
        ];

        if ($this->hasAccess('edit')) {
            $actions[] = $this->deleteIconButton($this->deleteEvent, ['id' => $row->id], "Delete product \"{$row->title}\"?");
        }

        return $actions;
    }
}
