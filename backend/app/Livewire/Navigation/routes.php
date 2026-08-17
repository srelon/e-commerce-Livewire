<?php

use App\Livewire\Components\Admins;
use App\Livewire\Components\Auth\Login;
use App\Livewire\Components\Catalog\Authors;
use App\Livewire\Components\Catalog\Categories;
use App\Livewire\Components\Catalog\Products;
use App\Livewire\Components\Content\News;
use App\Livewire\Components\Dashboard;
use App\Livewire\Components\Menu;
use App\Livewire\Components\Roles;
use App\Livewire\Components\Users;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', Login::class)->name('login')->middleware('guest:admins');

    Route::middleware('auth:admins')->group(function () {
        Route::get('/', Dashboard::class)->name('dashboard');

        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', Users\Index::class)->name('index');
            Route::get('create', Users\Form::class)->name('create');
            Route::get('{user}/edit', Users\Form::class)->name('edit');
        });

        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', Roles\Index::class)->name('index');
            Route::get('create', Roles\Form::class)->name('create');
            Route::get('{role}/edit', Roles\Form::class)->name('edit');
        });

        Route::prefix('admins')->name('admins.')->group(function () {
            Route::get('/', Admins\Index::class)->name('index');
            Route::get('create', Admins\Form::class)->name('create');
            Route::get('{admin}/edit', Admins\Form::class)->name('edit');
        });

        Route::get('profile', Admins\Profile::class)->name('profile');

        Route::prefix('menus')->name('menus.')->group(function () {
            Route::get('/', Menu\Form::class)->name('index');
        });

        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', Products\Index::class)->name('index');
            Route::get('create', Products\Form::class)->name('create');
            Route::get('{product}/edit', Products\Form::class)->name('edit');
        });

        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', Categories\Form::class)->name('index');
        });

        Route::prefix('authors')->name('authors.')->group(function () {
            Route::get('/', Authors\Form::class)->name('index');
        });

        Route::prefix('news')->name('news.')->group(function () {
            Route::get('/', News\Index::class)->name('index');
            Route::get('create', News\Form::class)->name('create');
            Route::get('{post}/edit', News\Form::class)->name('edit');
        });

        Route::post('logout', function () {
            auth('admins')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            return redirect()->route('admin.login');
        })->name('logout');
    });
});
