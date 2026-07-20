<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireUnpaidOrders extends Command
{
    protected $signature = 'orders:expire-unpaid';

    protected $description = 'Tự hủy các đơn chuyển khoản quá hạn thanh toán và hoàn lại kho + voucher.';

    public function handle(): int
    {
        // Ứng viên: đơn còn chờ, chưa trả tiền, đã đặt hạn và hạn đã qua.
        // (Đơn COD có expires_at = null nên không bao giờ lọt vào đây.)
        $orderIds = Order::query()
            ->where('order_status', 'pending')
            ->where('payment_status', 'unpaid')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->pluck('id');

        if ($orderIds->isEmpty()) {
            return self::SUCCESS;
        }

        $cancelled = 0;

        foreach ($orderIds as $orderId) {
            // Mỗi đơn một transaction + khóa để không đụng webhook đang xác nhận cùng lúc.
            DB::transaction(function () use ($orderId, &$cancelled): void {
                $order = Order::query()
                    ->whereKey($orderId)
                    ->lockForUpdate()
                    ->first();

                // Kiểm tra lại sau khi khóa: có thể vừa được thanh toán hoặc gia hạn.
                if (
                    $order === null
                    || $order->order_status !== 'pending'
                    || $order->payment_status !== 'unpaid'
                    || $order->expires_at === null
                    || $order->expires_at->isFuture()
                ) {
                    return;
                }

                $order->restockAndMarkCancelled();
                $cancelled++;
            });
        }

        if ($cancelled > 0) {
            $this->info("Đã hủy {$cancelled} đơn quá hạn thanh toán.");
        }

        return self::SUCCESS;
    }
}
