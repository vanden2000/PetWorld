<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('product_variants', 'sku')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->string('sku')->nullable()->unique()->after('product_id');
            });
        }

        DB::table('product_variants')
            ->whereNull('sku')
            ->orderBy('id')
            ->eachById(function (object $variant): void {
                DB::table('product_variants')
                    ->where('id', $variant->id)
                    ->update(['sku' => 'PW-VAR-'.$variant->id]);
            });

        if (! Schema::hasTable('product_variant_types')) {
            Schema::create('product_variant_types', function (Blueprint $table) {
                $table->foreignId('product_variant_id')
                    ->constrained('product_variants')
                    ->cascadeOnDelete();

                $table->foreignId('variant_type_id')
                    ->constrained('variant_types')
                    ->restrictOnDelete();

                $table->string('value');

                // Mỗi biến thể chỉ được có một giá trị cho mỗi loại biến thể.
                $table->primary(['product_variant_id', 'variant_type_id']);
            });

            // Chuyển toàn bộ loại và giá trị cũ sang bảng trung gian trước khi bỏ cột.
            DB::table('product_variant_types')->insertUsing(
                ['product_variant_id', 'variant_type_id', 'value'],
                DB::table('product_variants')->select([
                    'id',
                    'variant_type_id',
                    'variant_name',
                ]),
            );
        }

        // Giữ một index riêng cho FK product_id trước khi bỏ unique index cũ.
        Schema::table('product_variants', function (Blueprint $table) {
            $table->index('product_id', 'product_variants_product_id_index');
        });

        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->dropForeign(['variant_type_id']);
                $table->dropUnique(['product_id', 'variant_type_id', 'variant_name']);
                $table->dropColumn(['variant_type_id', 'variant_name']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->foreignId('variant_type_id')
                ->nullable()
                ->after('id')
                ->constrained('variant_types')
                ->restrictOnDelete();
            $table->string('variant_name')->nullable()->after('product_id');
        });

        DB::table('product_variants')
            ->orderBy('id')
            ->eachById(function (object $variant): void {
                $option = DB::table('product_variant_types')
                    ->where('product_variant_id', $variant->id)
                    ->orderBy('variant_type_id')
                    ->first();

                if ($option !== null) {
                    DB::table('product_variants')
                        ->where('id', $variant->id)
                        ->update([
                            'variant_type_id' => $option->variant_type_id,
                            'variant_name' => $option->value,
                        ]);
                }
            });

        Schema::dropIfExists('product_variant_types');

        Schema::table('product_variants', function (Blueprint $table) {
            $table->unique(['product_id', 'variant_type_id', 'variant_name']);
            $table->dropIndex('product_variants_product_id_index');
            $table->dropUnique(['sku']);
            $table->dropColumn('sku');
        });
    }
};
