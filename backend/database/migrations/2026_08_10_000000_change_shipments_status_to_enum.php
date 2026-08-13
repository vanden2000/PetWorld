<?php

use App\Models\Shipment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $statuses = implode(', ', array_map(
            fn (string $status): string => "'{$status}'",
            Shipment::STATUSES,
        ));

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE shipments MODIFY status ENUM({$statuses}) NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE shipments MODIFY status VARCHAR(40) NOT NULL DEFAULT 'pending'");
        }
    }
};
