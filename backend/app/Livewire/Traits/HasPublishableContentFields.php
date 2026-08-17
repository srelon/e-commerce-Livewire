<?php

namespace App\Livewire\Traits;

use App\Models\SeoMeta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

trait HasPublishableContentFields
{
    public string $title = '';

    public string $slug = '';

    public $category_id = null;

    public $author_id = null;

    public string $published_at = '';

    public string $seo_title = '';

    public string $seo_description = '';

    public string $seo_keywords = '';

    protected function fillTitleSlug(string $title, string $slug): void {
        $this->title = $title;
        $this->slug = $slug;
    }

    protected function slugValidationRule(string $table, ?int $ignoreId): array {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique($table, 'slug')->ignore($ignoreId)],
        ];
    }

    protected function fillPublishedAt(?Carbon $date): void {
        $this->published_at = $date?->format('Y-m-d\TH:i') ?? '';
    }

    protected function publishedAtValidationRule(): array {
        return ['published_at' => ['nullable', 'date']];
    }

    protected function parsePublishedAt(): ?Carbon {
        return filled($this->published_at) ? Carbon::parse($this->published_at) : null;
    }

    protected function fillSeoFields(?SeoMeta $seo): void {
        $this->seo_title = (string) $seo?->seo_title;
        $this->seo_description = (string) $seo?->seo_description;
        $this->seo_keywords = (string) $seo?->seo_keywords;
    }

    protected function seoValidationRules(): array {
        return [
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:255'],
            'seo_keywords' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function saveSeoFields(Model $model): void {
        $model->seo()->updateOrCreate([], [
            'seo_title' => filled($this->seo_title) ? $this->seo_title : null,
            'seo_description' => filled($this->seo_description) ? $this->seo_description : null,
            'seo_keywords' => filled($this->seo_keywords) ? $this->seo_keywords : null,
        ]);
    }
}
