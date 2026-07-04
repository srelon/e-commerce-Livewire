<?php

namespace App\Services;

use App\Models\TeamMember;
use Illuminate\Support\Collection;

class TeamService
{
    public function getTeamMembers(): Collection
    {
        return TeamMember::query()
            ->where('status', 1)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (TeamMember $member) => [
                'name' => $member->name,
                'role' => $member->role,
                'initials' => $member->initials,
                'color' => $member->color,
                'bio' => $member->bio,
            ]);
    }
}
