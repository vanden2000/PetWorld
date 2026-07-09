<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            // Change status column to enum in categories
            if (Schema::hasColumn('categories', 'status')) {
                Schema::table('categories', function (Blueprint $table) {
                    $table->enum('status', ['active', 'draft'])->default('active')->change();
                });
            }

            // Change status column to enum in brands
            if (Schema::hasColumn('brands', 'status')) {
                Schema::table('brands', function (Blueprint $table) {
                    $table->enum('status', ['active', 'draft'])->default('active')->change();
                });
            }

            // Change status column to enum in banners
            if (Schema::hasColumn('banners', 'status')) {
                Schema::table('banners', function (Blueprint $table) {
                    $table->enum('status', ['active', 'draft'])->default('active')->change();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            if (Schema::hasColumn('categories', 'status')) {
                Schema::table('categories', function (Blueprint $table) {
                    $table->string('status')->default('active')->change();
                });
            }

            if (Schema::hasColumn('brands', 'status')) {
                Schema::table('brands', function (Blueprint $table) {
                    $table->string('status')->default('active')->change();
                });
            }

            if (Schema::hasColumn('banners', 'status')) {
                Schema::table('banners', function (Blueprint $table) {
                    $table->string('status')->default('active')->change();
                });
            }
        }
    }
};
