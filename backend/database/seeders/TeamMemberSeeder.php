<?php

namespace Database\Seeders;

use App\Models\TeamMember;
use Illuminate\Database\Seeder;

class TeamMemberSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            [
                'name' => 'Daniel Brooks',
                'role' => 'Founder & CEO',
                'initials' => 'DB',
                'color' => '#ff6310',
                'bio' => 'Passionate bibliophile with 15 years in publishing.',
            ],
            [
                'name' => 'Emma Collins',
                'role' => 'Head of Curation',
                'initials' => 'EC',
                'color' => '#6b4fff',
                'bio' => 'Former librarian turned digital curator.',
            ],
            [
                'name' => 'Michael Reed',
                'role' => 'Lead Developer',
                'initials' => 'MR',
                'color' => '#18b96e',
                'bio' => 'Builds the tech that powers your reading journey.',
            ],
            [
                'name' => 'Oliver Grant',
                'role' => 'Marketing Director',
                'initials' => 'OG',
                'color' => '#e84393',
                'bio' => 'Storyteller at heart, strategist by trade.',
            ],
            [
                'name' => 'Sophia Turner',
                'role' => 'Customer Success',
                'initials' => 'ST',
                'color' => '#f5a623',
                'bio' => 'Making sure every reader finds their next favorite book.',
            ],
            [
                'name' => 'Ethan Walker',
                'role' => 'Content Editor',
                'initials' => 'EW',
                'color' => '#2196f3',
                'bio' => 'Reviews, edits, and helps authors shine.',
            ],
            [
                'name' => 'Lucas Meyer',
                'role' => 'Logistics Manager',
                'initials' => 'LM',
                'color' => '#009688',
                'bio' => 'Ensures your order arrives safe and on time.',
            ],
            [
                'name' => 'Noah Bennett',
                'role' => 'Design Lead',
                'initials' => 'NB',
                'color' => '#9c27b0',
                'bio' => 'Crafts the visual experience you love.',
            ],
        ];

        foreach ($members as $index => $data) {
            TeamMember::firstOrCreate(
                ['name' => $data['name']],
                [
                    'role' => $data['role'],
                    'initials' => $data['initials'],
                    'color' => $data['color'],
                    'bio' => $data['bio'],
                    'status' => 1,
                    'sort_order' => $index + 1,
                ],
            );
        }
    }
}
