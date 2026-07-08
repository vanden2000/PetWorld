<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\CheckoutOptionController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SepayWebhookController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\ReviewController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Models\User;
use Illuminate\Auth\Events\Verified;

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

// Forgot Password API routes
Route::prefix('forgot-password')->group(function () {
    Route::post('/send-otp', [\App\Http\Controllers\Api\ForgotPasswordController::class, 'sendOtp'])->middleware('throttle:5,1');
    Route::post('/verify-otp', [\App\Http\Controllers\Api\ForgotPasswordController::class, 'verifyOtp']);
    Route::post('/reset-password', [\App\Http\Controllers\Api\ForgotPasswordController::class, 'resetPassword']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);
    Route::put('/user', [AuthController::class, 'updateProfile']);
    Route::post('/user/avatar', [AuthController::class, 'updateAvatar']);
    Route::put('/user/password', [AuthController::class, 'updatePassword']);
    Route::apiResource('addresses', AddressController::class)->except(['show']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist/{product}', [WishlistController::class, 'store']);
    Route::delete('/wishlist/{product}', [WishlistController::class, 'destroy']);
    Route::get('/vouchers', [\App\Http\Controllers\Api\VoucherController::class, 'index']);
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


// check email 
Route::get('/email/verify/{id}/{hash}', function (Request $request, int $id, string $hash) {
    $user = User::query()->findOrFail($id);

    // Kiểm tra hash email trong đường dẫn.
    abort_unless(
        hash_equals(
            (string) $hash,
            sha1($user->getEmailForVerification())
        ),
        403,
        'Liên kết xác minh không hợp lệ.'
    );

    // Chỉ cập nhật nếu email chưa được xác minh.
    if (!$user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();

        event(new Verified($user));
    }

    // Xác minh xong thì chuyển sang trang Next.js.
    return redirect(
        config('app.frontend_url')
        . '/verify-email/success'
    );
})->middleware([
            'signed',
            'throttle:6,1',
        ])->name('verification.verify');