<?php

namespace App\Livewire\Components\Catalog\Authors;

use App\Livewire\Traits\ConfirmsAction;
use App\Livewire\Traits\HandlesModalForm;
use App\Livewire\Traits\HasAccessControl;
use App\Models\ProductsAuthor;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('livewire.components.layouts.admin.app')]
class Form extends Component
{
    use ConfirmsAction, HandlesModalForm, HasAccessControl, WithFileUploads;

    protected string $accessKey = 'authors';

    public bool $show_modal = false;

    public ?int $editing_id = null;

    public string $name = '';

    public string $avatar_color = '#d97706';

    public string $content = '';

    public $photo = null;

    #[Computed]
    public function schema(): array {
        $author = $this->editing_id ? ProductsAuthor::find($this->editing_id) : null;

        return [
            [
                'name' => 'photo',
                'label' => 'Photo',
                'type' => 'file',
                'preview' => $this->previewUrl($author?->photo),
                'preview_class' => 'h-20 w-20 rounded-full object-cover',
            ],
            [
                'name' => 'name',
                'label' => 'Name',
                'type' => 'text',
            ],
            [
                'name' => 'avatar_color',
                'label' => 'Avatar color',
                'type' => 'text',
                'input_type' => 'color',
            ],
            [
                'name' => 'content',
                'label' => 'Bio',
                'type' => 'textarea',
                'full_width' => true,
                'rows' => 6,
            ],
        ];
    }

    #[On('open-author-form')]
    public function openEdit(int $authorId): void {
        $author = ProductsAuthor::findOrFail($authorId);

        $this->resetForm();
        $this->editing_id = $author->id;
        $this->name = $author->name;
        $this->avatar_color = $author->avatar_color ?: '#d97706';
        $this->content = (string) $author->content;
        $this->show_modal = true;
    }

    protected function resetForm(): void {
        $this->resetFormFields(['editing_id', 'name', 'avatar_color', 'content', 'photo']);
    }

    public function save(): void {
        if (! $this->guardSave()) {
            return;
        }

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'avatar_color' => ['nullable', 'string', 'max:20'],
            'content' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        $isCreating = ! $this->editing_id;
        $author = $this->editing_id ? ProductsAuthor::findOrFail($this->editing_id) : new ProductsAuthor;
        $author->name = $this->name;
        $author->avatar_color = $this->avatar_color;
        $author->content = $this->content;

        if ($this->photo) {
            $author->photo = ['original' => $this->photo->store('products_authors', 'public')];
        }

        $author->save();

        $this->show_modal = false;

        $this->dispatch('pg:eventRefresh-authors-table');
        $this->dispatch('notify', type: 'success', message: $isCreating ? 'Author created.' : 'Author updated.');
    }

    public function render() {
        return view('livewire.admin.catalog.authors.index');
    }
}
