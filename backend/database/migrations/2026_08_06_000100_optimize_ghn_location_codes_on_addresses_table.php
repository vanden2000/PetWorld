<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table): void {
            $table->dropIndex('addresses_ghn_destination_index');
            $table->dropColumn('ghn_province_id');
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table): void {
            $table->unsignedInteger('ghn_province_id')->nullable()->after('province');
            $table->index(['ghn_district_id', 'ghn_ward_code'], 'addresses_ghn_destination_index');
        });
    }
};
