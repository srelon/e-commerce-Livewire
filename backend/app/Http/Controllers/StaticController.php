<?php

namespace App\Http\Controllers;

use App\Services\AboutService;
use App\Services\ContactService;
use App\Services\HomeService;
use App\Services\PageService;

class StaticController extends Controller
{
    public function __construct(
        protected HomeService $homeService,
        protected ContactService $contactService,
        protected AboutService $aboutService,
        protected PageService $pageService,
    ) {
    }

    public function home()
    {
        return $this->respondWithJson($this->homeService->getHome());
    }

    public function contact()
    {
        return $this->respondWithJson($this->contactService->getContact());
    }

    public function about()
    {
        return $this->respondWithJson($this->aboutService->getAbout());
    }

    public function show(string $slug)
    {
        return $this->respondWithJson($this->pageService->getPage($slug));
    }
}
