<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('knowledge_articles', function (Blueprint $table) {
            $table->text('summary')->nullable()->after('title');
            $table->json('questions')->nullable()->after('content');
        });
    }

    public function down(): void
    {
        Schema::table('knowledge_articles', function (Blueprint $table) {
            $table->dropColumn(['summary', 'questions']);
        });
    }
};
