<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Admin\LoginController;

// Admin authentication routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])
        ->name('dashboard');

    Route::get('/categories', [CategoryController::class, 'index'])
        ->name('categories');

    Route::get('/categories/create', [CategoryController::class, 'create'])
        ->name('categories.create');

    Route::post('/categories', [CategoryController::class, 'store'])
        ->name('categories.store');

    Route::get('/categories/{slug}/edit', [CategoryController::class, 'edit'])
        ->name('categories.edit');

    Route::put('/categories/{slug}', [CategoryController::class, 'update'])
        ->name('categories.update');

    Route::get('/brands', [BrandController::class, 'index'])
        ->name('brands');

    Route::get('/brands/create', [BrandController::class, 'create'])
        ->name('brands.create');

    Route::post('/brands', [BrandController::class, 'store'])
        ->name('brands.store');

    Route::get('/brands/{id}/edit', [BrandController::class, 'edit'])
        ->name('brands.edit');

    Route::put('/brands/{id}', [BrandController::class, 'update'])
        ->name('brands.update');

    Route::get('/banners', [BannerController::class, 'index'])
        ->name('banners');

    Route::get('/orders', [OrderController::class, 'index'])
        ->name('orders');

    Route::get('/orders/{id}', [OrderController::class, 'show'])
        ->name('orders.show');

    Route::get('/posts', [PostController::class, 'index'])
        ->name('posts');

    Route::get('/products', [ProductController::class, 'index'])
        ->name('products');

    Route::get('/products/create', [ProductController::class, 'create'])
        ->name('products.create');

    Route::get('/products/variants', [ProductController::class, 'variants'])
        ->name('products.variants');

    Route::get('/products/{id}/edit', [ProductController::class, 'edit'])
        ->name('products.edit');

    Route::put('/products/{id}', [ProductController::class, 'update'])
        ->name('products.update');

    Route::delete('/products/{id}', [ProductController::class, 'destroy'])
        ->name('products.destroy');

    // Route::get('/reviews', [ReviewController::class, 'index'])
//     ->name('admin.reviews');

    Route::get('/users', [UserController::class, 'index'])
        ->name('users');

    Route::get('/vouchers', [VoucherController::class, 'index'])->name('vouchers');
    Route::get('/vouchers/create', [VoucherController::class, 'create'])->name('vouchers.create');
    Route::post('/vouchers', [VoucherController::class, 'store'])->name('vouchers.store');
    Route::get('/vouchers/{id}/edit', [VoucherController::class, 'edit'])->name('vouchers.edit');
    Route::put('/vouchers/{id}', [VoucherController::class, 'update'])->name('vouchers.update');
    Route::delete('/vouchers/{id}', [VoucherController::class, 'destroy'])->name('vouchers.destroy');
});