<?php

use App\Livewire\Admin\Admins;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Menu;
use App\Livewire\Admin\Roles;
use App\Livewire\Admin\Users;
use App\Livewire\Auth\Login;
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

        Route::prefix('menus')->name('menus.')->group(function () {
            Route::get('/', Menu\Tree::class)->name('index');
        });

        Route::post('logout', function () {
            auth('admins')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            return redirect()->route('admin.login');
        })->name('logout');
    });
});
