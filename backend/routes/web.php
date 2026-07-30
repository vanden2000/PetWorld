<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminAccountController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\PetSpeciesController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductAiContentController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\BlogCommentController;
use App\Http\Controllers\Admin\KnowledgeArticleController;


Route::get('/', fn() => response('XIn chào'));

// Admin authentication routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])
        ->name('dashboard');

    Route::get('/account', [AdminAccountController::class, 'edit'])
        ->name('account.edit');

    Route::put('/account/profile', [AdminAccountController::class, 'updateProfile'])
        ->name('account.profile.update');

    Route::put('/account/password', [AdminAccountController::class, 'updatePassword'])
        ->name('account.password.update');

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

    Route::get('/pet-species', [PetSpeciesController::class, 'index'])->name('pet-species');
    Route::get('/pet-species/create', [PetSpeciesController::class, 'create'])->name('pet-species.create');
    Route::post('/pet-species', [PetSpeciesController::class, 'store'])->name('pet-species.store');
    Route::get('/pet-species/{petSpecies}/edit', [PetSpeciesController::class, 'edit'])->name('pet-species.edit');
    Route::put('/pet-species/{petSpecies}', [PetSpeciesController::class, 'update'])->name('pet-species.update');

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

    Route::get('/banners', [BannerController::class, 'index'])->name('banners');
    Route::get('/banners/create', [BannerController::class, 'create'])->name('banners.create');
    Route::post('/banners', [BannerController::class, 'store'])->name('banners.store');
    Route::get('/banners/{id}/edit', [BannerController::class, 'edit'])->name('banners.edit');
    Route::put('/banners/{id}', [BannerController::class, 'update'])->name('banners.update');
    Route::delete('/banners/{id}', [BannerController::class, 'destroy'])->name('banners.destroy');
    Route::patch('/banners/{id}/toggle-status', [BannerController::class, 'toggleStatus'])->name('banners.toggle-status');

    Route::get('/orders', [OrderController::class, 'index'])
        ->name('orders');

    Route::get('/orders/export', [OrderController::class, 'export'])
        ->name('orders.export');

    Route::get('/orders/{id}/invoice', [OrderController::class, 'invoice'])
        ->name('orders.invoice');

    Route::get('/orders/{id}', [OrderController::class, 'show'])
        ->name('orders.show');

    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])
        ->name('orders.update-status');

    Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews');
    Route::patch('/reviews/{review}/status', [ReviewController::class, 'updateStatus'])->name('reviews.status.update');

    Route::get('/blog-comments', [BlogCommentController::class, 'index'])->name('blog-comments');
    Route::delete('/blog-comments/{comment}', [BlogCommentController::class, 'destroy'])->name('blog-comments.destroy');

    Route::get('/posts', [PostController::class, 'index'])->name('posts');
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::patch('/posts/{post}/status', [PostController::class, 'updateStatus'])->name('posts.status');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

    Route::get('/knowledge', [KnowledgeArticleController::class, 'index'])->name('knowledge');
    Route::get('/knowledge/create', [KnowledgeArticleController::class, 'create'])->name('knowledge.create');
    Route::post('/knowledge', [KnowledgeArticleController::class, 'store'])->name('knowledge.store');
    Route::get('/knowledge/{article}/edit', [KnowledgeArticleController::class, 'edit'])->name('knowledge.edit');
    Route::put('/knowledge/{article}', [KnowledgeArticleController::class, 'update'])->name('knowledge.update');

    Route::get('/products', [ProductController::class, 'index'])
        ->name('products');

    Route::get('/products/create', [ProductController::class, 'create'])
        ->name('products.create');

    Route::post('/products', [ProductController::class, 'store'])
        ->name('products.store');

    Route::post('/products/ai/improve', ProductAiContentController::class)
        ->middleware('throttle:10,10')
        ->name('products.ai.improve');

    Route::post('/posts/ai/improve', \App\Http\Controllers\Admin\PostAiContentController::class)
        ->middleware('throttle:10,10')
        ->name('posts.ai.improve');

    Route::get('/products/export', [ProductController::class, 'export'])
        ->name('products.export');

    Route::get('/products/variants', [ProductController::class, 'variants'])
        ->name('products.variants');

    Route::post('/products/variants/types', [ProductController::class, 'storeVariantType'])
        ->name('products.variants.types.store');

    Route::put('/products/variants/types/{variantType}', [ProductController::class, 'updateVariantType'])
        ->name('products.variants.types.update');

    Route::delete('/products/variants/types/{variantType}', [ProductController::class, 'destroyVariantType'])
        ->name('products.variants.types.destroy');

    Route::post('/products/variants/types/{variantType}/values', [ProductController::class, 'storeVariantValue'])
        ->name('products.variants.values.store');

    Route::put('/products/variants/values/{variantValue}', [ProductController::class, 'updateVariantValue'])
        ->name('products.variants.values.update');

    Route::delete('/products/variants/values/{variantValue}', [ProductController::class, 'destroyVariantValue'])
        ->name('products.variants.values.destroy');

    Route::get('/products/{id}/edit', [ProductController::class, 'edit'])
        ->name('products.edit');

    Route::put('/products/{id}', [ProductController::class, 'update'])
        ->name('products.update');

    Route::patch('/products/{product}/status', [ProductController::class, 'updateStatus'])
        ->name('products.status.update');

    // Route::get('/reviews', [ReviewController::class, 'index'])
//     ->name('admin.reviews');

    Route::get('/users', [UserController::class, 'index'])
        ->name('users');

    Route::patch('/users/{user}/status', [UserController::class, 'updateStatus'])
        ->name('users.status.update');

    Route::patch('/users/{user}/grant-admin', [UserController::class, 'grantAdmin'])
        ->name('users.grant-admin');

    Route::patch('/users/{user}/revoke-admin', [UserController::class, 'revokeAdmin'])
        ->name('users.revoke-admin');

    Route::get('/vouchers', [VoucherController::class, 'index'])->name('vouchers');
    Route::get('/vouchers/create', [VoucherController::class, 'create'])->name('vouchers.create');
    Route::post('/vouchers', [VoucherController::class, 'store'])->name('vouchers.store');
    Route::get('/vouchers/{id}/edit', [VoucherController::class, 'edit'])->name('vouchers.edit');
    Route::put('/vouchers/{id}', [VoucherController::class, 'update'])->name('vouchers.update');
    Route::delete('/vouchers/{id}', [VoucherController::class, 'destroy'])->name('vouchers.destroy');

    // Reports/Statistics routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/revenue', [ReportController::class, 'revenue'])->name('revenue');
        Route::get('/order-status', [ReportController::class, 'orderStatus'])->name('order-status');
        Route::get('/customers', [ReportController::class, 'customers'])->name('customers');
        Route::get('/best-sellers', [ReportController::class, 'bestSellers'])->name('best-sellers');
        Route::get('/low-stock', [ReportController::class, 'lowStock'])->name('low-stock');
        Route::get('/latest-orders', [ReportController::class, 'latestOrders'])->name('latest-orders');
    });
});
