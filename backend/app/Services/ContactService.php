<?php

namespace App\Services;

use App\Models\Faq;
use Illuminate\Support\Facades\Cache;

class ContactService
{
    public function __construct(protected PageService $pageService)
    {
    }

    public function getContact(): array
    {
        return Cache::tags([CacheService::TAG_CONTACT])->remember(
            'contact.data',
            CacheService::TTL_CONTACT,
            fn () => [
                'faqs' => $this->getFaqs()->toArray(),
                'page' => $this->pageService->getPage('contact'),
            ],
        );
    }

    protected function getFaqs()
    {
        return Faq::query()
            ->where('type', 'contact')
            ->where('status', 1)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Faq $faq) => [
                'question' => $faq->title,
                'answer' => $faq->content,
            ]);
    }
}
