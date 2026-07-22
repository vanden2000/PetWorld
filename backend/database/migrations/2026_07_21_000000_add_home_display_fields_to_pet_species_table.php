<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pet_species', function (Blueprint $table): void {
            $table->string('image')->nullable()->after('slug');
            $table->string('background_color', 20)->nullable()->after('image');
            $table->unsignedInteger('sort_order')->default(0)->after('background_color');
            $table->boolean('show_on_home')->default(false)->after('sort_order');
        });

        DB::table('pet_species')->where('slug', 'cat')->update([
            'background_color' => '#EEF6FF',
            'sort_order' => 1,
            'show_on_home' => true,
        ]);

        DB::table('pet_species')->where('slug', 'dog')->update([
            'background_color' => '#FFF2E8',
            'sort_order' => 2,
            'show_on_home' => true,
        ]);
    }

    public function down(): void
    {
        Schema::table('pet_species', function (Blueprint $table): void {
            $table->dropColumn(['image', 'background_color', 'sort_order', 'show_on_home']);
        });
    }
};
