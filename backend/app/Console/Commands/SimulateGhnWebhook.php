<?php

namespace App\Console\Commands;

use App\Models\Shipment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SimulateGhnWebhook extends Command
{
    protected $signature = 'ghn:simulate {tracking_code : Mã vận đơn GHN (ví dụ: L8KCP4)} {status : Trạng thái cần test (ready_to_pick, picking, picked, delivering, delivered, return)}';

    protected $description = 'Giả lập webhook từ GHN cập nhật trạng thái vận đơn để kiểm thử luồng 5 bước';

    public function handle(): int
    {
        $trackingCode = (string) $this->argument('tracking_code');
        $status = (string) $this->argument('status');

        $shipment = Shipment::where('tracking_code', $trackingCode)->first();

        if (! $shipment) {
            $this->error("Không tìm thấy vận đơn với mã [{$trackingCode}].");
            return self::FAILURE;
        }

        DB::transaction(function () use ($shipment, $status): void {
            $shipment->loadMissing('order');

            $shipment->update([
                'status' => $status,
                'provider_status_code' => $status,
            ]);

            if ($status === 'delivered' && $shipment->order !== null) {
                $orderUpdates = [];
                if ($shipment->order->order_status === 'shipping') {
                    $orderUpdates['order_status'] = 'completed';
                }

                if ((float) $shipment->cod_amount > 0 && $shipment->order->payment_status === 'unpaid') {
                    $orderUpdates['payment_status'] = 'customer_paid';
                }

                if ($orderUpdates !== []) {
                    $shipment->order->update($orderUpdates);
                }
            }
        });

        $this->info("✓ Đã giả lập webhook GHN thành công cho mã vận đơn [{$trackingCode}]:");
        $this->line("  - Trạng thái vận đơn: {$status}");
        $this->line("  - Trạng thái đơn hàng: " . $shipment->fresh()->order?->order_status);
        $this->line("  - Trạng thái thanh toán: " . $shipment->fresh()->order?->payment_status);

        return self::SUCCESS;
    }
}
