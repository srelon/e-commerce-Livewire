<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\LayoutController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\StaticController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

Route::get('layout', [LayoutController::class, 'index']);
Route::post('newsletter', [NewsletterController::class, 'store']);

Route::get('csrf-cookie', [CsrfCookieController::class, 'show']);

Route::prefix('auth')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('register', 'register');
        Route::post('login', 'login');
        Route::post('logout', 'logout');
    });
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store']);
    Route::post('reset-password', [NewPasswordController::class, 'store']);
    Route::get('profile', [ProfileController::class, 'show']);
});

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

Route::prefix('products/{slug}/reviews')->controller(ReviewController::class)->group(function () {
    Route::get('/', 'index');
    Route::post('/', 'store')->middleware('auth:sanctum');
    Route::put('{review}', 'update')->middleware('auth:sanctum');
    Route::delete('{review}', 'destroy')->middleware('auth:sanctum');
    Route::post('{review}/react', 'react')->middleware('auth:sanctum');
    Route::post('{review}/report', 'report')->middleware('auth:sanctum');
});

Route::prefix('notifications')->controller(NotificationController::class)->middleware('auth:sanctum')->group(function () {
    Route::get('/', 'index');
    Route::get('all', 'all');
    Route::get('unread-count', 'unreadCount');
    Route::patch('{id}/read', 'markRead');
});

Route::prefix('authors')->controller(AuthorController::class)->group(function () {
    Route::get('/', 'index');
});

Route::prefix('news')->controller(NewsController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('{slug}', 'show');
});
