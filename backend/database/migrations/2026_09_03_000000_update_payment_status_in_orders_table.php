<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'reconciliation_note')) {
                $table->string('reconciliation_note')->nullable()->after('note');
            }
            if (! Schema::hasColumn('orders', 'reconciled_at')) {
                $table->timestamp('reconciled_at')->nullable()->after('reconciliation_note');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['reconciliation_note', 'reconciled_at']);
        });
    }
};
