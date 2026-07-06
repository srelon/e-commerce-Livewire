<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthorFilterRequest;
use App\Services\AuthorService;
use App\Services\PageService;

class AuthorController extends Controller
{
    public function __construct(protected AuthorService $authorService, protected PageService $pageService) {}

    public function index(AuthorFilterRequest $request) {
        $paginated = $this->authorService->getFilteredList($request->filters());

        return $this->respondWithJson([
            'items' => $paginated,
            'page' => $this->pageService->getPage('authors'),
        ]);
    }
}
