<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Log mọi giao dịch SePay đẩy về webhook: vừa để đối soát/audit, vừa chống
     * xử lý trùng (sepay_id unique) khi SePay gửi lại cùng một giao dịch.
     */
    public function up(): void
    {
        Schema::create('sepay_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sepay_id')->unique();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('gateway')->nullable();
            $table->dateTime('transaction_date')->nullable();
            $table->string('account_number')->nullable();
            $table->string('transfer_type')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->text('content')->nullable();
            $table->string('reference_code')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sepay_transactions');
    }
};
