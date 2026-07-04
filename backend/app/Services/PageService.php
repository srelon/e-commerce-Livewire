<?php

namespace App\Services;

use App\Models\Page;

class PageService
{
    public function getPage(string $slug): ?array
    {
        $page = Page::query()->where('slug', $slug)->where('status', 1)->first();

        if (!$page) {
            return null;
        }

        return [
            'title' => $page->title,
            'content' => $page->content,
            'excerpt' => $page->excerpt,
            'image' => $page->image['original'] ?? null,
            'seo' => [
                'title' => $page->seo?->seo_title ?? $page->title,
                'description' => $page->seo?->seo_description ?? $page->excerpt,
                'keywords' => $page->seo?->seo_keywords,
            ],
        ];
    }
}
