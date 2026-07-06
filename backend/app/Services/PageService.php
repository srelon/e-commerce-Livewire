<?php

namespace App\Services;

use App\Http\Resources\PageResource;
use App\Models\Page;

class PageService
{
    public function getPage(string $slug): ?array {
        $page = Page::query()->where('slug', $slug)->where('status', 1)->first();

        return $page ? (new PageResource($page))->resolve() : null;
    }
}
