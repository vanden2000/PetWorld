<?php

namespace App\Console\Commands;

use App\Models\Shipment;
use App\Services\GhnService;
use Illuminate\Console\Command;
use Throwable;

class SyncGhnShipments extends Command
{
    protected $signature = 'shipments:sync-ghn {--limit=50 : So van don toi da moi lan quet}';

    protected $description = 'Dong bo trang thai van don GHN ve PetWorld.';

    public function handle(GhnService $ghn): int
    {
        $shipments = Shipment::query()
            ->where('provider', 'ghn')
            ->whereNotNull('tracking_code')
            ->whereNotIn('status', ['delivered', 'cancelled', 'returned'])
            ->oldest('updated_at')
            ->limit((int) $this->option('limit'))
            ->get();

        $synced = 0;

        foreach ($shipments as $shipment) {
            try {
                $detail = $ghn->orderDetail($shipment->tracking_code);
                $status = (string) ($detail['status'] ?? $shipment->status);

                $shipment->update([
                    'status' => $status,
                    'provider_status_code' => $detail['status_code'] ?? $status,
                    'provider_payload' => $detail,
                ]);
                $synced++;
            } catch (Throwable $exception) {
                report($exception);
                $this->warn("Khong dong bo duoc {$shipment->tracking_code}.");
            }
        }

        $this->info("Da dong bo {$synced} van don GHN.");

        return self::SUCCESS;
    }
}
