<?php

namespace App\Services;

use App\Http\Resources\PerkResource;
use App\Models\Perk;

class AboutService
{
    public function __construct(protected TeamService $teamService, protected PageService $pageService) {}

    public function getAbout(): array {
        return CacheService::remember(
            'about',
            'about.data',
            fn () => [
                'team' => $this->teamService->getTeamMembers()->toArray(),
                'perks' => $this->getPerks()->toArray(),
                'page' => $this->pageService->getPage('about'),
            ],
        );
    }

    protected function getPerks() {
        return Perk::query()
            ->where('status', 1)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Perk $perk) => (new PerkResource($perk))->resolve());
    }
}
