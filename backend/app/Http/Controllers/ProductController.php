<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductFilterRequest;
use App\Services\ProductService;

class ProductController extends Controller
{
    public function __construct(protected ProductService $productService)
    {
    }

    public function index(ProductFilterRequest $request)
    {
        $filters = $request->filters();
        $paginated = $this->productService->getFilteredList($filters);

        return $this->respondWithJson([
            'products' => $paginated->items(),
            'filter_groups' => $this->productService->getFilterGroups($filters),
            'meta' => $this->paginationMeta($paginated),
        ]);
    }
}
