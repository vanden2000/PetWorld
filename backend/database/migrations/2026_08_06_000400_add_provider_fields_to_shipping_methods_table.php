<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipping_methods', function (Blueprint $table): void {
            $table->string('code', 40)->nullable()->after('id');
            $table->string('provider', 40)->nullable()->after('name');
            $table->enum('fee_mode', ['live_quote', 'fixed', 'unavailable'])->default('fixed')->after('shipping_fee');
            $table->string('description', 255)->nullable()->after('fee_mode');
            $table->unique('code', 'shipping_methods_code_unique');
        });
    }

    public function down(): void
    {
        Schema::table('shipping_methods', function (Blueprint $table): void {
            $table->dropUnique('shipping_methods_code_unique');
            $table->dropColumn(['code', 'provider', 'fee_mode', 'description']);
        });
    }
};
