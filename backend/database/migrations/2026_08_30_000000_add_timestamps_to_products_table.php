<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('products', 'created_at')) {
            return;
        }

        Schema::table('products', function (Blueprint $table): void {
            $table->timestamps();
        });

        // Sản phẩm cũ chưa có mốc thời gian: lấy ngày tải ảnh đầu tiên làm ngày đăng.
        // Ảnh được thêm ngay khi tạo sản phẩm nên đây là mốc gần đúng nhất hiện có.
        DB::table('products')->update([
            'created_at' => DB::raw('(SELECT MIN(images.created_at) FROM images WHERE images.product_id = products.id)'),
        ]);

        // Sản phẩm chưa có ảnh thì không suy ra được; dùng thời điểm chạy migration.
        DB::table('products')->whereNull('created_at')->update([
            'created_at' => now(),
        ]);

        DB::table('products')->update([
            'updated_at' => DB::raw('created_at'),
        ]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['created_at', 'updated_at']);
        });
    }
};
