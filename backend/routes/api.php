<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\CheckoutOptionController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SepayWebhookController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WishlistController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Xác thực bằng Sanctum personal access token (frontend Next.js khác origin).
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist/{product}', [WishlistController::class, 'store']);
    Route::delete('/wishlist/{product}', [WishlistController::class, 'destroy']);

    // Sổ địa chỉ giao hàng
    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::put('/addresses/{address}', [AddressController::class, 'update']);
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy']);

    // Đơn hàng
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
});

Route::get('/home', HomeController::class);
Route::get('/checkout-options', CheckoutOptionController::class);
// Webhook SePay gọi về khi nhận được chuyển khoản (xác thực bằng API key bên trong).
Route::post('/webhooks/sepay', SepayWebhookController::class);
Route::get('/blogs', [BlogController::class, 'index']);
Route::get('/blogs/{slug}', [BlogController::class, 'show']);
Route::get('/products', [ProductController::class, 'index']);
// đã xem gần đây
Route::get('/products/recent', [ProductController::class, 'recent']);
Route::get('/products/{slug}', [ProductController::class, 'show']);
