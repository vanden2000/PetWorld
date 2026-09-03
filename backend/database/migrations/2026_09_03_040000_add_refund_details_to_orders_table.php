<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('refunded_at')->nullable()->after('reconciled_at');
            $table->string('refund_bank_name', 100)->nullable()->after('refunded_at');
            $table->string('refund_account_number', 50)->nullable()->after('refund_bank_name');
            $table->string('refund_account_name', 100)->nullable()->after('refund_account_number');
            $table->decimal('refund_amount', 12, 2)->nullable()->after('refund_account_name');
            $table->string('refund_proof_image')->nullable()->after('refund_amount');
            $table->text('refund_note')->nullable()->after('refund_proof_image');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'refunded_at',
                'refund_bank_name',
                'refund_account_number',
                'refund_account_name',
                'refund_amount',
                'refund_proof_image',
                'refund_note',
            ]);
        });
    }
};
