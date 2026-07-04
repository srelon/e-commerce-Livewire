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
                'content' => [
                    'Curabitur arcu erat, accumsan id imperdiet et, porttitor at sem. Curabitur aliquet quam id dui posuere blandit. Nulla porttitor accumsan tincidunt. Ornare curabitur vitae scelerisque nulla porttitor accumsan tincidunt cras ultricies ligula.',
                    'Sed magna dictum porta. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae. Proin vel ante a orci tempus eleifend ut et magna. Sed porttitor lectus nibh curabitur arcu erat.',
                    'Accumsan id imperdiet et porttitor at sem donec rutrum congue leo eget malesuada quisque velit nisi pretium ut lacinia in elementum id enim pellentesque in ipsum id orci porta dapibus proin eget tortor risus.',
                ],
            ],
            [
                'title' => 'Massa Fames Eleifend Convallis',
                'excerpt' => 'Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; proin vel ante a orci tempus eleifend.',
                'category' => 'Literature',
                'image' => 'blog-image-5.webp',
                'date' => 'December 15, 2025',
                'content' => [
                    'Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae. Proin vel ante a orci tempus eleifend ut et magna massa fames eleifend convallis sed porttitor lectus nibh curabitur arcu erat.',
                    'Accumsan id imperdiet et porttitor at sem donec rutrum congue leo eget malesuada. Quisque velit nisi pretium ut lacinia in elementum id enim pellentesque in ipsum id orci porta dapibus.',
                    'Proin eget tortor risus curabitur non nulla sit amet nisl tempus convallis quis ac lectus praesent sapien massa convallis a pellentesque nec egestas non nisi nulla quis lorem ut libero malesuada feugiat.',
                ],
            ],
            [
                'title' => 'Porttitor Suspendisse Bibendum',
                'excerpt' => 'Nulla porttitor accumsan tincidunt. Cras ultricies ligula sed magna dictum porta.',
                'category' => 'Reading',
                'image' => 'blog-image-6.webp',
                'date' => 'December 12, 2025',
                'content' => [
                    'Nulla porttitor accumsan tincidunt cras ultricies ligula sed magna dictum porta suspendisse bibendum vestibulum ac diam sit amet quam vehicula elementum sed sit amet dui.',
                    'Curabitur aliquet quam id dui posuere blandit nulla porttitor accumsan tincidunt vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae proin vel ante a orci tempus eleifend.',
                    'Sed porttitor lectus nibh curabitur arcu erat accumsan id imperdiet et porttitor at sem donec rutrum congue leo eget malesuada quisque velit nisi pretium ut lacinia in elementum id enim.',
                ],
            ],
            [
                'title' => 'Platea Justo Curabitur Consequat',
                'excerpt' => 'Quisque velit nisi, pretium ut lacinia in, elementum id enim. Donec rutrum congue leo eget malesuada.',
                'category' => 'Authors',
                'image' => 'blog-image-7.webp',
                'date' => 'December 10, 2025',
                'content' => [
                    'Quisque velit nisi pretium ut lacinia in elementum id enim donec rutrum congue leo eget malesuada platea justo curabitur consequat pellentesque in ipsum id orci porta dapibus.',
                    'Proin eget tortor risus curabitur non nulla sit amet nisl tempus convallis quis ac lectus praesent sapien massa convallis a pellentesque nec egestas non nisi nulla quis lorem ut libero malesuada feugiat.',
                    'Mauris blandit aliquet elit eget tincidunt nibh pulvinar a praesent sapien massa convallis a pellentesque nec egestas non nisi vestibulum ante ipsum primis in faucibus orci luctus et ultrices.',
                ],
            ],
            [
                'title' => 'Volutpat Tempor Accumsan Porta',
                'excerpt' => 'Pellentesque in ipsum id orci porta dapibus. Curabitur non nulla sit amet nisl tempus convallis quis ac lectus.',
                'category' => 'Cultural',
                'image' => 'blog-image-8.webp',
                'date' => 'December 8, 2025',
                'content' => [
                    'Pellentesque in ipsum id orci porta dapibus curabitur non nulla sit amet nisl tempus convallis quis ac lectus volutpat tempor accumsan porta sed magna dictum porta.',
                    'Praesent sapien massa convallis a pellentesque nec egestas non nisi nulla quis lorem ut libero malesuada feugiat vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae.',
                    'Curabitur arcu erat accumsan id imperdiet et porttitor at sem donec rutrum congue leo eget malesuada quisque velit nisi pretium ut lacinia in elementum id enim.',
                ],
            ],
            [
                'title' => 'Sagittis Vitae Et Leo Duis',
                'excerpt' => 'Quam elementum pulvinar etiam non quam. Faucibus nisl tincidunt eget nullam non nisi elementum sagittis vitae et leo.',
                'category' => 'Literature',
                'image' => 'blog-image-9.webp',
                'date' => 'December 5, 2025',
                'content' => [
                    'Quam elementum pulvinar etiam non quam faucibus nisl tincidunt eget nullam non nisi elementum sagittis vitae et leo duis pellentesque in ipsum id orci porta dapibus.',
                    'Mauris blandit aliquet elit eget tincidunt nibh pulvinar a praesent sapien massa convallis a pellentesque nec egestas non nisi vestibulum ante ipsum primis in faucibus orci luctus.',
                    'Sed porttitor lectus nibh curabitur arcu erat accumsan id imperdiet et porttitor at sem donec rutrum congue leo eget malesuada quisque velit nisi pretium ut lacinia in elementum id enim.',
                ],
            ],
            [
                'title' => 'Nibh Pulvinar A Praesent Sapien',
                'excerpt' => 'Mauris blandit aliquet elit, eget tincidunt nibh pulvinar a. Praesent sapien massa, convallis a pellentesque nec egestas.',
                'category' => 'Reading',
                'image' => 'blog-image-3.webp',
                'date' => 'December 2, 2025',
                'content' => [
                    'Mauris blandit aliquet elit eget tincidunt nibh pulvinar a praesent sapien massa convallis a pellentesque nec egestas non nisi vestibulum ante ipsum primis in faucibus orci luctus et ultrices.',
                    'Posuere cubilia curae proin vel ante a orci tempus eleifend ut et magna sed porttitor lectus nibh curabitur arcu erat accumsan id imperdiet et porttitor at sem.',
                    'Donec rutrum congue leo eget malesuada quisque velit nisi pretium ut lacinia in elementum id enim pellentesque in ipsum id orci porta dapibus proin eget tortor risus.',
                ],
            ],
            [
                'title' => 'Accumsan Id Imperdiet Et Porttitor',
                'excerpt' => 'Curabitur arcu erat, accumsan id imperdiet et, porttitor at sem. Curabitur aliquet quam id dui posuere blandit nulla.',
                'category' => 'Authors',
                'image' => 'blog-image-4.webp',
                'date' => 'November 28, 2025',
                'content' => [
                    'Curabitur arcu erat accumsan id imperdiet et porttitor at sem curabitur aliquet quam id dui posuere blandit nulla porttitor accumsan tincidunt vestibulum ac diam sit amet quam vehicula.',
                    'Elementum sed sit amet dui cras ultricies ligula sed magna dictum porta vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae proin vel ante.',
                    'A orci tempus eleifend ut et magna sed porttitor lectus nibh curabitur arcu erat accumsan id imperdiet et porttitor at sem donec rutrum congue leo eget malesuada.',
                ],
            ],
            [
                'title' => 'Elementum Id Enim Donec Rutrum',
                'excerpt' => 'Quisque velit nisi, pretium ut lacinia in, elementum id enim. Donec rutrum congue leo eget malesuada quisque velit.',
                'category' => 'Cultural',
                'image' => 'blog-image-5.webp',
                'date' => 'November 24, 2025',
                'content' => [
                    'Quisque velit nisi pretium ut lacinia in elementum id enim donec rutrum congue leo eget malesuada quisque velit pellentesque in ipsum id orci porta dapibus proin eget tortor risus.',
                    'Curabitur non nulla sit amet nisl tempus convallis quis ac lectus praesent sapien massa convallis a pellentesque nec egestas non nisi nulla quis lorem ut libero malesuada feugiat.',
                    'Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae proin vel ante a orci tempus eleifend ut et magna sed porttitor lectus nibh.',
                ],
            ],
            [
                'title' => 'Ac Diam Sit Amet Quam Vehicula',
                'excerpt' => 'Vestibulum ac diam sit amet quam vehicula elementum sed sit amet dui. Cras ultricies ligula sed magna dictum porta.',
                'category' => 'Literature',
                'image' => 'blog-image-6.webp',
                'date' => 'November 19, 2025',
                'content' => [
                    'Vestibulum ac diam sit amet quam vehicula elementum sed sit amet dui cras ultricies ligula sed magna dictum porta ac diam sit amet quam vehicula elementum.',
                    'Curabitur aliquet quam id dui posuere blandit nulla porttitor accumsan tincidunt vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae proin vel ante.',
                    'A orci tempus eleifend ut et magna sed porttitor lectus nibh curabitur arcu erat accumsan id imperdiet et porttitor at sem donec rutrum congue leo eget malesuada.',
                ],
            ],
            [
                'title' => 'Malesuada Feugiat Nulla Quis Lorem',
                'excerpt' => 'Proin eget tortor risus. Curabitur non nulla sit amet nisl tempus convallis quis ac lectus praesent sapien massa.',
                'category' => 'Reading',
                'image' => 'blog-image-7.webp',
                'date' => 'November 14, 2025',
                'content' => [
                    'Proin eget tortor risus curabitur non nulla sit amet nisl tempus convallis quis ac lectus praesent sapien massa malesuada feugiat nulla quis lorem ut libero malesuada feugiat.',
                    'Convallis a pellentesque nec egestas non nisi vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae proin vel ante a orci tempus eleifend.',
                    'Ut et magna sed porttitor lectus nibh curabitur arcu erat accumsan id imperdiet et porttitor at sem donec rutrum congue leo eget malesuada quisque velit nisi pretium.',
                ],
            ],
            [
                'title' => 'Dictum Porta Nibh Venenatis Cras',
                'excerpt' => 'Nulla porttitor accumsan tincidunt. Cras ultricies ligula sed magna dictum porta nibh venenatis cras sed felis.',
                'category' => 'Authors',
                'image' => 'blog-image-8.webp',
                'date' => 'November 9, 2025',
                'content' => [
                    'Nulla porttitor accumsan tincidunt cras ultricies ligula sed magna dictum porta nibh venenatis cras sed felis dictum porta nibh venenatis cras sed felis.',
                    'Vestibulum ac diam sit amet quam vehicula elementum sed sit amet dui curabitur aliquet quam id dui posuere blandit nulla porttitor accumsan tincidunt vestibulum ante ipsum primis.',
                    'In faucibus orci luctus et ultrices posuere cubilia curae proin vel ante a orci tempus eleifend ut et magna sed porttitor lectus nibh curabitur arcu erat accumsan.',
                ],
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
