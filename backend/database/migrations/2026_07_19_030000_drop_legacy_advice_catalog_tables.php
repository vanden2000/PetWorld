<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('advice_option_product');
        Schema::dropIfExists('advice_options');
        Schema::dropIfExists('advice_dimensions');
    }

    public function down(): void
    {
        // These tables belonged to a discarded, dynamic advice-catalog design.
        // They are intentionally not recreated on rollback.
    }
};
