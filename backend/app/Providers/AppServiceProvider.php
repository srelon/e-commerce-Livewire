<?php

namespace App\Providers;

use App\Models\NewsPost;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductsCategory;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void {
        Relation::enforceMorphMap([
            'products' => Product::class,
            'products_categories' => ProductsCategory::class,
            'news_posts' => NewsPost::class,
            'pages' => Page::class,
        ]);

        ResetPassword::createUrlUsing(function (User $user, string $token) {
            $frontend_url = rtrim(config('app.frontend_url'), '/');

            return "{$frontend_url}/reset-password?token={$token}&email=".urlencode($user->email);
        });
    }
}
