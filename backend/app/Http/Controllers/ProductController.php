<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductFilterRequest;
use App\Services\PageService;
use App\Services\ProductService;

class ProductController extends Controller
{
    public function __construct(protected ProductService $productService, protected PageService $pageService)
    {
    }

    public function index(ProductFilterRequest $request)
    {
        $filters = $request->filters();
        $paginated = $this->productService->getFilteredList($filters);

        return $this->respondWithJson([
            'items' => $paginated,
            'filter_groups' => $this->productService->getFilterGroups($filters),
            'page' => $this->pageService->getPage('products'),
        ]);
    }
}
