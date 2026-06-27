<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            foreach (['products', 'variant_types', 'product_variants'] as $table) {
                DB::statement("ALTER TABLE {$table} MODIFY status VARCHAR(20) NOT NULL DEFAULT 'active'");
                DB::statement("UPDATE {$table} SET status = CASE WHEN status IN ('1', 'active') THEN 'active' ELSE 'inactive' END");
                DB::statement("ALTER TABLE {$table} MODIFY status ENUM('active', 'inactive') NOT NULL DEFAULT 'active'");
            }

            return;
        }

        foreach (['products', 'variant_types', 'product_variants'] as $table) {
            DB::table($table)->whereIn('status', [1, '1', true])->update(['status' => 'active']);
            DB::table($table)->whereIn('status', [0, '0', false])->update(['status' => 'inactive']);
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach (['products', 'variant_types', 'product_variants'] as $table) {
            DB::statement("ALTER TABLE {$table} MODIFY status VARCHAR(20) NOT NULL DEFAULT 'active'");
            DB::statement("UPDATE {$table} SET status = CASE WHEN status = 'active' THEN '1' ELSE '0' END");
            DB::statement("ALTER TABLE {$table} MODIFY status TINYINT(1) NOT NULL DEFAULT 1");
        }
    }
};
