<?php

namespace App\Services;

use App\Models\ProductsAuthor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class AuthorService
{
    public function getFilteredList(array $filters, int $perPage = 6): LengthAwarePaginator
    {
        $query = ProductsAuthor::query()
            ->withCount('products')
            ->withSum('products as bestseller_sum', 'bestseller');
        $this->applySort($query, $filters['sort_by'] ?? 'newest');

        return $query->paginate($perPage)->through(fn (ProductsAuthor $author) => $this->formatAuthor($author));
    }

    protected function applySort(Builder $query, string $sortBy): void
    {
        match ($sortBy) {
            'books' => $query->orderByDesc('products_count'),
            'bestseller' => $query->orderByDesc('bestseller_sum'),
            'oldest' => $query->orderBy('created_at'),
            default => $query->orderByDesc('created_at'),
        };
    }

    protected function formatAuthor(ProductsAuthor $author): array
    {
        return [
            'slug' => $author->slug,
            'name' => $author->name,
            'initials' => $this->initials($author->name),
            'color' => $author->avatar_color ?? '#999999',
            'bio' => $author->content ?? '',
            'books' => $author->products_count,
            'bestsellers' => (int) $author->bestseller_sum,
        ];
    }

    protected function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name));

        return strtoupper(mb_substr($parts[0] ?? '', 0, 1) . mb_substr($parts[1] ?? '', 0, 1));
    }
}
