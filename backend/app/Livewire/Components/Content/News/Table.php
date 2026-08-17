<?php

namespace App\Livewire\Components\Content\News;

use App\Livewire\Traits\HasAccessControl;
use App\Livewire\Traits\HasPowerGridBehavior;
use App\Models\NewsAuthor;
use App\Models\NewsCategory;
use App\Models\NewsPost;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class Table extends PowerGridComponent
{
    use HasAccessControl, HasPowerGridBehavior;

    protected string $accessKey = 'news';

    protected string $modelClass = NewsPost::class;

    protected string $itemNoun = 'News post';

    public string $deleteEvent = 'deleteNewsPost';

    public string $tableName = 'news-table';

    public string $sortField = 'id';

    public string $sortDirection = 'desc';

    public function datasource(): Builder {
        return NewsPost::query()->with(['category', 'author']);
    }

    public function fields(): PowerGridFields {
        return PowerGrid::fields()
            ->add('id')
            ->add('image_cell', $this->imageCellField(fn (NewsPost $model) => $model->image))
            ->add('title')
            ->add('category_name', fn (NewsPost $model) => $model->category?->name ?? '—')
            ->add('author_name', fn (NewsPost $model) => $model->author?->name ?? '—')
            ->add('status_icon', $this->booleanIconField(fn (NewsPost $model) => (bool) $model->status));
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

            Column::make('Status', 'status_icon', 'status')
                ->sortable()
                ->template(),

            Column::action('Actions'),
        ];
    }

    public function filters(): array {
        return [
            Filter::select('category_name', 'category_id')
                ->dataSource($this->filterOptionsWithEmpty(NewsCategory::orderBy('name')->get(), 'No category'))
                ->optionValue('id')
                ->optionLabel('name')
                ->builder(fn (Builder $builder, string|array|int|null $values) => $this->applyRelationFilter($builder, 'category', $values)),

            Filter::select('author_name', 'author_id')
                ->dataSource($this->filterOptionsWithEmpty(NewsAuthor::orderBy('name')->get(), 'No author'))
                ->optionValue('id')
                ->optionLabel('name')
                ->builder(fn (Builder $builder, string|array|int|null $values) => $this->applyRelationFilter($builder, 'author', $values)),

            Filter::boolean('status_icon')
                ->label('Active', 'Inactive')
                ->builder(function (Builder $builder, string|array|int|null $values) {
                    if (blank($values) || $values === 'all') {
                        return;
                    }

                    $isActive = $values === 'true' || $values === '1';

                    $builder->where('status', $isActive ? 1 : 0);
                }),
        ];
    }

    private function filterOptionsWithEmpty(iterable $records, string $emptyLabel): array {
        $options = [['id' => 'empty', 'name' => $emptyLabel]];

        foreach ($records as $record) {
            $options[] = ['id' => (string) $record->id, 'name' => $record->name];
        }

        return $options;
    }

    private function applyRelationFilter(Builder $builder, string $relation, string|array|int|null $value): void {
        if (blank($value) || $value === 'all') {
            return;
        }

        if ($value === 'empty') {
            $builder->whereDoesntHave($relation);

            return;
        }

        $builder->where("{$relation}_id", $value);
    }

    public function actions(NewsPost $row): array {
        $actions = [
            $this->editIconButton('admin.news.edit', ['post' => $row->id]),
        ];

        if ($this->hasAccess('edit')) {
            $actions[] = $this->deleteIconButton($this->deleteEvent, ['id' => $row->id], "Delete news post \"{$row->title}\"?");
        }

        return $actions;
    }
}
