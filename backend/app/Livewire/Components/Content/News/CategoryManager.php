<?php

namespace App\Livewire\Components\Content\News;

use App\Livewire\Traits\HandlesEmbeddedManager;
use App\Livewire\Traits\HasAccessControl;
use App\Models\NewsCategory;
use Illuminate\Support\Str;
use Livewire\Component;

class CategoryManager extends Component
{
    use HandlesEmbeddedManager, HasAccessControl;

    protected string $accessKey = 'news';

    protected string $modelClass = NewsCategory::class;

    protected string $countRelation = 'newsPosts';

    protected string $updatedEvent = 'news-categories-updated';

    protected string $itemNoun = 'Category';

    protected array $formFieldNames = ['editing_id', 'name', 'status'];

    public string $managerEventKey = 'news-category';

    public array $listColumns = [
        ['key' => 'name', 'label' => 'Name', 'type' => 'text'],
        ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
        ['key' => 'posts_count', 'label' => 'Posts', 'type' => 'text'],
    ];

    protected function validationRules(): array {
        return [
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:0,1'],
        ];
    }

    protected function beforeSave($model, bool $isCreating): void {
        if ($isCreating) {
            $model->slug = $this->uniqueSlug($this->name);
        }
    }

    private function uniqueSlug(string $name): string {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;

        while (NewsCategory::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function render() {
        return view('livewire.admin.content.news.partials.category-manager');
    }
}
