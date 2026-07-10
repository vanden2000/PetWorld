<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'description')) {
                $table->text('description')->nullable()->after('image');
            }
            if (!Schema::hasColumn('categories', 'status')) {
                $table->string('status')->default('active')->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('categories', 'description')) {
                $cols[] = 'description';
            }
            if (Schema::hasColumn('categories', 'status')) {
                $cols[] = 'status';
            }
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
