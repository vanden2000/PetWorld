<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->string('seo_title', 70)->nullable()->after('title');
            $table->string('meta_description', 180)->nullable()->after('description');
            $table->string('focus_keyword', 120)->nullable()->after('meta_description');
            $table->json('secondary_keywords')->nullable()->after('focus_keyword');
            $table->enum('search_intent', ['informational', 'commercial', 'transactional', 'navigational'])
                ->nullable()
                ->after('secondary_keywords');
            $table->string('cover_alt', 255)->nullable()->after('image');
        });
    }

    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropColumn([
                'seo_title',
                'meta_description',
                'focus_keyword',
                'secondary_keywords',
                'search_intent',
                'cover_alt',
            ]);
        });
    }
};
