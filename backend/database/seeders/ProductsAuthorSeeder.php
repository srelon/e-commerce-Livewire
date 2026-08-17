<?php

namespace Database\Seeders;

use App\Models\ProductsAuthor;
use Illuminate\Database\Seeder;

class ProductsAuthorSeeder extends Seeder
{
    public function run(): void {
        $authors = [
            [
                'name' => 'Oliver Hartman',
                'avatar_color' => '#ff6310',
                'content' => 'Oliver Hartman is a bestselling author known for his compelling narratives and deep character studies across multiple genres.',
            ],
            [
                'name' => 'Clara Whitfield',
                'avatar_color' => '#6b4fff',
                'content' => 'Clara Whitfield writes with elegance and precision, crafting stories that linger long after the last page is turned.',
            ],
            [
                'name' => 'Henry Caldwell',
                'avatar_color' => '#18b96e',
                'content' => 'Henry Caldwell is celebrated for his rich historical fiction and immersive world-building that transports readers to another era.',
            ],
            [
                'name' => 'Amelia Brooks',
                'avatar_color' => '#e84393',
                'content' => 'Amelia Brooks combines vivid imagination with emotional depth to create unforgettable journeys through her books.',
            ],
            [
                'name' => 'Nathaniel Parker',
                'avatar_color' => '#f5a623',
                'content' => 'Nathaniel Parker is a prolific author whose works span science, fantasy, and adventure with a unique poetic voice.',
            ],
            [
                'name' => 'Eleanor Finch',
                'avatar_color' => '#2196f3',
                'content' => 'Eleanor Finch writes thought-provoking literary fiction that challenges conventions and celebrates the human spirit.',
            ],
            [
                'name' => 'Marcus Langley',
                'avatar_color' => '#8e44ad',
                'content' => 'Marcus Langley crafts gripping thrillers with unexpected twists that keep readers guessing until the final page.',
            ],
            [
                'name' => 'Sophia Reyes',
                'avatar_color' => '#00a8a8',
                'content' => 'Sophia Reyes is known for her lush, atmospheric prose and richly drawn characters across contemporary fiction.',
            ],
            [
                'name' => 'Daniel Okafor',
                'avatar_color' => '#d35400',
                'content' => 'Daniel Okafor blends sharp wit with social commentary in his acclaimed collection of short stories.',
            ],
            [
                'name' => 'Ivy Sinclair',
                'avatar_color' => '#c2185b',
                'content' => 'Ivy Sinclair writes cozy mysteries set in small towns, beloved for their warmth and clever plotting.',
            ],
            [
                'name' => 'Gabriel Moreno',
                'avatar_color' => '#2e7d32',
                'content' => 'Gabriel Moreno explores speculative futures with a keen eye for technology and its human cost.',
            ],
            [
                'name' => 'Lucy Bennett',
                'avatar_color' => '#5c6bc0',
                'content' => 'Lucy Bennett writes heartfelt coming-of-age stories that resonate with readers of every generation.',
            ],
            [
                'name' => 'Theodore Marsh',
                'avatar_color' => '#795548',
                'content' => 'Theodore Marsh is a veteran travel writer whose essays capture the character of every place he visits.',
            ],
            [
                'name' => 'Ruby Anderson',
                'avatar_color' => '#ef6c00',
                'content' => 'Ruby Anderson writes sweeping family sagas spanning generations, praised for their emotional depth.',
            ],
        ];

        foreach ($authors as $index => $author) {
            $record = ProductsAuthor::firstOrCreate(['name' => $author['name']], $author);
            $record->forceFill(['created_at' => now()->subDays(count($authors) - $index)])->save();
        }
    }
}
