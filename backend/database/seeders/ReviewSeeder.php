<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    private const BODIES = [
        5 => [
            'Absolutely loved this one — couldn\'t put it down until the last page.',
            'One of the best books I\'ve read this year. Highly recommend.',
            'The writing is beautiful and the story stayed with me long after I finished it.',
            'Exceeded my expectations in every way. A must-read.',
            'Five stars without hesitation — I\'ll be recommending this to everyone I know.',
        ],
        4 => [
            'Really enjoyed this one, just a bit slow in the middle chapters.',
            'Well written and engaging, though the ending felt a touch rushed.',
            'A solid read overall — minor pacing issues but worth the time.',
            'Great book, would happily read more from this author.',
        ],
        3 => [
            'Decent read, but didn\'t quite live up to the hype for me.',
            'Some strong moments, but overall it felt a bit uneven.',
            'It was okay — enjoyable enough, just not something I\'d reread.',
        ],
        2 => [
            'Struggled to stay engaged, the pacing really didn\'t work for me.',
            'Not really my style, though I can see why others might like it.',
        ],
        1 => [
            'Unfortunately this one just wasn\'t for me.',
            'Didn\'t finish it — the story didn\'t hold my attention.',
        ],
    ];

    private const RATING_WEIGHTS = [5, 5, 5, 4, 4, 4, 3, 3, 2, 1];

    public function run(): void {
        $users = User::all();

        if ($users->isEmpty()) {
            return;
        }

        Product::all()->each(function (Product $product) use ($users) {
            if (random_int(1, 100) <= 10) {
                return;
            }

            $reviewer_count = min($users->count(), random_int(1, 6));
            $reviewers = $users->random($reviewer_count);

            foreach ($reviewers as $user) {
                $rating = self::RATING_WEIGHTS[array_rand(self::RATING_WEIGHTS)];
                $bodies = self::BODIES[$rating];

                $review = $product->reviews()->create([
                    'user_id' => $user->id,
                    'rating' => $rating,
                    'body' => $bodies[array_rand($bodies)],
                    'status' => 1,
                ]);

                $created_at = now()->subDays(random_int(0, 60))->subHours(random_int(0, 23));
                $review->created_at = $created_at;
                $review->updated_at = $created_at;
                $review->save();
            }

            $avg = $product->reviews()->whereNull('parent_id')->avg('rating');

            $product->update([
                'rating_avg' => $avg !== null ? round($avg, 1) : null,
                'reviews_count' => $product->reviews()->whereNull('parent_id')->count(),
            ]);
        });
    }
}
