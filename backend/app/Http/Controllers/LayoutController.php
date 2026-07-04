<?php

namespace App\Http\Controllers;

use App\Services\LayoutService;

class LayoutController extends Controller
{
    public function __construct(protected LayoutService $layoutService)
    {
    }

    public function index()
    {
        return $this->respondWithJson($this->layoutService->getLayout());
    }
}
