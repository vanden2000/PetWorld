<?php

namespace App\Providers;

use App\Models\BlogComment;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\Voucher;
use App\Observers\OrderObserver;
use App\Observers\VoucherObserver;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Order::observe(OrderObserver::class);
        Voucher::observe(VoucherObserver::class);

        View::composer('admin.layouts.app', function ($view): void {
            $newCommentsCount = BlogComment::query()
                ->where('created_at', '>=', now()->subDay())
                ->count();

            $newOrdersCount = Order::query()
                ->where('order_status', 'pending')
                ->count();

            $lowStockVariantsCount = ProductVariant::query()
                ->where('status', 'active')
                ->where('quantity', '>', 0)
                ->where('quantity', '<=', 5)
                ->count();

            $view->with([
                'adminNotifications' => [
                    [
                        'icon' => 'fa-regular fa-comment-dots',
                        'label' => 'Comment mới',
                        'count' => $newCommentsCount,
                        'url' => route('admin.posts'),
                    ],
                    [
                        'icon' => 'fa-solid fa-cart-plus',
                        'label' => 'Đơn hàng mới',
                        'count' => $newOrdersCount,
                        'url' => route('admin.orders', ['order_status' => 'pending']),
                    ],
                    [
                        'icon' => 'fa-solid fa-box-open',
                        'label' => 'Biến thể sắp hết hàng',
                        'count' => $lowStockVariantsCount,
                        'url' => route('admin.products', ['status' => 'active']),
                    ],
                ],
                'adminNotificationsTotal' => $newCommentsCount + $newOrdersCount + $lowStockVariantsCount,
            ]);
        });
    }
}
