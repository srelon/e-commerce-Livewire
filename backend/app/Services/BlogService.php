<?php

namespace App\Services;

use App\Models\NewsCategory;
use App\Models\NewsPost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class BlogService
{
    public function getLatestPosts(int $limit = 7): Collection
    {
        return NewsPost::query()
            ->where('status', 1)
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get()
            ->map(fn (NewsPost $post) => $this->formatPost($post));
    }

    public function getFilteredList(array $filters, int $perPage = 6): LengthAwarePaginator
    {
        $query = NewsPost::query()->where('status', 1);
        $this->applyFilters($query, $filters);
        $this->applySort($query, $filters['sort_by'] ?? 'newest');

        return $query->paginate($perPage)->through(fn (NewsPost $post) => $this->formatPost($post));
    }

    public function getBySlug(string $slug): ?NewsPost
    {
        return NewsPost::query()
            ->where('slug', $slug)
            ->where('status', 1)
            ->with(['author', 'category', 'seo'])
            ->first();
    }

    public function formatPostDetail(NewsPost $post): array
    {
        return [
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt,
            'content' => $post->content,
            'author' => $post->author?->name,
            'date' => $post->published_at?->toDateString(),
            'image' => $post->image['original'] ?? null,
            'category' => $post->category?->name,
            'seo' => [
                'title' => $post->seo?->seo_title ?? $post->title,
                'description' => $post->seo?->seo_description ?? $post->excerpt,
                'keywords' => $post->seo?->seo_keywords,
            ],
        ];
    }

    public function getCategories(): array
    {
        return NewsCategory::query()
            ->where('status', 1)
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['category'])) {
            $query->whereHas('category', fn (Builder $q) => $q->where('name', $filters['category']));
        }
    }

    protected function applySort(Builder $query, string $sortBy): void
    {
        match ($sortBy) {
            'oldest' => $query->orderBy('published_at'),
            default => $query->orderByDesc('published_at'),
        };
    }

    protected function formatPost(NewsPost $post): array
    {
        return [
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt,
            'author' => $post->author?->name,
            'date' => $post->published_at?->toDateString(),
            'image' => $post->image['original'] ?? null,
            'category' => $post->category?->name,
        ];
    }
}
