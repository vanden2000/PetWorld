<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->json('advice_attributes')->nullable()->after('short_description');
        });

        Schema::table('chat_conversations', function (Blueprint $table): void {
            $table->json('context')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table): void {
            $table->dropColumn('context');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn('advice_attributes');
        });
    }
};
