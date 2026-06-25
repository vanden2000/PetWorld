<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }

            if (! Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('password');
            }

            if (! Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['user', 'admin'])->default('user')->after('avatar');
            }

            if (! Schema::hasColumn('users', 'status')) {
                $table->enum('status', ['active', 'inactive', 'blocked'])->default('active')->after('role');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            $this->changeStatusToEnum('products');
            $this->changeStatusToEnum('product_variants');
            $this->changeStatusToEnum('variant_types');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            $this->changeStatusToBoolean('variant_types');
            $this->changeStatusToBoolean('product_variants');
            $this->changeStatusToBoolean('products');
        }

        Schema::table('users', function (Blueprint $table) {
            foreach (['status', 'role', 'avatar', 'phone'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function changeStatusToEnum(string $table): void
    {
        DB::statement("ALTER TABLE {$table} MODIFY status VARCHAR(20) NOT NULL DEFAULT 'active'");
        DB::statement("UPDATE {$table} SET status = CASE WHEN status IN ('1', 'active') THEN 'active' ELSE 'inactive' END");
        DB::statement("ALTER TABLE {$table} MODIFY status ENUM('active', 'inactive') NOT NULL DEFAULT 'active'");
    }

    private function changeStatusToBoolean(string $table): void
    {
        DB::statement("ALTER TABLE {$table} MODIFY status VARCHAR(20) NOT NULL DEFAULT 'active'");
        DB::statement("UPDATE {$table} SET status = CASE WHEN status = 'active' THEN '1' ELSE '0' END");
        DB::statement("ALTER TABLE {$table} MODIFY status TINYINT(1) NOT NULL DEFAULT 1");
    }
};
