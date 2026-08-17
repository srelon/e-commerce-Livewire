<?php

namespace App\Livewire\Components\Content\News;

use App\Livewire\Traits\HandlesEmbeddedManager;
use App\Livewire\Traits\HasAccessControl;
use App\Models\NewsAuthor;
use Livewire\Component;
use Livewire\WithFileUploads;

class AuthorManager extends Component
{
    use HandlesEmbeddedManager, HasAccessControl, WithFileUploads;

    protected string $accessKey = 'news';

    protected string $modelClass = NewsAuthor::class;

    protected string $countRelation = 'posts';

    protected string $updatedEvent = 'news-authors-updated';

    protected string $itemNoun = 'Author';

    protected array $formFieldNames = ['editing_id', 'name', 'status', 'avatar'];

    public string $managerEventKey = 'news-author';

    public array $listColumns = [
        ['key' => 'avatar_url', 'label' => '', 'type' => 'avatar'],
        ['key' => 'name', 'label' => 'Name', 'type' => 'text'],
        ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
        ['key' => 'posts_count', 'label' => 'Posts', 'type' => 'text'],
    ];

    public $avatar = null;

    protected function validationRules(): array {
        return [
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:0,1'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ];
    }

    protected function extraListFields($model): array {
        return ['avatar_url' => $this->previewUrl($model->avatar)];
    }

    protected function extraSchemaFieldsBefore(): array {
        $author = $this->editing_id ? NewsAuthor::find($this->editing_id) : null;

        return [[
            'name' => 'avatar',
            'label' => 'Avatar',
            'type' => 'file',
            'preview' => $this->previewUrl($author?->avatar),
            'preview_class' => 'h-20 w-20 rounded-full object-cover',
        ]];
    }

    protected function beforeSave($model, bool $isCreating): void {
        if ($this->avatar) {
            $model->avatar = $this->avatar->store('news_authors', 'public');
            $this->avatar = null;
        }
    }

    public function render() {
        return view('livewire.admin.content.news.partials.author-manager');
    }
}
