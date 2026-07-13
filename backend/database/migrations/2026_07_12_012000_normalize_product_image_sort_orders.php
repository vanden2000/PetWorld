<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('images')
            ->whereNotNull('product_id')
            ->distinct()
            ->orderBy('product_id')
            ->pluck('product_id')
            ->each(function (int $productId): void {
                DB::table('images')
                    ->where('product_id', $productId)
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get()
                    ->each(function (object $image, int $sortOrder): void {
                        DB::table('images')->where('id', $image->id)->update(['sort_order' => $sortOrder]);
                    });
            }, 'product_id');
    }

    public function down(): void
    {
        // Normalized ordering is safe to retain on rollback.
    }
};
