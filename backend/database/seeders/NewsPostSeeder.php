<?php

namespace Database\Seeders;

use App\Models\NewsCategory;
use App\Models\NewsPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class NewsPostSeeder extends Seeder
{
    public function run(): void
    {
        $posts = [
            [
                'title' => 'Blandit Praesent Morbi Faucibus',
                'excerpt' => 'Mauris blandit aliquet elit, eget tincidunt nibh pulvinar a. Praesent sapien massa, convallis a pellentesque nec.',
                'category' => 'Literature',
                'image' => 'blog-image-3.webp',
                'date' => 'December 20, 2025',
                'content' => [
                    'Mauris blandit aliquet elit, eget tincidunt nibh pulvinar a. Praesent sapien massa, convallis a pellentesque nec, egestas non nisi. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; proin vel ante a orci tempus eleifend ut et magna.',
                    'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur aliquet quam id dui posuere blandit. Nulla porttitor accumsan tincidunt. Vestibulum ac diam sit amet quam vehicula elementum sed sit amet dui. Cras ultricies ligula sed magna dictum porta.',
                    'Sed porttitor lectus nibh. Curabitur arcu erat, accumsan id imperdiet et, porttitor at sem. Donec rutrum congue leo eget malesuada. Quisque velit nisi, pretium ut lacinia in, elementum id enim. Pellentesque in ipsum id orci porta dapibus.',
                    'Proin eget tortor risus. Curabitur non nulla sit amet nisl tempus convallis quis ac lectus. Praesent sapien massa, convallis a pellentesque nec, egestas non nisi. Nulla quis lorem ut libero malesuada feugiat.',
                ],
            ],
            [
                'title' => 'Ornare Curabitur Vitae Scelerisque',
                'excerpt' => 'Curabitur arcu erat, accumsan id imperdiet et, porttitor at sem. Curabitur aliquet quam id dui posuere blandit.',
                'category' => 'Cultural',
                'image' => 'blog-image-4.webp',
                'date' => 'December 18, 2025',
            ],
            [
                'title' => 'Massa Fames Eleifend Convallis',
                'excerpt' => 'Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; proin vel ante a orci tempus eleifend.',
                'category' => 'Literature',
                'image' => 'blog-image-5.webp',
                'date' => 'December 15, 2025',
            ],
            [
                'title' => 'Porttitor Suspendisse Bibendum',
                'excerpt' => 'Nulla porttitor accumsan tincidunt. Cras ultricies ligula sed magna dictum porta.',
                'category' => 'Reading',
                'image' => 'blog-image-6.webp',
                'date' => 'December 12, 2025',
            ],
            [
                'title' => 'Platea Justo Curabitur Consequat',
                'excerpt' => 'Quisque velit nisi, pretium ut lacinia in, elementum id enim. Donec rutrum congue leo eget malesuada.',
                'category' => 'Authors',
                'image' => 'blog-image-7.webp',
                'date' => 'December 10, 2025',
            ],
            [
                'title' => 'Volutpat Tempor Accumsan Porta',
                'excerpt' => 'Pellentesque in ipsum id orci porta dapibus. Curabitur non nulla sit amet nisl tempus convallis quis ac lectus.',
                'category' => 'Cultural',
                'image' => 'blog-image-8.webp',
                'date' => 'December 8, 2025',
            ],
            [
                'title' => 'Sagittis Vitae Et Leo Duis',
                'excerpt' => 'Quam elementum pulvinar etiam non quam. Faucibus nisl tincidunt eget nullam non nisi elementum sagittis vitae et leo.',
                'category' => 'Literature',
                'image' => 'blog-image-9.webp',
                'date' => 'December 5, 2025',
            ],
            [
                'title' => 'Nibh Pulvinar A Praesent Sapien',
                'excerpt' => 'Mauris blandit aliquet elit, eget tincidunt nibh pulvinar a. Praesent sapien massa, convallis a pellentesque nec egestas.',
                'category' => 'Reading',
                'image' => 'blog-image-3.webp',
                'date' => 'December 2, 2025',
            ],
            [
                'title' => 'Accumsan Id Imperdiet Et Porttitor',
                'excerpt' => 'Curabitur arcu erat, accumsan id imperdiet et, porttitor at sem. Curabitur aliquet quam id dui posuere blandit nulla.',
                'category' => 'Authors',
                'image' => 'blog-image-4.webp',
                'date' => 'November 28, 2025',
            ],
            [
                'title' => 'Elementum Id Enim Donec Rutrum',
                'excerpt' => 'Quisque velit nisi, pretium ut lacinia in, elementum id enim. Donec rutrum congue leo eget malesuada quisque velit.',
                'category' => 'Cultural',
                'image' => 'blog-image-5.webp',
                'date' => 'November 24, 2025',
            ],
            [
                'title' => 'Ac Diam Sit Amet Quam Vehicula',
                'excerpt' => 'Vestibulum ac diam sit amet quam vehicula elementum sed sit amet dui. Cras ultricies ligula sed magna dictum porta.',
                'category' => 'Literature',
                'image' => 'blog-image-6.webp',
                'date' => 'November 19, 2025',
            ],
            [
                'title' => 'Malesuada Feugiat Nulla Quis Lorem',
                'excerpt' => 'Proin eget tortor risus. Curabitur non nulla sit amet nisl tempus convallis quis ac lectus praesent sapien massa.',
                'category' => 'Reading',
                'image' => 'blog-image-7.webp',
                'date' => 'November 14, 2025',
            ],
            [
                'title' => 'Dictum Porta Nibh Venenatis Cras',
                'excerpt' => 'Nulla porttitor accumsan tincidunt. Cras ultricies ligula sed magna dictum porta nibh venenatis cras sed felis.',
                'category' => 'Authors',
                'image' => 'blog-image-8.webp',
                'date' => 'November 9, 2025',
            ],
        ];

        foreach ($posts as $data) {
            $category = NewsCategory::where('name', $data['category'])->firstOrFail();

            NewsPost::firstOrCreate(
                ['slug' => Str::slug($data['title'])],
                [
                    'category_id' => $category->id,
                    'title' => $data['title'],
                    'excerpt' => $data['excerpt'],
                    'content' => isset($data['content']) ? implode("\n\n", $data['content']) : null,
                    'image' => ['original' => "news/{$data['image']}"],
                    'published_at' => Carbon::parse($data['date']),
                    'status' => 1,
                ],
            );
        }
    }
}
