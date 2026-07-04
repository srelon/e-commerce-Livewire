<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\SeoMeta;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'home',
                'title' => 'Where Quiet Moments Meet Great Books',
                'excerpt' => 'Discover new releases, bestsellers, and hidden gems on our virtual shelves.',
                'seo_title' => 'BookStore — Discover Your Next Great Read',
                'seo_description' => 'Browse new releases, bestsellers, and hidden gems. Enjoy smooth navigation and secure checkout for a seamless book-buying experience.',
                'seo_keywords' => 'bookstore, online books, bestsellers, new releases',
            ],
            [
                'slug' => 'products',
                'title' => 'Products',
                'excerpt' => 'Browse our full catalog of books across every genre.',
                'seo_title' => 'Shop All Books | BookStore',
                'seo_description' => 'Browse our full catalog of books across every genre, filter by category, author, price, and rating to find your next great read.',
                'seo_keywords' => 'buy books online, book catalog, bestsellers, new releases',
            ],
            [
                'slug' => 'authors',
                'title' => 'Authors',
                'excerpt' => 'Meet the authors behind your favorite books.',
                'seo_title' => 'Authors | BookStore',
                'seo_description' => 'Meet the authors behind your favorite books and explore their full collections.',
                'seo_keywords' => 'book authors, writers, bestselling authors',
            ],
            [
                'slug' => 'news',
                'title' => 'News',
                'excerpt' => 'The latest news, articles, and stories from the world of books.',
                'seo_title' => 'Blog & News | BookStore',
                'seo_description' => 'The latest news, articles, and stories from the world of books and reading.',
                'seo_keywords' => 'book news, literary blog, reading articles',
            ],
            [
                'slug' => 'contact',
                'title' => 'Get In Touch With Us',
                'excerpt' => 'Have a question or need help finding the perfect book? Our team is here for you.',
                'seo_title' => 'Contact Us | BookStore',
                'seo_description' => 'Have a question or need help finding the perfect book? Get in touch with our team.',
                'seo_keywords' => 'contact bookstore, customer support, store location',
            ],
            [
                'slug' => 'about',
                'title' => 'About Us',
                'excerpt' => 'The right book in the right hands at the right time can change the world.',
                'content' => 'We are a passionate team of book lovers dedicated to connecting readers with stories that inspire, educate, and transform. Our curated collection spans every genre.',
                'seo_title' => 'About Us | BookStore',
                'seo_description' => 'We are a passionate team of book lovers dedicated to connecting readers with stories that inspire, educate, and transform.',
                'seo_keywords' => 'about bookstore, our story, book lovers',
            ],
            [
                'slug' => 'cart',
                'title' => 'Your Cart',
                'excerpt' => 'Review your cart and complete checkout.',
                'seo_title' => 'Your Cart | BookStore',
                'seo_description' => 'Review the books in your cart and complete a secure checkout.',
                'seo_keywords' => 'cart, checkout, buy books',
            ],
        ];

        foreach ($pages as $index => $data) {
            $page = Page::firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'title' => $data['title'],
                    'content' => $data['content'] ?? null,
                    'excerpt' => $data['excerpt'],
                    'status' => 1,
                    'sort_order' => $index + 1,
                ],
            );

            SeoMeta::firstOrCreate(
                ['type' => 'pages', 'record_id' => $page->id],
                [
                    'seo_title' => $data['seo_title'],
                    'seo_description' => $data['seo_description'],
                    'seo_keywords' => $data['seo_keywords'],
                ],
            );
        }
    }
}
