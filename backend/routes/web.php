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

Route::get('/', [AdminController::class, 'index'])
    ->name('admin.dashboard');

Route::get('/categories', [CategoryController::class, 'index'])
    ->name('admin.categories');

Route::get('/categories/create', [CategoryController::class, 'create'])
    ->name('admin.categories.create');

Route::get('/brands', [BrandController::class, 'index'])
    ->name('admin.brands');

Route::get('/brands/create', [BrandController::class, 'create'])
    ->name('admin.brands.create');

Route::get('/banners', [BannerController::class, 'index'])
    ->name('admin.banners');

Route::get('/orders', [OrderController::class, 'index'])
    ->name('admin.orders');

Route::get('/orders/{id}', [OrderController::class, 'show'])
    ->name('admin.orders.show');

Route::get('/posts', [PostController::class, 'index'])
    ->name('admin.posts');

Route::get('/products', [ProductController::class, 'index'])
    ->name('admin.products');

Route::get('/products/create', [ProductController::class, 'create'])
    ->name('admin.products.create');

Route::get('/products/variants', [ProductController::class, 'variants'])
    ->name('admin.products.variants');

// Route::get('/reviews', [ReviewController::class, 'index'])
//     ->name('admin.reviews');

Route::get('/users', [UserController::class, 'index'])
    ->name('admin.users');

Route::get('/vouchers', [VoucherController::class, 'index'])
    ->name('admin.vouchers');