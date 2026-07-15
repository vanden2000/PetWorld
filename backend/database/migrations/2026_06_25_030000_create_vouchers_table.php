<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->unsignedInteger('usage_limit')->default(0);
            $table->decimal('discount_value', 12, 2);
            $table->text('description')->nullable();
            $table->decimal('min_order_value', 12, 2)->default(0);
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->enum('status', ['active', 'inactive', 'expired'])->default('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
