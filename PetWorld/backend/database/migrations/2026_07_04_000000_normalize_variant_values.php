<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL DDL is not transactional. Remove only artifacts from an interrupted,
        // still-pending run while the legacy source table is present.
        if (Schema::hasTable('product_variant_types')) {
            Schema::dropIfExists('product_variant_values');
            Schema::dropIfExists('variant_values');
        }

        Schema::table('variant_types', function (Blueprint $table) {
            $table->timestamps();
        });

        DB::table('variant_types')->update([
                'created_at' => now(),
                'updated_at' => now(),
        ]);

        Schema::create('variant_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_type_id')->constrained()->restrictOnDelete();
            $table->string('value');
            $table->timestamps();

            $table->unique(['variant_type_id', 'value'], 'variant_values_type_value_unique');
        });

        Schema::create('product_variant_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_value_id')->constrained()->restrictOnDelete();
            $table->timestamps();

            $table->unique(['product_variant_id', 'variant_value_id'], 'pv_values_variant_value_unique');
        });

        DB::table('product_variant_types')
            ->orderBy('product_variant_id')
            ->orderBy('variant_type_id')
            ->get()
            ->each(function (object $option): void {
                $valueId = DB::table('variant_values')
                    ->where('variant_type_id', $option->variant_type_id)
                    ->where('value', $option->value)
                    ->value('id');

                if ($valueId === null) {
                    $valueId = DB::table('variant_values')->insertGetId([
                        'variant_type_id' => $option->variant_type_id,
                        'value' => $option->value,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('product_variant_values')->insert([
                    'product_variant_id' => $option->product_variant_id,
                    'variant_value_id' => $valueId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        Schema::drop('product_variant_types');
    }

    public function down(): void
    {
        Schema::create('product_variant_types', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_type_id')->constrained()->restrictOnDelete();
            $table->string('value');
            $table->primary(['product_variant_id', 'variant_type_id']);
        });

        DB::table('product_variant_values')
            ->join('variant_values', 'variant_values.id', '=', 'product_variant_values.variant_value_id')
            ->select([
                'product_variant_values.product_variant_id',
                'variant_values.variant_type_id',
                'variant_values.value',
            ])
            ->orderBy('product_variant_values.id')
            ->get()
            ->each(fn (object $option) => DB::table('product_variant_types')->insert((array) $option));

        Schema::drop('product_variant_values');
        Schema::drop('variant_values');

        if (Schema::hasColumn('variant_types', 'created_at')) {
            Schema::table('variant_types', function (Blueprint $table) {
                $table->dropColumn(['created_at', 'updated_at']);
            });
        }
    }
};
