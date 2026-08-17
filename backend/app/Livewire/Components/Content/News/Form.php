<?php

namespace App\Livewire\Components\Content\News;

use App\Livewire\Traits\ConfirmsAction;
use App\Livewire\Traits\HandlesFullPageSave;
use App\Livewire\Traits\HandlesRichText;
use App\Livewire\Traits\HasAccessControl;
use App\Livewire\Traits\HasFormHelpers;
use App\Livewire\Traits\HasPublishableContentFields;
use App\Models\NewsAuthor;
use App\Models\NewsCategory;
use App\Models\NewsPost;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('livewire.components.layouts.admin.app')]
class Form extends Component
{
    use ConfirmsAction, HandlesFullPageSave, HandlesRichText, HasAccessControl, HasFormHelpers, HasPublishableContentFields, WithFileUploads;

    protected string $accessKey = 'news';

    protected string $routePrefix = 'news';

    protected string $routeParamKey = 'post';

    protected string $saveMessageName = 'News post';

    public ?NewsPost $post = null;

    public $status = 1;

    public string $excerpt = '';

    public string $content = '';

    public $image = null;

    public $content_image = null;

    public function updatedContentImage(): void {
        $this->handleRichTextImageUpload($this->content_image, 'content', 'news');
        $this->content_image = null;
    }

    public function mount(?NewsPost $post = null): void {
        if ($post) {
            $this->guardView();

            $this->post = $post;
            $this->fillTitleSlug($post->title, $post->slug);
            $this->category_id = $post->category_id;
            $this->author_id = $post->author_id;
            $this->status = $post->status;
            $this->fillPublishedAt($post->published_at);
            $this->excerpt = (string) $post->excerpt;
            $this->content = (string) $post->content;
            $this->fillSeoFields($post->seo);
        } else {
            abort_unless($this->hasAccess('edit'), 403);

            $this->category_id = $this->categories->first()?->id;
        }
    }

    #[Computed]
    public function categories(): Collection {
        return NewsCategory::query()
            ->where(function ($query) {
                $query->where('status', 1);

                if ($this->category_id) {
                    $query->orWhere('id', $this->category_id);
                }
            })
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function authors(): Collection {
        return NewsAuthor::query()
            ->where(function ($query) {
                $query->where('status', 1);

                if ($this->author_id) {
                    $query->orWhere('id', $this->author_id);
                }
            })
            ->orderBy('name')
            ->get();
    }

    #[On('news-categories-updated')]
    public function refreshCategories(): void {
        unset($this->categories);
    }

    #[On('news-authors-updated')]
    public function refreshAuthors(): void {
        unset($this->authors);
    }

    #[Computed]
    public function schema(): array {
        return [
            [
                'name' => 'image',
                'label' => 'Image',
                'type' => 'file',
                'preview' => $this->previewUrl($this->post?->image),
                'preview_class' => 'h-24 w-24 rounded object-cover',
            ],
            [
                'name' => 'title',
                'label' => 'Title',
                'type' => 'text',
            ],
            [
                'name' => 'slug',
                'label' => 'Slug',
                'type' => 'text',
            ],
            [
                'name' => 'category_id',
                'label' => 'Category',
                'type' => 'select',
                'options' => $this->categories->map(fn (NewsCategory $category) => [
                    'value' => $category->id,
                    'label' => $category->name,
                ])->all(),
                'manage_button' => [
                    'label' => 'Manage categories',
                    'dispatch' => 'open-news-category-manager',
                ],
            ],
            [
                'name' => 'author_id',
                'label' => 'Author',
                'type' => 'select',
                'options' => $this->authors->map(fn (NewsAuthor $author) => [
                    'value' => $author->id,
                    'label' => $author->name,
                ])->all(),
                'manage_button' => [
                    'label' => 'Manage authors',
                    'dispatch' => 'open-news-author-manager',
                ],
            ],
            $this->statusField(),
            [
                'name' => 'published_at',
                'label' => 'Published at',
                'type' => 'text',
                'input_type' => 'datetime-local',
            ],
            [
                'name' => 'excerpt',
                'label' => 'Excerpt',
                'type' => 'textarea',
                'full_width' => true,
                'rows' => 2,
            ],
            [
                'name' => 'content',
                'label' => 'Content',
                'type' => 'richtext',
                'full_width' => true,
                'value' => $this->content,
                'emoji' => true,
            ],
            [
                'name' => 'seo_title',
                'label' => 'SEO title',
                'type' => 'text',
                'placeholder' => $this->title ?: null,
            ],
            [
                'name' => 'seo_keywords',
                'label' => 'SEO keywords',
                'type' => 'text',
                'placeholder' => 'comma, separated, keywords',
            ],
            [
                'name' => 'seo_description',
                'label' => 'SEO description',
                'type' => 'textarea',
                'full_width' => true,
                'rows' => 2,
                'placeholder' => $this->excerpt ?: null,
            ],
        ];
    }

    #[On('deleteNewsPost')]
    public function deleteNewsPost(): void {
        if (! $this->post || ! $this->guardSave()) {
            return;
        }

        $this->post->delete();

        session()->flash('notify', ['type' => 'success', 'message' => 'News post deleted.']);
        $this->redirectRoute('admin.news.index', navigate: true);
    }

    private function persist(): ?NewsPost {
        if (! $this->guardSave()) {
            return null;
        }

        $this->validate([
            'category_id' => ['required', 'exists:news_categories,id'],
            'author_id' => ['nullable', 'exists:news_authors,id'],
            'status' => ['required', 'in:0,1'],
            'excerpt' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:4096'],
            ...$this->slugValidationRule('news_posts', $this->post?->id),
            ...$this->publishedAtValidationRule(),
            ...$this->seoValidationRules(),
        ]);

        $post = $this->post ?? new NewsPost;
        $post->category_id = (int) $this->category_id;
        $post->author_id = $this->author_id ? (int) $this->author_id : null;
        $post->title = $this->title;
        $post->slug = $this->slug;
        $post->status = (int) $this->status;
        $post->published_at = $this->parsePublishedAt();
        $post->excerpt = $this->excerpt;
        $post->content = $this->sanitizeContent($this->content);

        if ($this->image) {
            $post->image = ['original' => $this->image->store('news', 'public')];
            $this->image = null;
        }

        $post->save();

        $this->saveSeoFields($post);

        $this->post = $post;

        return $post;
    }

    public function render() {
        return $this->renderResourceForm(
            pageTitle: $this->post ? 'Edit news post' : 'New news post',
            disabled: ! $this->hasAccess('edit'),
            cancelRoute: 'admin.news.index',
            siteUrl: $this->post ? rtrim(config('app.frontend_url'), '/')."/news/{$this->post->slug}" : null,
            deleteAction: $this->post && $this->hasAccess('edit') ? 'deleteNewsPost' : null,
            deleteMessage: $this->post ? "Delete news post \"{$this->post->title}\"?" : null,
            extra: 'livewire.admin.content.news.partials.managers',
        );
    }
}
