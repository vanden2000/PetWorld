<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('administrative_location_mappings');
    }

    public function down(): void
    {
        // The removed table contained no production data. Restore it from the original migration if needed.
    }
};
