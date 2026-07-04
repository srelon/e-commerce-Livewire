<?php

use App\Http\Controllers\AuthorController;
use App\Http\Controllers\LayoutController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StaticController;
use Illuminate\Support\Facades\Route;

Route::get('layout', [LayoutController::class, 'index']);
Route::post('newsletter', [NewsletterController::class, 'store']);

Route::prefix('pages')->controller(StaticController::class)->group(function () {
    Route::get('home', 'home');
    Route::get('contact', 'contact');
    Route::get('about', 'about');
    Route::get('{slug}', 'show');
});

Route::prefix('products')->controller(ProductController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('{slug}', 'show');
});

Route::prefix('authors')->controller(AuthorController::class)->group(function () {
    Route::get('/', 'index');
});

Route::prefix('news')->controller(NewsController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('{slug}', 'show');
});
