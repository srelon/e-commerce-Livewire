<?php

namespace App\Services;

use App\Http\Resources\BestAuthorResource;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductsAuthor;
use App\Models\ProductsCategory;
use App\Models\ProductStock;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProductService
{
    public function getFilteredList(array $filters, int $perPage = 9): LengthAwarePaginator {
        $query = $this->baseQuery();
        $this->applyFilters($query, $filters);
        $this->applySort($query, $filters['sort_by'] ?? 'default');

        return $query->paginate($perPage)->through(fn (Product $product) => $this->formatProduct($product));
    }

    public function getBySlug(string $slug): ?Product {
        return Product::query()
            ->where('slug', $slug)
            ->whereIn('status', [1, 2])
            ->with(['author', 'category', 'activeStock' => $this->withStockReservations(...), 'images', 'seo'])
            ->first();
    }

    public function formatProductDetail(Product $product): array {
        return (new ProductResource($product, detailed: true))->resolve();
    }

    public function getRelatedProducts(Product $product, int $limit = 4): Collection {
        return $this->baseQuery()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit($limit)
            ->get()
            ->map(fn (Product $related) => $this->formatProduct($related));
    }

    public function resolveItems(array $items): array {
        $conflicts = [];
        $resolved = [];

        foreach ($items as $item) {
            $product = Product::where('slug', $item['slug'])->firstOrFail();
            $stock = $product->activeStock()->lockForUpdate()->first();
            $available = $stock?->availableQuantity() ?? 0;

            if ($item['quantity'] > $available) {
                $conflicts[] = [
                    'slug' => $item['slug'],
                    'available' => $available,
                ];

                continue;
            }

            $resolved[] = [
                'product' => $product,
                'stock' => $stock,
                'quantity' => $item['quantity'],
            ];
        }

        return [$conflicts, $resolved];
    }

    public function getFilterGroups(array $filters = []): array {
        return [
            $this->priceFilterGroup(),
            $this->categoryFilterGroup($filters),
            $this->authorFilterGroup($filters),
            [
                'title' => 'Rating',
                'type' => 'rating',
                'query_key' => 'rating',
            ],
            $this->statusFilterGroup($filters),
        ];
    }

    protected function applyFilters(Builder $query, array $filters, array $except = []): void {
        if (! in_array('category', $except, true) && ! empty($filters['category'])) {
            $query->whereHas('category', fn (Builder $q) => $q->whereIn('name', $filters['category']));
        }

        if (! in_array('author', $except, true) && ! empty($filters['author'])) {
            $query->whereHas('author', fn (Builder $q) => $q->whereIn('name', $filters['author']));
        }

        if (! in_array('price', $except, true) && (isset($filters['price_min']) || isset($filters['price_max']))) {
            $query->whereHas('activeStock', function (Builder $q) use ($filters) {
                if (isset($filters['price_min'])) {
                    $q->where('price', '>=', $filters['price_min']);
                }
                if (isset($filters['price_max'])) {
                    $q->where('price', '<=', $filters['price_max']);
                }
            });
        }

        if (! in_array('rating', $except, true) && ! empty($filters['rating'])) {
            $query->where('rating_avg', '>=', $filters['rating']);
        }

        if (! in_array('status', $except, true) && ! empty($filters['status'])) {
            $statuses = $filters['status'];
            $query->where(function (Builder $q) use ($statuses) {
                if (in_array('In Stock', $statuses, true)) {
                    $q->orWhereHas('activeStock', fn (Builder $sq) => $sq->where('quantity', '>', 0));
                }
                if (in_array('On Sale', $statuses, true)) {
                    $q->orWhereHas('activeStock', fn (Builder $sq) => $sq->whereNotNull('before_price'));
                }
                if (in_array('Bestseller', $statuses, true)) {
                    $q->orWhere('bestseller', 1);
                }
            });
        }
    }

    protected function applySort(Builder $query, string $sortBy): void {
        match ($sortBy) {
            'popularity' => $query->orderByDesc('bestseller')->orderByDesc('rating_avg'),
            'rating' => $query->orderByDesc('rating_avg'),
            'price_asc' => $query->orderBy($this->priceSubquery()),
            'price_desc' => $query->orderByDesc($this->priceSubquery()),
            'newest' => $query->orderByDesc('published_at'),
            'oldest' => $query->orderBy('published_at'),
            default => $query->orderBy('id'),
        };
    }

    protected function priceSubquery() {
        return ProductStock::select('price')
            ->whereColumn('product_id', 'products.id')
            ->where('status', 1)
            ->latest('sort_order')
            ->limit(1);
    }

    protected function filteredBaseQuery(array $filters, array $except): Builder {
        $query = Product::query()->where('products.status', 1);
        $this->applyFilters($query, $filters, $except);

        return $query;
    }

    protected function priceFilterGroup(): array {
        $bounds = ProductStock::where('status', 1)->selectRaw('MIN(price) as min, MAX(price) as max')->first();

        return [
            'title' => 'Filter by Price',
            'type' => 'price',
            'query_key' => 'price',
            'min' => (float) ($bounds->min ?? 0),
            'max' => (float) ($bounds->max ?? 100),
        ];
    }

    protected function categoryFilterGroup(array $filters): array {
        return $this->checkboxFilterGroup(
            filters: $filters,
            model: ProductsCategory::class,
            title: 'Categories',
            queryKey: 'category',
            orderBy: 'sort_order',
            scopedByStatus: true,
        );
    }

    protected function authorFilterGroup(array $filters): array {
        return $this->checkboxFilterGroup(
            filters: $filters,
            model: ProductsAuthor::class,
            title: 'Authors',
            queryKey: 'author',
            orderBy: 'name',
            scopedByStatus: false,
        );
    }

    protected function checkboxFilterGroup(
        array $filters,
        string $model,
        string $title,
        string $queryKey,
        string $orderBy,
        bool $scopedByStatus,
    ): array {
        $selected = $filters[$queryKey] ?? [];

        $query = $model::query();

        if ($scopedByStatus) {
            $query->where('status', 1);
        }

        $items = $query
            ->withCount(['products' => function (Builder $q) use ($filters, $queryKey) {
                $q->where('products.status', 1);
                $this->applyFilters($q, $filters, [$queryKey]);
            }])
            ->orderBy($orderBy)
            ->get()
            ->map(fn ($item) => [
                'name' => $item->name,
                'count' => $item->products_count,
            ])
            ->all();

        return $this->checkboxGroup($title, $queryKey, $items, $selected);
    }

    protected function statusFilterGroup(array $filters): array {
        $selected = $filters['status'] ?? [];

        $items = [
            [
                'name' => 'In Stock',
                'count' => $this->filteredBaseQuery($filters, ['status'])
                    ->whereHas('activeStock', fn (Builder $q) => $q->where('quantity', '>', 0))
                    ->count(),
            ],
            [
                'name' => 'On Sale',
                'count' => $this->filteredBaseQuery($filters, ['status'])
                    ->whereHas('activeStock', fn (Builder $q) => $q->whereNotNull('before_price'))
                    ->count(),
            ],
            [
                'name' => 'Bestseller',
                'count' => $this->filteredBaseQuery($filters, ['status'])
                    ->where('bestseller', 1)
                    ->count(),
            ],
        ];

        return $this->checkboxGroup('Status', 'status', $items, $selected);
    }

    protected function checkboxGroup(string $title, string $queryKey, array $items, array $selected): array {
        return [
            'title' => $title,
            'type' => 'checkbox',
            'query_key' => $queryKey,
            'items' => array_values(array_filter(
                $items,
                fn (array $item) => $item['count'] > 0 || in_array($item['name'], $selected, true),
            )),
        ];
    }

    public function getCategories(): Collection {
        return ProductsCategory::query()
            ->where('status', 1)
            ->withCount('products')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (ProductsCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'icon' => $category->icon['original'] ?? null,
                'image' => $category->image['original'] ?? null,
                'count' => $category->products_count,
            ]);
    }

    public function getBestsellers(int $limit = 8): Collection {
        return $this->topProducts(['bestseller', 'rating_avg'], $limit);
    }

    public function getBestRated(int $limit = 9): Collection {
        return $this->topProducts(['rating_avg'], $limit, includeStock: false);
    }

    protected function topProducts(array $orderByDesc, int $limit, bool $includeStock = true): Collection {
        $query = $this->baseQuery();

        foreach ($orderByDesc as $column) {
            $query->orderByDesc($column);
        }

        return $query->limit($limit)->get()->map(fn (Product $product) => $this->formatProduct($product, $includeStock));
    }

    public function getBestAuthor(): ?array {
        $ranked = Product::query()
            ->where('status', 1)
            ->selectRaw('author_id, SUM(bestseller) as bestseller_sum, AVG(rating_avg) as avg_rating')
            ->groupBy('author_id')
            ->orderByDesc('bestseller_sum')
            ->orderByDesc('avg_rating')
            ->first();

        $author = $ranked ? ProductsAuthor::find($ranked->author_id) : null;

        if (! $author) {
            return null;
        }

        return (new BestAuthorResource($author))->resolve();
    }

    protected function baseQuery(): Builder {
        return Product::query()
            ->where('status', 1)
            ->with(['author', 'category', 'activeStock' => $this->withStockReservations(...), 'primaryImage']);
    }

    protected function withStockReservations($query): void {
        $query
            ->withSum(['orderItems as pending_quantity' => fn (Builder $q) => $q->where('status', 0)], 'quantity')
            ->withSum(['orderItems as fulfilled_quantity' => fn (Builder $q) => $q->whereIn('status', [1, 2, 3])], 'fact_quantity');
    }

    protected function formatProduct(Product $product, bool $includeStock = true): array {
        return (new ProductResource($product, includeStock: $includeStock))->resolve();
    }
}
