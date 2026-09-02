<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE reviews MODIFY status ENUM('pending', 'approved', 'hidden') NOT NULL DEFAULT 'pending'");
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE reviews ALTER COLUMN status SET DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE reviews MODIFY status ENUM('pending', 'approved', 'hidden') NOT NULL DEFAULT 'approved'");
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE reviews ALTER COLUMN status SET DEFAULT 'approved'");
        }
    }
};
