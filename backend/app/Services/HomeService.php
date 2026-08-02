<?php

namespace App\Services;

class HomeService
{
    public function __construct(
        protected ProductService $productService,
        protected BlogService $blogService,
        protected PageService $pageService,
    ) {}

    public function getHome(): array {
        return CacheService::remember(
            'home',
            'home.data',
            fn () => [
                'bestsellers' => $this->productService->getBestsellers()->toArray(),
                'best_author' => $this->productService->getBestAuthor(),
                'best_rated' => $this->productService->getBestRated()->toArray(),
                'blog' => $this->blogService->getLatestPosts()->toArray(),
                'page' => $this->pageService->getPage('home'),
            ],
        );
    }
}
