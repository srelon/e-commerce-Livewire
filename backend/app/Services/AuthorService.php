<?php

namespace App\Services;

use App\Http\Resources\ProductsAuthorResource;
use App\Models\ProductsAuthor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class AuthorService
{
    public function getFilteredList(array $filters, int $perPage = 6): LengthAwarePaginator {
        $query = ProductsAuthor::query()
            ->withCount('products')
            ->withSum('products as bestseller_sum', 'bestseller');
        $this->applySort($query, $filters['sort_by'] ?? 'newest');

        return $query->paginate($perPage)->through(fn (ProductsAuthor $author) => $this->formatAuthor($author));
    }

    protected function applySort(Builder $query, string $sortBy): void {
        match ($sortBy) {
            'books' => $query->orderByDesc('products_count'),
            'bestseller' => $query->orderByDesc('bestseller_sum'),
            'oldest' => $query->orderBy('created_at'),
            default => $query->orderByDesc('created_at'),
        };
    }

    protected function formatAuthor(ProductsAuthor $author): array {
        return (new ProductsAuthorResource($author))->resolve();
    }
}
