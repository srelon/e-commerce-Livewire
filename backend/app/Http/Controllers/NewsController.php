<?php

namespace App\Http\Controllers;

use App\Http\Requests\NewsFilterRequest;
use App\Services\BlogService;
use App\Services\PageService;

class NewsController extends Controller
{
    public function __construct(protected BlogService $blogService, protected PageService $pageService)
    {
    }

    public function index(NewsFilterRequest $request)
    {
        $filters = $request->filters();
        $paginated = $this->blogService->getFilteredList($filters);

        return $this->respondWithJson([
            'items' => $paginated,
            'categories' => $this->blogService->getCategories(),
            'page' => $this->pageService->getPage('news'),
        ]);
    }
}
