<?php

namespace App\Services;

use App\Models\Perk;
use Illuminate\Support\Facades\Cache;

class AboutService
{
    public function __construct(protected TeamService $teamService, protected PageService $pageService)
    {
    }

    public function getAbout(): array
    {
        return Cache::tags([CacheService::TAG_ABOUT])->remember(
            'about.data',
            CacheService::TTL_ABOUT,
            fn () => [
                'team' => $this->teamService->getTeamMembers()->toArray(),
                'perks' => $this->getPerks()->toArray(),
                'page' => $this->pageService->getPage('about'),
            ],
        );
    }

    protected function getPerks()
    {
        return Perk::query()
            ->where('status', 1)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Perk $perk) => [
                'title' => $perk->title,
                'desc' => $perk->description,
                'icon' => $perk->icon,
            ]);
    }
}
