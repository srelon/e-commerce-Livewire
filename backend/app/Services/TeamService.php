<?php

namespace App\Services;

use App\Http\Resources\TeamMemberResource;
use App\Models\TeamMember;
use Illuminate\Support\Collection;

class TeamService
{
    public function getTeamMembers(): Collection {
        return TeamMember::query()
            ->where('status', 1)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (TeamMember $member) => (new TeamMemberResource($member))->resolve());
    }
}
