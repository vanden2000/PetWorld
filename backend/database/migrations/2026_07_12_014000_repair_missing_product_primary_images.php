<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('images')->whereNotNull('product_id')->distinct()->orderBy('product_id')->pluck('product_id')
            ->each(function (int $productId): void {
                $primary = DB::table('images')->where('product_id', $productId)->orderByDesc('is_primary')->orderBy('sort_order')->orderBy('id')->first();
                if (! $primary) return;

                DB::table('images')->where('product_id', $productId)->update(['is_primary' => false]);
                DB::table('images')->where('id', $primary->id)->update(['is_primary' => true]);
            });
    }

    public function down(): void {}
};
