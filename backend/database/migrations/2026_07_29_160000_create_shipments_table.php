<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('provider', 40);
            $table->string('tracking_code')->nullable()->unique();
            $table->unsignedInteger('weight_grams')->default(0);
            $table->decimal('shipping_fee', 12, 2)->default(0);
            $table->decimal('cod_amount', 12, 2)->default(0);
            $table->string('status', 40)->default('pending');
            $table->string('provider_status_code', 40)->nullable();
            $table->string('label_url')->nullable();
            $table->json('provider_payload')->nullable();
            $table->timestamps();

            $table->index(['provider', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
