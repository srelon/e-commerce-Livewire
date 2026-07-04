<?php

namespace App\Http\Controllers;

use App\Http\Requests\NewsletterSubscribeRequest;
use App\Services\NewsletterService;

class NewsletterController extends Controller
{
    public function __construct(protected NewsletterService $newsletterService)
    {
    }

    public function store(NewsletterSubscribeRequest $request)
    {
        $subscriber = $this->newsletterService->subscribe($request->validated());

        return $this->respondWithJson($subscriber);
    }
}
