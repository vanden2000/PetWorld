<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\SepayPaymentReconciler;
use Illuminate\Console\Command;
use Throwable;

class CheckSepayPayments extends Command
{
    protected $signature = 'orders:check-sepay-payments';

    protected $description = 'Quet SePay API de tu xac nhan cac don chuyen khoan dang cho, khong can webhook/ngrok.';

    public function handle(SepayPaymentReconciler $reconciler): int
    {
        if (! $reconciler->hasApiCredentials()) {
            $this->warn('Chua cau hinh SEPAY_API_TOKEN, bo qua quet thanh toan SePay.');
            return self::SUCCESS;
        }

        $orders = Order::query()
            ->where('order_status', 'pending')
            ->where('payment_status', 'unpaid')
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', now())
            ->oldest()
            ->limit(50)
            ->get();

        $confirmed = 0;

        foreach ($orders as $order) {
            try {
                $checked = $reconciler->reconcile($order);
                if ($checked->payment_status === 'paid') {
                    $confirmed++;
                }
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        if ($confirmed > 0) {
            $this->info("Da xac nhan {$confirmed} don thanh toan qua SePay.");
        }

        return self::SUCCESS;
    }
}
