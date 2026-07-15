<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('images', function (Blueprint $table) {
            if (! Schema::hasColumn('images', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('image_url');
            }

            if (! Schema::hasColumn('images', 'alt_text')) {
                $table->string('alt_text')->nullable()->after('is_primary');
            }
        });

        DB::table('images')
            ->orderBy('product_id')
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->get(['id', 'product_id'])
            ->groupBy('product_id')
            ->each(function ($images): void {
                foreach ($images->values() as $index => $image) {
                    DB::table('images')->where('id', $image->id)->update(['sort_order' => $index]);
                }
            });
    }

    public function down(): void
    {
        // The columns may predate this migration in existing databases, so rollback must not remove user-owned schema.
    }
};
