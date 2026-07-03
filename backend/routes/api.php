<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LayoutController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('layout', [LayoutController::class, 'index']);
Route::post('newsletter', [NewsletterController::class, 'store']);
Route::get('home', [HomeController::class, 'index']);

Route::prefix('products')->controller(ProductController::class)->group(function () {
    Route::get('/', 'index');
});
